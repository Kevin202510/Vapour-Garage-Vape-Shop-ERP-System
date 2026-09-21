<x-app-layout title="Documentation">

    <x-page-header
        overline="Reference · Single Page"
        title="Application Documentation"
        subtitle="Everything about how Vapour Garage ERP is built and how it works — architecture, modules, roles, and scheduled jobs."
    />

    {{-- In-page jump nav --}}
    <div class="sticky top-0 z-30 -mx-4 md:-mx-8 px-4 md:px-8 py-3 mb-6 bg-cream/95 backdrop-blur border-b border-cream3 overflow-x-auto">
        <nav class="flex items-center gap-1.5 whitespace-nowrap text-[11px] font-mono">
            @foreach([
                'overview'    => 'Overview',
                'architecture'=> 'Architecture',
                'security'    => 'Auth & Security',
                'roles'       => 'Roles & Permissions',
                'branches'    => 'Branch Scoping',
                'modules'     => 'Modules',
                'jobs'        => 'Scheduled Jobs',
                'api'         => 'API',
            ] as $anchor => $label)
                <a href="#{{ $anchor }}"
                   class="px-2.5 py-1.5 rounded-md text-ink3 hover:text-ink hover:bg-cream2 transition-colors">
                    {{ $label }}
                </a>
            @endforeach
        </nav>
    </div>

    <div class="flex flex-col gap-6 max-w-5xl">

        {{-- ══════════════════════════ Overview ══════════════════════════ --}}
        <x-card title="Overview" id="overview" class="scroll-mt-20">
            <p class="text-[13px] text-ink2 leading-relaxed mb-4">
                <strong class="text-ink">Vapour Garage ERP</strong> is a multi-branch retail management platform
                for a vape/e-juice retailer. One Laravel codebase serves four surfaces from the same domain
                logic: the authenticated back-office ERP (this application), a JSON REST API, a public online
                storefront, and an AI shopping/support chatbot.
            </p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @foreach([
                    ['ERP Back Office', 'Blade + Alpine.js admin app — POS, inventory, purchasing, HR, payroll, reporting.'],
                    ['REST API', 'Sanctum-authenticated JSON API mirroring the same business domains.'],
                    ['Online Storefront', 'Public shop — browse, cart, checkout, order tracking. No ERP auth required.'],
                    ['AI Chatbot', 'OpenAI-powered assistant at /chatbot, rate-limited to 20 requests/minute.'],
                ] as [$t, $d])
                    <div class="bg-cream rounded-lg p-3.5">
                        <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-1.5">{{ $t }}</p>
                        <p class="text-[11.5px] text-ink3 leading-snug">{{ $d }}</p>
                    </div>
                @endforeach
            </div>
        </x-card>

        {{-- ══════════════════════════ Tech stack + Architecture ══════════════════════════ --}}
        <x-card title="Architecture" id="architecture" class="scroll-mt-20">

            <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-2">Technology Stack</p>
            <div class="flex flex-wrap gap-1.5 mb-5">
                @foreach(['PHP 8.3','Laravel 13','Blade','Alpine.js','Tailwind CSS','Vite','Laravel Sanctum','Laravel Breeze','Spatie Laravel Permission','Redis / Predis','OpenAI PHP Client','Resend','Symfony Brevo Mailer','MySQL'] as $t)
                    <x-badge variant="navy">{{ $t }}</x-badge>
                @endforeach
            </div>

            <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-2">Layering</p>
            <p class="text-[13px] text-ink2 leading-relaxed mb-3">
                The codebase follows a strict <strong class="text-ink">Repository → Service → Controller</strong>
                dependency direction. Controllers stay thin — validate, authorize, call a service, return a
                response. All business rules live in services. Repositories are the only layer that talks to
                Eloquent, and every repository sits behind a contract so it can be swapped or mocked.
            </p>
            <div class="bg-sidebar text-white rounded-lg p-4 font-mono text-[11px] leading-loose mb-4">
                Controller<br>
                &nbsp;&nbsp;↓<br>
                Service&nbsp;&nbsp;<span class="text-white/40">(app/Services — business/domain logic)</span><br>
                &nbsp;&nbsp;↓<br>
                Repository Contract&nbsp;&nbsp;<span class="text-white/40">(app/Contracts/Repositories)</span><br>
                &nbsp;&nbsp;↓<br>
                Repository Implementation&nbsp;&nbsp;<span class="text-white/40">(app/Repositories, extends BaseRepository)</span><br>
                &nbsp;&nbsp;↓<br>
                Eloquent / MySQL
            </div>

            <p class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 mb-2">Controller Namespaces</p>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                @foreach([
                    ['Web/', 'ERP back-office Blade pages. Sits behind auth + verified middleware.'],
                    ['Api/', 'JSON REST API. Sanctum bearer-token auth, mirrors the Web domains.'],
                    ['Shop/', 'Public storefront. No ERP auth — its own throttling instead.'],
                    ['Auth/', 'Session login, password reset, 2FA challenge/setup.'],
                ] as [$t, $d])
                    <div class="bg-cream rounded-lg p-3.5">
                        <p class="font-mono text-[11px] text-terra mb-1">{{ $t }}</p>
                        <p class="text-[11.5px] text-ink3 leading-snug">{{ $d }}</p>
                    </div>
                @endforeach
            </div>
            <p class="text-[11.5px] text-ink4 mt-3">
                Web and API controllers for the same domain always route through the same Service — business
                logic is never duplicated between them.
            </p>
        </x-card>

        {{-- ══════════════════════════ Auth & Security ══════════════════════════ --}}
        <x-card title="Authentication & Security" id="security" class="scroll-mt-20">
            <dl class="flex flex-col gap-4">
                @foreach([
                    'Web sessions'   => 'Laravel session auth via Breeze scaffolding. Accounts are provisioned by an admin (Employees → “Create login account”) — public self-registration is intentionally disabled.',
                    'API tokens'     => 'Laravel Sanctum bearer tokens, issued via POST /api/auth/login. Protected routes require auth:sanctum and a per-user throttle:120,1 ceiling.',
                    '2FA'            => 'Optional TOTP two-factor authentication (pragmarx/google2fa-laravel). Secret encrypted at rest, recovery codes hashed and single-use, per-user+IP rate-limited on challenge.',
                    'Authorization'  => 'Spatie laravel-permission ability strings, checked with $this->authorize(\'permission name\') or @can in Blade. There are no Policy classes and no route-level permission middleware — every check happens inside the controller/service.',
                    'Self-service exception' => 'A caller can always view their own payslip and profile even without the \'view payroll\' permission — ownership bypasses the gate, but only for the caller\'s own record.',
                ] as $label => $value)
                    <div class="flex flex-col md:flex-row md:items-start gap-1 md:gap-4 border-b border-cream2 pb-3 last:border-0 last:pb-0">
                        <dt class="font-mono text-[9px] tracking-[0.14em] uppercase text-ink4 md:w-40 flex-shrink-0 pt-0.5">{{ $label }}</dt>
                        <dd class="text-[12.5px] text-ink2 leading-relaxed">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-card>

        {{-- ══════════════════════════ Roles & Permissions ══════════════════════════ --}}
        <x-card title="Roles & Permissions" id="roles" class="scroll-mt-20">
            <p class="text-[13px] text-ink2 leading-relaxed mb-4">
                Permissions are plain ability strings (e.g. <code class="font-mono text-[11px] bg-cream px-1.5 py-0.5 rounded">view payroll</code>,
                <code class="font-mono text-[11px] bg-cream px-1.5 py-0.5 rounded">approve stock transfers</code>) grouped into six seeded
                roles. <code class="font-mono text-[11px] bg-cream px-1.5 py-0.5 rounded">super_admin</code> and
                <code class="font-mono text-[11px] bg-cream px-1.5 py-0.5 rounded">admin</code> hold every permission and are exempt from
                branch scoping; every other role is pinned to its assigned branch.
            </p>
            <div class="overflow-x-auto -mx-1">
                <table class="w-full text-[11.5px]">
                    <thead>
                        <tr class="border-b border-cream3">
                            <th class="text-left font-mono text-[9px] tracking-[0.1em] uppercase text-ink4 pb-2 pl-1">Role</th>
                            <th class="text-left font-mono text-[9px] tracking-[0.1em] uppercase text-ink4 pb-2">Scope</th>
                            <th class="text-left font-mono text-[9px] tracking-[0.1em] uppercase text-ink4 pb-2">Access</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach([
                            ['super_admin',     'purple', 'All branches', 'Every permission. Can switch active branch from the topbar.'],
                            ['admin',           'purple', 'All branches', 'Every permission — mirrors super_admin.'],
                            ['branch_manager',  'navy',   'Own branch',   'Full operational + HR/user management within their branch: products, inventory, transfers, purchasing, sales, customers, discounts, users, reports.'],
                            ['supervisor',      'muted',  'Own branch',   'Day-to-day operations without admin/HR financials: products, inventory, transfers, purchasing, sales, customers.'],
                            ['cashier',         'terra',  'Own branch',   'POS only — view products/inventory, create sales, apply discounts, manage customers.'],
                            ['inventory_clerk', 'muted',  'Own branch',   'Stock-focused: products, inventory adjustments, transfers, purchasing, plus sales creation.'],
                        ] as [$role, $variant, $scope, $access])
                            <tr class="border-b border-cream2 last:border-0">
                                <td class="py-2.5 pl-1"><x-badge :variant="$variant">{{ $role }}</x-badge></td>
                                <td class="py-2.5 font-mono text-[10.5px] text-ink3">{{ $scope }}</td>
                                <td class="py-2.5 text-ink2 leading-snug">{{ $access }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-[11px] text-ink4 mt-3 font-mono">
                Full permission list, module groupings, and each role's exact grant are managed via Roles →
                Permissions in the sidebar (requires 'manage roles' / 'manage permissions').
            </p>
        </x-card>

        {{-- ══════════════════════════ Branch Scoping ══════════════════════════ --}}
        <x-card title="Branch Scoping" id="branches" class="scroll-mt-20">
            <p class="text-[13px] text-ink2 leading-relaxed mb-3">
                Vapour Garage is multi-branch. The branch a request operates against is resolved by
                <code class="font-mono text-[11px] bg-cream px-1.5 py-0.5 rounded">ResolvesActiveBranch::resolveEffectiveBranchId()</code>:
                non-super-admins are always pinned to their own <code class="font-mono text-[11px] bg-cream px-1.5 py-0.5 rounded">branch_id</code>;
                super-admins can switch branches from the topbar (stored in the <code class="font-mono text-[11px] bg-cream px-1.5 py-0.5 rounded">active_branch_id</code>
                session key, where <code class="font-mono text-[11px] bg-cream px-1.5 py-0.5 rounded">0</code> is the "All Branches" sentinel).
            </p>
            <p class="text-[13px] text-ink2 leading-relaxed">
                Single-record write actions (approve a transfer, release a payslip, edit an employee, etc.) are
                additionally guarded by an <code class="font-mono text-[11px] bg-cream px-1.5 py-0.5 rounded">EnforcesBranchAccess</code>
                trait shared by both services and controllers — it aborts with 403 unless the acting user is a
                super-admin or the record actually belongs to their branch, so a permission alone is never
                enough to reach another branch's data by guessing an ID.
            </p>
        </x-card>

        {{-- ══════════════════════════ Modules ══════════════════════════ --}}
        <x-card title="Modules" id="modules" class="scroll-mt-20">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach([
                    ['Point of Sale',        'monitor',     'Cashiers ring up sales against live branch stock. Pricing/tax/discounts are always recomputed server-side; stock deduction, sale creation, and loyalty points run in one DB transaction with idempotency-key + per-cashier locking to survive double-submits.'],
                    ['Products & Categories','box',         'Catalogue with cost/selling price, tax rate, reorder level, sale/wholesale flags, and per-branch stock via BranchInventory.'],
                    ['Inventory',            'list',        'Per-branch on-hand/reserved/available stock. Manual adjustments are audit-logged with a reason.'],
                    ['Stock Transfers',      'repeat',      'Move stock between two branches: request → approve → dispatch → receive (or reject/cancel). Receiving locks the transfer row so it can\'t be double-processed, and deducts source / credits destination atomically.'],
                    ['Purchasing',           'shopping-bag','Purchase orders against suppliers: draft → approve → receive (partial receiving supported) → closed, or cancel. Inventory only increases on receive, never on create/approve.'],
                    ['Suppliers',            'users',       'Vendor directory feeding purchase orders.'],
                    ['Sales & Period History','activity',   'Transaction history, voids (with inventory/loyalty reversal), and closed-period snapshots (SalesPeriodSummary) for daily/weekly/monthly/yearly reporting.'],
                    ['Customers',            'users',       'Loyalty program — tiers and points earned/redeemed per completed sale.'],
                    ['Online Orders',        'shopping-bag','Storefront checkout orders. Staff move an order through pending → confirmed → processing → ready → completed; completing one creates a real SalesTransaction and decrements stock, so it\'s guarded the same way POS sales are.'],
                    ['Employees & Attendance','user',       'Staff records, clock-in/out, manual attendance entry, monthly summaries used by payroll generation.'],
                    ['Payroll',              'credit-card', 'Per-period payroll generation from attendance (base pay, OT, PH statutory deductions), then draft → approved → released. Generation is protected against duplicate runs by a DB unique constraint.'],
                    ['Branches',             'home',        'The organizational unit everything else scopes to.'],
                    ['Users, Roles & Permissions','shield',  'Admin UI over Spatie roles/permissions and ERP user accounts — who can log in, what they can do, and which branch they\'re pinned to.'],
                    ['Reporting & Audit',    'activity',     'Dashboard KPIs, CSV/PDF exports, and an AuditLog trail (who/what/when/old→new) for sensitive state changes across most modules.'],
                ] as [$t, $icon, $d])
                    <div class="flex gap-3 bg-cream rounded-lg p-3.5">
                        <div class="w-8 h-8 rounded-md bg-sidebar text-white flex items-center justify-center flex-shrink-0">
                            <x-icon :name="$icon" class="w-4 h-4" />
                        </div>
                        <div>
                            <p class="text-[12.5px] font-semibold text-ink mb-1">{{ $t }}</p>
                            <p class="text-[11.5px] text-ink3 leading-snug">{{ $d }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>

        {{-- ══════════════════════════ Scheduled Jobs ══════════════════════════ --}}
        <x-card title="Scheduled Jobs & Console Commands" id="jobs" class="scroll-mt-20">
            <p class="text-[13px] text-ink2 leading-relaxed mb-4">
                Background work runs as Artisan commands registered in <code class="font-mono text-[11px] bg-cream px-1.5 py-0.5 rounded">routes/console.php</code>
                and driven by <code class="font-mono text-[11px] bg-cream px-1.5 py-0.5 rounded">schedule:run</code> (cron: <code class="font-mono text-[11px] bg-cream px-1.5 py-0.5 rounded">* * * * *</code>).
                Queued mail uses the <code class="font-mono text-[11px] bg-cream px-1.5 py-0.5 rounded">database</code> queue driver by default — a
                <code class="font-mono text-[11px] bg-cream px-1.5 py-0.5 rounded">queue:work</code> process must be running for queued jobs to send.
            </p>
            <div class="overflow-x-auto -mx-1">
                <table class="w-full text-[11.5px]">
                    <thead>
                        <tr class="border-b border-cream3">
                            <th class="text-left font-mono text-[9px] tracking-[0.1em] uppercase text-ink4 pb-2 pl-1">Command</th>
                            <th class="text-left font-mono text-[9px] tracking-[0.1em] uppercase text-ink4 pb-2">Schedule</th>
                            <th class="text-left font-mono text-[9px] tracking-[0.1em] uppercase text-ink4 pb-2">What it does</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach([
                            ['sales:close-period {period}',  '00:05 / 00:10 (Mon) / 00:15 (1st) / 00:20 (Jan 1)', 'Snapshots revenue/tax/discount/voids into SalesPeriodSummary per branch + an all-branches aggregate. Idempotent — locks the row and skips an already-closed period unless --force.'],
                            ['sales:send-report {period}',   '12:00 AM daily · 7 AM Mon (weekly) · 7 AM 1st (monthly) · 8 AM Jan 1 (yearly)', 'Emails a revenue/branch/top-products report to every user holding \'export reports\'. Queued per recipient so one mail failure doesn\'t block the rest.'],
                            ['attendance:auto-clockout',     '00:30 daily', 'Force-closes any attendance record left open from a previous day (forgotten clock-out), anchoring hours to a fixed end-of-shift time rather than "now".'],
                            ['orders:cancel-stale --hours=48','Hourly', 'Cancels storefront orders still \'pending\' (never confirmed by staff) past the given age. Safe to automate — a pending order hasn\'t touched inventory yet.'],
                            ['queue:prune-failed --hours=168','Daily', 'Built-in Laravel housekeeping — clears failed_jobs older than 7 days.'],
                            ['sanctum:prune-expired --hours=24','Daily', 'Built-in Sanctum housekeeping — currently a no-op since token expiration isn\'t configured.'],
                        ] as [$cmd, $sched, $desc])
                            <tr class="border-b border-cream2 last:border-0">
                                <td class="py-2.5 pl-1 font-mono text-[10.5px] text-terra whitespace-nowrap">{{ $cmd }}</td>
                                <td class="py-2.5 font-mono text-[10px] text-ink4 whitespace-nowrap">{{ $sched }}</td>
                                <td class="py-2.5 text-ink2 leading-snug">{{ $desc }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>

        {{-- ══════════════════════════ API ══════════════════════════ --}}
        <x-card title="API" id="api" class="scroll-mt-20">
            <p class="text-[13px] text-ink2 leading-relaxed mb-3">
                A parallel JSON interface to the same domains, under <code class="font-mono text-[11px] bg-cream px-1.5 py-0.5 rounded">/api</code>.
                Get a token with <code class="font-mono text-[11px] bg-cream px-1.5 py-0.5 rounded">POST /api/auth/login</code>, then send it as a
                Sanctum bearer token. Every protected endpoint enforces its own <code class="font-mono text-[11px] bg-cream px-1.5 py-0.5 rounded">$this->authorize()</code>
                check and branch scoping independently of the Web UI — hiding a button is never treated as
                sufficient access control.
            </p>
            <p class="text-[11.5px] text-ink4">
                Covers: Auth, Products, Categories, Inventory, Suppliers, Purchase Orders, Sales Transactions,
                Customers, Discounts, Employees, Departments, Attendance, Payroll, Branches, and Users
                (roles/permissions sync).
            </p>
        </x-card>

    </div>

</x-app-layout>
