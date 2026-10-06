<x-app-layout title="Import Products">

    <x-page-header overline="Catalogue" title="Import Products" subtitle="Bulk-add or update products from a CSV or Excel file.">
        <x-slot:actions>
            <x-btn :href="route('products.index')" variant="ghost" size="sm">← Back to Products</x-btn>
        </x-slot:actions>
    </x-page-header>

    <div class="flex flex-col gap-5 max-w-3xl">

        @if($result = session('importResult'))
            <x-card title="Import Results">
                <div class="grid grid-cols-3 gap-3 mb-4">
                    <div class="bg-ok/5 border border-ok/20 rounded-lg p-3.5 text-center">
                        <p class="font-serif text-[22px] font-bold text-ok">{{ $result['created'] }}</p>
                        <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mt-1">Created</p>
                    </div>
                    <div class="bg-navy/5 border border-navy/20 rounded-lg p-3.5 text-center">
                        <p class="font-serif text-[22px] font-bold text-navy">{{ $result['updated'] }}</p>
                        <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mt-1">Updated</p>
                    </div>
                    <div class="bg-danger/5 border border-danger/20 rounded-lg p-3.5 text-center">
                        <p class="font-serif text-[22px] font-bold text-danger">{{ $result['skipped'] }}</p>
                        <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mt-1">Skipped</p>
                    </div>
                </div>

                @if(! empty($result['errors']))
                    <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-2">
                        Rows skipped {{ count($result['errors']) >= 100 ? '(first 100 shown)' : '' }}
                    </p>
                    <div class="flex flex-col gap-1.5 max-h-80 overflow-y-auto">
                        @foreach($result['errors'] as $error)
                            <div class="flex items-start gap-2 px-3 py-2 bg-danger/[0.03] border border-danger/15 rounded-md">
                                <span class="font-mono text-[10px] text-danger flex-shrink-0">Row {{ $error['row'] }}</span>
                                <span class="text-[11.5px] text-ink3">{{ $error['message'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-[12.5px] text-ink3">All rows imported without errors.</p>
                @endif
            </x-card>
        @endif

        <x-card title="1. Download the Template">
            <p class="text-[13px] text-ink2 leading-relaxed mb-4">
                Start from the template so your column headers match exactly what the importer expects.
                <strong class="text-ink">SKU</strong>, <strong class="text-ink">Name</strong>,
                <strong class="text-ink">Unit Cost</strong>, and <strong class="text-ink">Selling Price</strong>
                are required — everything else is optional.
            </p>
            <x-btn :href="route('products.import.template')" variant="ghost" size="sm">
                <x-icon name="download" class="w-3 h-3" /> Download CSV Template
            </x-btn>
        </x-card>

        <x-card title="2. Column Reference">
            <div class="overflow-x-auto -mx-1">
                <table class="w-full text-[11.5px]">
                    <thead>
                        <tr class="border-b border-cream3">
                            <th class="text-left font-mono text-[9px] tracking-[0.1em] uppercase text-ink4 pb-2 pl-1">Column</th>
                            <th class="text-left font-mono text-[9px] tracking-[0.1em] uppercase text-ink4 pb-2">Required</th>
                            <th class="text-left font-mono text-[9px] tracking-[0.1em] uppercase text-ink4 pb-2">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach([
                            ['SKU',                       true,  'Unique code. If a product with this SKU already exists, it is updated instead of duplicated.'],
                            ['Name',                      true,  'Product name.'],
                            ['Description',               false, 'Optional long description.'],
                            ['Category',                  false, 'Must match an existing category name exactly, or the row is skipped.'],
                            ['Supplier',                   false, 'Must match an existing supplier\'s company name exactly, or the row is skipped.'],
                            ['Unit Cost',                  true,  'Numeric, e.g. 350.00'],
                            ['Selling Price',              true,  'Numeric, e.g. 599.00'],
                            ['Tax Rate (%)',               false, 'Numeric percent. Defaults to 0.'],
                            ['Unit of Measure',            false, 'e.g. pc, box, bottle'],
                            ['Reorder Level',               false, 'Integer. Defaults to 0.'],
                            ['Age Restricted (Yes/No)',    false, 'Defaults to No.'],
                            ['Active (Yes/No)',            false, 'Defaults to Yes.'],
                            ['On Sale (Yes/No)',           false, 'Defaults to No. Sale Price is ignored unless this is Yes.'],
                            ['Sale Price',                 false, 'Numeric. Only used when On Sale is Yes.'],
                            ['Wholesale (Yes/No)',         false, 'Defaults to No. Min Order Qty is ignored unless this is Yes.'],
                            ['Min Order Qty',              false, 'Integer. Only used when Wholesale is Yes.'],
                        ] as [$col, $req, $note])
                            <tr class="border-b border-cream2 last:border-0">
                                <td class="py-2 pl-1 font-mono text-[10.5px] text-terra whitespace-nowrap">{{ $col }}</td>
                                <td class="py-2">
                                    @if($req)
                                        <x-badge variant="warn">Required</x-badge>
                                    @else
                                        <span class="text-ink4">Optional</span>
                                    @endif
                                </td>
                                <td class="py-2 text-ink3 leading-snug">{{ $note }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>

        <x-card title="3. Upload Your File">
            <form method="POST" action="{{ route('products.import') }}" enctype="multipart/form-data" class="flex flex-col gap-4">
                @csrf

                <div>
                    <label class="block font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-1.5">CSV or Excel File</label>
                    <input type="file" name="file" accept=".csv,.txt,.xlsx,.xls" required
                           class="w-full bg-cream border border-cream3 rounded-md px-3 py-2.5 text-[12.5px] font-sans text-ink
                                  outline-none focus:border-terra focus:ring-1 focus:ring-terra/20 transition-colors
                                  file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-sidebar file:text-white file:text-[11px] file:font-mono file:cursor-pointer">
                    <p class="text-[11px] text-ink4 mt-1.5">Accepted: .csv, .xlsx, .xls — max 5 MB.</p>
                    @error('file')
                        <p class="erp-field-error mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-btn type="submit" variant="primary" size="sm">
                        <x-icon name="upload" class="w-3 h-3" /> Import Products
                    </x-btn>
                </div>
            </form>
        </x-card>

    </div>

</x-app-layout>
