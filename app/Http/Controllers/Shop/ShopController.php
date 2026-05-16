<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Mail\PaymentInstructionsMail;
use App\Models\BranchInventory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\OnlineOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    // ── Shop catalog ─────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $categories = ProductCategory::orderBy('name')->get();

        $products = Product::with(['category', 'inventory'])
            ->where('is_active', true)
            ->when($request->category, fn ($q) => $q->where('category_id', $request->category))
            ->when($request->search,   fn ($q) => $q->where(fn ($s) =>
                $s->where('name', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%")
            ))
            ->when($request->boolean('is_on_sale'),   fn ($q) => $q->where('is_on_sale', true)->whereNotNull('sale_price'))
            ->when($request->boolean('is_wholesale'), fn ($q) => $q->where('is_wholesale', true))
            ->when($request->sort === 'price_asc',   fn ($q) => $q->orderBy('selling_price', 'asc'))
            ->when($request->sort === 'price_desc',  fn ($q) => $q->orderBy('selling_price', 'desc'))
            ->when($request->sort === 'newest',      fn ($q) => $q->latest())
            ->when(!$request->sort, fn ($q) => $q->orderBy('name'))
            ->paginate(16)
            ->withQueryString();

        return view('shop.index', compact('products', 'categories'));
    }

    public function product(int $id)
    {
        $product  = Product::with(['category', 'supplier', 'inventory'])->findOrFail($id);
        $related  = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->limit(4)
            ->get();

        return view('shop.product', compact('product', 'related'));
    }

    // ── Cart (session-based) ─────────────────────────────────────────────────
    public function cart()
    {
        $cart     = session('vgm_cart', []);
        $products = Product::whereIn('id', array_keys($cart))->with('inventory')->get()->keyBy('id');
        $items    = collect($cart)->map(fn ($qty, $id) => [
            'product'  => $products[$id] ?? null,
            'quantity' => $qty,
        ])->filter(fn ($i) => $i['product'] !== null);

        return view('shop.cart', compact('items'));
    }

    public function addToCart(Request $request, int $id)
    {
        $product = Product::findOrFail($id);
        $qty     = max(1, (int) $request->input('quantity', 1));
        $cart    = session('vgm_cart', []);
        $cart[$id] = ($cart[$id] ?? 0) + $qty;
        session(['vgm_cart' => $cart]);

        if ($request->wantsJson()) {
            return response()->json(['cart_count' => array_sum($cart), 'message' => 'Added to cart']);
        }
        return back()->with('success', "{$product->name} added to cart.");
    }

    public function updateCart(Request $request, int $id)
    {
        $cart = session('vgm_cart', []);
        $qty  = (int) $request->input('quantity', 1);
        if ($qty < 1) {
            unset($cart[$id]);
        } else {
            $cart[$id] = $qty;
        }
        session(['vgm_cart' => $cart]);
        return back();
    }

    public function removeFromCart(int $id)
    {
        $cart = session('vgm_cart', []);
        unset($cart[$id]);
        session(['vgm_cart' => $cart]);
        return back()->with('success', 'Item removed from cart.');
    }

    // ── Checkout ─────────────────────────────────────────────────────────────
    public function checkout()
    {
        $cart = session('vgm_cart', []);
        if (empty($cart)) return redirect()->route('shop.cart')->with('error', 'Your cart is empty.');

        $products = Product::whereIn('id', array_keys($cart))->with('inventory')->get()->keyBy('id');
        $items    = collect($cart)->map(fn ($qty, $id) => [
            'product'  => $products[$id] ?? null,
            'quantity' => $qty,
        ])->filter(fn ($i) => $i['product'] !== null);

        return view('shop.checkout', compact('items'));
    }

    public function placeOrder(Request $request)
    {
        $validated = $request->validate([
            'first_name'       => 'required|string|max:100',
            'last_name'        => 'required|string|max:100',
            'email'            => 'required|email|max:255',
            'phone'            => 'required|string|max:20',
            'address'          => 'required|string|max:500',
            'city'             => 'required|string|max:100',
            'pickup_branch_id' => 'nullable|integer|exists:branches,id',
            'payment_method'   => 'required|in:gcash,maya,cod,bank_transfer',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $cart     = session('vgm_cart', []);
        if (empty($cart)) return redirect()->route('shop.cart');

        $products = Product::whereIn('id', array_keys($cart))->get()->keyBy('id');

        // Validate stock before creating the order
        $branchId = $validated['pickup_branch_id'] ?? null;
        foreach ($cart as $productId => $qty) {
            if (! isset($products[$productId])) continue;
            $product = $products[$productId];

            if ($branchId) {
                $inv       = BranchInventory::where('branch_id', $branchId)->where('product_id', $productId)->first();
                $available = $inv ? max(0, $inv->quantity_on_hand - $inv->quantity_reserved) : 0;
            } else {
                $available = (int) BranchInventory::where('product_id', $productId)
                    ->selectRaw('COALESCE(SUM(quantity_on_hand - quantity_reserved), 0) as avail')
                    ->value('avail');
            }

            if ($available < $qty) {
                return back()->withInput()->withErrors([
                    'stock' => "\"{$product->name}\" — only {$available} unit(s) available, you requested {$qty}.",
                ]);
            }
        }

        $subtotal = collect($cart)->reduce(fn ($sum, $qty, $id) =>
            $sum + (($products[$id]->selling_price ?? 0) * $qty), 0
        );
        $tax   = round($subtotal * 0.12, 2);
        $total = $subtotal + $tax;

        $orderNumber = 'VGM-' . strtoupper(Str::random(8));

        $order = DB::transaction(function () use ($validated, $cart, $products, $subtotal, $tax, $total, $orderNumber) {
            $order = OnlineOrder::create([
                'order_number'     => $orderNumber,
                'first_name'       => $validated['first_name'],
                'last_name'        => $validated['last_name'],
                'email'            => $validated['email'],
                'phone'            => $validated['phone'],
                'address'          => $validated['address'],
                'city'             => $validated['city'],
                'pickup_branch_id' => $validated['pickup_branch_id'] ?? null,
                'payment_method'   => $validated['payment_method'],
                'notes'            => $validated['notes'] ?? null,
                'subtotal'         => $subtotal,
                'tax_amount'       => $tax,
                'total_amount'     => $total,
                'status'           => 'pending',
            ]);

            foreach ($cart as $productId => $qty) {
                if (!isset($products[$productId])) continue;
                $product = $products[$productId];
                $order->items()->create([
                    'product_id'  => $productId,
                    'product_name'=> $product->name,
                    'sku'         => $product->sku,
                    'quantity'    => $qty,
                    'unit_price'  => $product->selling_price,
                    'subtotal'    => $product->selling_price * $qty,
                ]);
            }
            return $order;
        });

        session()->forget('vgm_cart');

        // Send payment instructions email for GCash and Maya orders
        if (in_array($order->payment_method, ['gcash', 'maya'])) {
            try {
                Mail::to($order->email, $order->full_name)
                    ->send(new PaymentInstructionsMail($order->load('items', 'branch')));
            } catch (\Exception $e) {
                // Non-fatal — order was placed; log the failure but don't block the redirect
                logger()->error('Payment instructions email failed', [
                    'order'   => $order->order_number,
                    'email'   => $order->email,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        return redirect()->route('shop.confirmation', $order->order_number);
    }

    // ── Order confirmation & tracking ────────────────────────────────────────
    public function confirmation(string $orderNumber)
    {
        $order = OnlineOrder::with(['items', 'branch'])->where('order_number', $orderNumber)->firstOrFail();
        return view('shop.confirmation', compact('order'));
    }

    public function trackOrder(Request $request)
    {
        $order = null;
        if ($request->order_number) {
            $order = OnlineOrder::with(['items', 'branch'])
                ->where('order_number', $request->order_number)
                ->when($request->email, fn ($q) => $q->where('email', $request->email))
                ->first();
        }
        return view('shop.track', compact('order'));
    }
}
