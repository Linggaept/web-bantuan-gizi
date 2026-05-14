# Lurah Dashboard Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a read-only + approval web dashboard for Lurah using Livewire v4 + Blade + Tailwind CSS v4, with session auth shared with admin (role-based redirect).

**Architecture:** Livewire v4 components query Eloquent directly. Shared `/login` page redirects by role: admin→`/dashboard`, lurah→`/lurah`. `WebRoleMiddleware` (existing) guards `/lurah/*` for `role=lurah`. Separate `layouts/lurah.blade.php` with 3 nav links.

**Tech Stack:** Laravel 13, PHP 8.3, Livewire v4, Tailwind CSS v4, Chart.js (already installed), Alpine.js, Pest v4

---

## File Map

**New files:**
```
app/Livewire/Lurah/Dashboard.php
app/Livewire/Lurah/ApprovalTable.php
app/Livewire/Lurah/LaporanTable.php
resources/views/layouts/lurah.blade.php
resources/views/livewire/lurah/dashboard.blade.php
resources/views/livewire/lurah/approval-table.blade.php
resources/views/livewire/lurah/laporan-table.blade.php
tests/Feature/Web/LurahAuthTest.php
tests/Feature/Web/LurahDashboardTest.php
tests/Feature/Web/LurahApprovalTest.php
tests/Feature/Web/LurahLaporanTest.php
```

**Modified files:**
```
app/Http/Controllers/Auth/AdminAuthController.php   ← login() redirect by role
routes/web.php                                      ← add /lurah/* routes
```

---

## Task 1: Routes + Layout + Auth Redirect

**Files:**
- Modify: `app/Http/Controllers/Auth/AdminAuthController.php`
- Modify: `routes/web.php`
- Create: `resources/views/layouts/lurah.blade.php`
- Create: `app/Livewire/Lurah/Dashboard.php` (stub)
- Create: `app/Livewire/Lurah/ApprovalTable.php` (stub)
- Create: `app/Livewire/Lurah/LaporanTable.php` (stub)
- Create: `tests/Feature/Web/LurahAuthTest.php`

- [ ] **Step 1: Write failing test**

Create `tests/Feature/Web/LurahAuthTest.php`:

```php
<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('lurah is redirected to /lurah after login', function () {
    User::factory()->lurah()->create([
        'email' => 'lurah@test.com',
        'password' => bcrypt('password'),
    ]);

    $this->post('/login', ['email' => 'lurah@test.com', 'password' => 'password'])
        ->assertRedirect('/lurah');
});

test('admin is still redirected to /dashboard after login', function () {
    User::factory()->admin()->create([
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
    ]);

    $this->post('/login', ['email' => 'admin@test.com', 'password' => 'password'])
        ->assertRedirect('/dashboard');
});

test('lurah can view /lurah dashboard', function () {
    $lurah = User::factory()->lurah()->create();
    $this->actingAs($lurah)->get('/lurah')->assertOk();
});

test('admin cannot access /lurah', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->get('/lurah')->assertForbidden();
});

test('lurah cannot access /dashboard', function () {
    $lurah = User::factory()->lurah()->create();
    $this->actingAs($lurah)->get('/dashboard')->assertForbidden();
});

test('guest is redirected to login from /lurah', function () {
    $this->get('/lurah')->assertRedirect('/login');
});

test('lurah can logout', function () {
    $lurah = User::factory()->lurah()->create();
    $this->actingAs($lurah)->post('/logout')->assertRedirect('/login');
    $this->assertGuest();
});
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test --compact --filter=LurahAuthTest
```

Expected: FAIL — routes not found.

- [ ] **Step 3: Add lurah() state to UserFactory if missing**

Check `database/factories/UserFactory.php`. If no `lurah()` state, add it alongside `admin()` and `operator()`:

```php
public function lurah(): static
{
    return $this->state(fn (array $attributes) => [
        'role' => 'lurah',
    ]);
}
```

- [ ] **Step 4: Create Livewire stub classes**

```bash
php artisan make:livewire Lurah/Dashboard --no-interaction
php artisan make:livewire Lurah/ApprovalTable --no-interaction
php artisan make:livewire Lurah/LaporanTable --no-interaction
```

- [ ] **Step 5: Update routes/web.php — add lurah routes**

Add after the existing `/dashboard` group:

```php
use App\Livewire\Lurah\ApprovalTable as LurahApproval;
use App\Livewire\Lurah\Dashboard as LurahDashboard;
use App\Livewire\Lurah\LaporanTable as LurahLaporan;

Route::prefix('lurah')->middleware(['auth', 'web.role:lurah'])->group(function () {
    Route::get('/', LurahDashboard::class)->name('lurah.dashboard');
    Route::get('/approval', LurahApproval::class)->name('lurah.approval');
    Route::get('/laporan', LurahLaporan::class)->name('lurah.laporan');
    Route::get('/laporan/print', [AdminAuthController::class, 'laporanPrint'])->name('lurah.laporan.print');
});
```

- [ ] **Step 6: Update AdminAuthController::login() to redirect by role**

Replace `return redirect()->intended(route('dashboard'));` with:

```php
$role = Auth::user()->role;

return redirect()->intended(match ($role) {
    'lurah' => route('lurah.dashboard'),
    default => route('dashboard'),
});
```

Full updated `login()` method:

```php
public function login(Request $request): RedirectResponse
{
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    if (! Auth::attempt($credentials, $request->boolean('remember'))) {
        return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
    }

    $request->session()->regenerate();

    $role = Auth::user()->role;

    return redirect()->intended(match ($role) {
        'lurah' => route('lurah.dashboard'),
        default => route('dashboard'),
    });
}
```

- [ ] **Step 7: Create `resources/views/layouts/lurah.blade.php`**

```html
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }} - Lurah</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 min-h-screen">

    <nav x-data="{ open: false }" class="bg-white border-b border-gray-200 px-4 sm:px-6 py-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 bg-green-600 rounded flex items-center justify-center">
                    <span class="text-white text-xs font-bold">L</span>
                </div>
                <span class="font-semibold text-gray-800">Lansia</span>
            </div>

            {{-- Desktop nav --}}
            <div class="hidden md:flex items-center gap-1">
                <a href="{{ route('lurah.dashboard') }}"
                   class="px-4 py-2 text-sm rounded {{ request()->routeIs('lurah.dashboard') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-600 hover:text-gray-800' }}">
                    Dashboard
                </a>
                <a href="{{ route('lurah.approval') }}"
                   class="px-4 py-2 text-sm rounded {{ request()->routeIs('lurah.approval') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-600 hover:text-gray-800' }}">
                    Approval Bantuan
                </a>
                <a href="{{ route('lurah.laporan') }}"
                   class="px-4 py-2 text-sm rounded {{ request()->routeIs('lurah.laporan*') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-600 hover:text-gray-800' }}">
                    Laporan
                </a>
            </div>

            {{-- Desktop logout --}}
            <div class="hidden md:flex items-center gap-4">
                <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-red-500 hover:text-red-700">Logout</button>
                </form>
            </div>

            {{-- Mobile --}}
            <div class="flex items-center gap-2 md:hidden">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-red-500 hover:text-red-700">Logout</button>
                </form>
                <button @click="open = !open" class="p-2 rounded text-gray-600 hover:bg-gray-100 focus:outline-none" aria-label="Toggle menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" style="display:none"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile dropdown --}}
        <div x-show="open" class="md:hidden border-t mt-2 pb-2" style="display:none">
            <div class="flex flex-col pt-2 gap-1">
                <a href="{{ route('lurah.dashboard') }}"
                   class="px-4 py-2 text-sm rounded {{ request()->routeIs('lurah.dashboard') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-600 hover:text-gray-800' }}">
                    Dashboard
                </a>
                <a href="{{ route('lurah.approval') }}"
                   class="px-4 py-2 text-sm rounded {{ request()->routeIs('lurah.approval') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-600 hover:text-gray-800' }}">
                    Approval Bantuan
                </a>
                <a href="{{ route('lurah.laporan') }}"
                   class="px-4 py-2 text-sm rounded {{ request()->routeIs('lurah.laporan*') ? 'bg-green-50 text-green-600 font-medium' : 'text-gray-600 hover:text-gray-800' }}">
                    Laporan
                </a>
                <div class="px-4 py-2 text-sm text-gray-500 border-t mt-1 pt-2">{{ auth()->user()->name }}</div>
            </div>
        </div>
    </nav>

    <main class="p-4 sm:p-6">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
```

- [ ] **Step 8: Run tests**

```bash
php artisan test --compact --filter=LurahAuthTest
```

Expected: 7/7 PASS.

- [ ] **Step 9: Run Pint**

```bash
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 10: Commit**

```bash
git add app/Http/Controllers/Auth/AdminAuthController.php routes/web.php resources/views/layouts/lurah.blade.php app/Livewire/Lurah/ database/factories/UserFactory.php tests/Feature/Web/LurahAuthTest.php
git commit -m "feat: add lurah routes, layout, and role-based login redirect"
```

---

## Task 2: Lurah Dashboard Page

**Files:**
- Create: `app/Livewire/Lurah/Dashboard.php`
- Create: `resources/views/livewire/lurah/dashboard.blade.php`
- Create: `tests/Feature/Web/LurahDashboardTest.php`

- [ ] **Step 1: Write failing test**

Create `tests/Feature/Web/LurahDashboardTest.php`:

```php
<?php

use App\Livewire\Lurah\Dashboard;
use App\Models\BantuanGizi;
use App\Models\Lansia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('lurah can view dashboard', function () {
    $lurah = User::factory()->lurah()->create();
    $this->actingAs($lurah)->get('/lurah')->assertOk();
});

test('dashboard shows total lansia stat', function () {
    $lurah = User::factory()->lurah()->create();
    $operator = User::factory()->operator()->create();
    Lansia::factory(7)->create(['created_by' => $operator->id]);

    Livewire::actingAs($lurah)
        ->test(Dashboard::class)
        ->assertSeeText('Total Lansia');
});

test('dashboard shows total penerima bantuan', function () {
    $lurah = User::factory()->lurah()->create();
    $operator = User::factory()->operator()->create();
    $lansia = Lansia::factory()->create(['created_by' => $operator->id]);
    BantuanGizi::factory()->create(['lansia_id' => $lansia->lansia_id, 'status_penerima' => 'penerima']);

    Livewire::actingAs($lurah)
        ->test(Dashboard::class)
        ->assertSeeText('Penerima Bantuan');
});

test('dashboard shows pending approval count', function () {
    $lurah = User::factory()->lurah()->create();
    $operator = User::factory()->operator()->create();
    $lansia = Lansia::factory()->create(['created_by' => $operator->id]);
    BantuanGizi::factory()->create(['lansia_id' => $lansia->lansia_id, 'status_penerima' => 'penerima', 'approved_at' => null]);

    Livewire::actingAs($lurah)
        ->test(Dashboard::class)
        ->assertSeeText('Pending Approval');
});
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test --compact --filter=LurahDashboardTest
```

Expected: FAIL.

- [ ] **Step 3: Replace `app/Livewire/Lurah/Dashboard.php`**

```php
<?php

namespace App\Livewire\Lurah;

use App\Models\BantuanGizi;
use App\Models\Lansia;
use App\Models\PemeriksaanKesehatan;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.lurah')]
#[Title('Dashboard Lurah')]
class Dashboard extends Component
{
    public function render()
    {
        $totalLansia = Lansia::count();

        $totalPenerima = BantuanGizi::where('status_penerima', 'penerima')->count();

        $pendingApproval = BantuanGizi::where('status_penerima', 'penerima')
            ->whereNull('approved_at')
            ->count();

        $perRw = Lansia::select('rw', DB::raw('count(*) as total'))
            ->groupBy('rw')
            ->orderByDesc('total')
            ->first();

        $distribusiUsia = Lansia::get()->groupBy(function (Lansia $l) {
            $usia = $l->usia;
            if ($usia < 65) {
                return '60-64';
            } elseif ($usia < 70) {
                return '65-69';
            } elseif ($usia < 75) {
                return '70-74';
            } elseif ($usia < 80) {
                return '75-79';
            } else {
                return '80+';
            }
        })->map(fn ($g) => $g->count());

        $chartLabels = ['60-64', '65-69', '70-74', '75-79', '80+'];
        $chartData = collect($chartLabels)->map(fn ($label) => $distribusiUsia[$label] ?? 0)->values();

        $kondisiStats = PemeriksaanKesehatan::whereIn(
            'pemeriksaan_id',
            PemeriksaanKesehatan::select(DB::raw('MAX(pemeriksaan_id)'))->groupBy('lansia_id')
        )
            ->select('hasil_periksa', DB::raw('count(*) as total'))
            ->groupBy('hasil_periksa')
            ->get()
            ->keyBy('hasil_periksa');

        $distribusiPerRw = Lansia::select(
            'lansia.rw',
            DB::raw('count(distinct lansia.lansia_id) as total_lansia'),
            DB::raw('sum(case when bg.status_penerima = "penerima" then 1 else 0 end) as total_penerima'),
            DB::raw('sum(case when bg.status_penerima = "tidak_penerima" then 1 else 0 end) as tidak_penerima'),
            DB::raw('sum(case when bg.approved_at is not null then 1 else 0 end) as total_approved')
        )
            ->leftJoin('bantuan_gizi as bg', 'lansia.lansia_id', '=', 'bg.lansia_id')
            ->groupBy('lansia.rw')
            ->orderBy('lansia.rw')
            ->get();

        return view('livewire.lurah.dashboard', [
            'totalLansia' => $totalLansia,
            'totalPenerima' => $totalPenerima,
            'pendingApproval' => $pendingApproval,
            'rwTerbanyak' => $perRw,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'kondisiSehat' => $kondisiStats['baik']->total ?? 0,
            'kondisiSakit' => ($kondisiStats['sedang']->total ?? 0) + ($kondisiStats['buruk']->total ?? 0),
            'distribusiPerRw' => $distribusiPerRw,
        ]);
    }
}
```

- [ ] **Step 4: Create `resources/views/livewire/lurah/dashboard.blade.php`**

```html
<div>
    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-4 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Lansia</p>
                <p class="text-3xl font-bold text-gray-800">{{ $totalLansia }}</p>
            </div>
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Penerima Bantuan</p>
                <p class="text-3xl font-bold text-green-600">{{ $totalPenerima }}</p>
            </div>
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Pending Approval</p>
                <p class="text-3xl font-bold text-yellow-500">{{ $pendingApproval }}</p>
            </div>
            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">RW {{ $rwTerbanyak?->rw ?? '-' }}</p>
                <p class="text-3xl font-bold text-gray-800">{{ $rwTerbanyak?->total ?? 0 }}</p>
                <p class="text-xs text-gray-400">Lansia Terbanyak</p>
            </div>
            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Chart + Kondisi --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 mb-6">
        {{-- Bar Chart Usia --}}
        <div class="col-span-1 lg:col-span-3 bg-white rounded-lg shadow-sm p-4">
            <h3 class="text-sm font-medium text-gray-700 mb-4">Distribusi Usia Lansia</h3>
            <div
                x-data="{}"
                x-init="
                    new Chart($refs.canvas, {
                        type: 'bar',
                        data: {
                            labels: {{ json_encode($chartLabels) }},
                            datasets: [{
                                label: 'Jumlah Lansia',
                                data: {{ json_encode($chartData) }},
                                backgroundColor: '#16a34a',
                                borderRadius: 4,
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: { legend: { display: false } },
                            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                        }
                    })
                "
            >
                <canvas x-ref="canvas" height="200"></canvas>
            </div>
        </div>

        {{-- Kondisi Kesehatan --}}
        <div class="col-span-1 lg:col-span-2 bg-white rounded-lg shadow-sm p-4">
            <h3 class="text-sm font-medium text-gray-700 mb-4">Kondisi Kesehatan</h3>
            <div class="flex flex-col gap-4 justify-center h-40">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Sehat</span>
                    <span class="text-2xl font-bold text-green-600">{{ $kondisiSehat }}</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    @php $total = $kondisiSehat + $kondisiSakit ?: 1; @endphp
                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ round($kondisiSehat / $total * 100) }}%"></div>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Sakit / Ringan</span>
                    <span class="text-2xl font-bold text-red-500">{{ $kondisiSakit }}</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="bg-red-400 h-2 rounded-full" style="width: {{ round($kondisiSakit / $total * 100) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Distribusi Per RW --}}
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b">
            <h3 class="font-medium text-gray-800 text-sm">Distribusi Penerima Bantuan per RW</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-4 py-3 text-gray-600">RW</th>
                        <th class="text-left px-4 py-3 text-gray-600">Total Lansia</th>
                        <th class="text-left px-4 py-3 text-gray-600">Penerima</th>
                        <th class="text-left px-4 py-3 text-gray-600">Tidak Penerima</th>
                        <th class="text-left px-4 py-3 text-gray-600">Approved</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($distribusiPerRw as $row)
                    <tr class="border-b last:border-0">
                        <td class="px-4 py-3 font-medium">RW {{ $row->rw }}</td>
                        <td class="px-4 py-3">{{ $row->total_lansia }}</td>
                        <td class="px-4 py-3 text-green-600 font-medium">{{ $row->total_penerima }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $row->tidak_penerima }}</td>
                        <td class="px-4 py-3 text-blue-600 font-medium">{{ $row->total_approved }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
```

- [ ] **Step 5: Run tests**

```bash
php artisan test --compact --filter=LurahDashboardTest
```

Expected: 4/4 PASS.

- [ ] **Step 6: Run Pint**

```bash
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 7: Commit**

```bash
git add app/Livewire/Lurah/Dashboard.php resources/views/livewire/lurah/dashboard.blade.php tests/Feature/Web/LurahDashboardTest.php
git commit -m "feat: add lurah dashboard with stat cards, chart, and distribusi per RW"
```

---

## Task 3: Approval Page

**Files:**
- Create: `app/Livewire/Lurah/ApprovalTable.php`
- Create: `resources/views/livewire/lurah/approval-table.blade.php`
- Create: `tests/Feature/Web/LurahApprovalTest.php`

- [ ] **Step 1: Write failing test**

Create `tests/Feature/Web/LurahApprovalTest.php`:

```php
<?php

use App\Livewire\Lurah\ApprovalTable;
use App\Models\BantuanGizi;
use App\Models\Lansia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('lurah can view approval page', function () {
    $lurah = User::factory()->lurah()->create();
    $this->actingAs($lurah)->get('/lurah/approval')->assertOk();
});

test('approval table shows penerima bantuan', function () {
    $lurah = User::factory()->lurah()->create();
    $operator = User::factory()->operator()->create();
    $lansia = Lansia::factory()->create(['nama' => 'Budi Approval', 'created_by' => $operator->id]);
    BantuanGizi::factory()->create([
        'lansia_id' => $lansia->lansia_id,
        'status_penerima' => 'penerima',
        'periode_bulan' => now()->month,
        'periode_tahun' => now()->year,
    ]);

    Livewire::actingAs($lurah)
        ->test(ApprovalTable::class)
        ->assertSeeText('Budi Approval');
});

test('lurah can approve individual bantuan', function () {
    $lurah = User::factory()->lurah()->create();
    $operator = User::factory()->operator()->create();
    $lansia = Lansia::factory()->create(['created_by' => $operator->id]);
    $bantuan = BantuanGizi::factory()->create([
        'lansia_id' => $lansia->lansia_id,
        'status_penerima' => 'penerima',
        'approved_at' => null,
    ]);

    Livewire::actingAs($lurah)
        ->test(ApprovalTable::class)
        ->call('approve', $bantuan->bantuan_id);

    $this->assertDatabaseHas('bantuan_gizi', [
        'bantuan_id' => $bantuan->bantuan_id,
        'approved_by' => $lurah->id,
    ]);
    $this->assertNotNull(BantuanGizi::find($bantuan->bantuan_id)->approved_at);
});

test('lurah can approve all penerima in bulk', function () {
    $lurah = User::factory()->lurah()->create();
    $operator = User::factory()->operator()->create();
    $bulan = now()->month;
    $tahun = now()->year;

    $lansia1 = Lansia::factory()->create(['created_by' => $operator->id]);
    $lansia2 = Lansia::factory()->create(['created_by' => $operator->id]);
    BantuanGizi::factory()->create(['lansia_id' => $lansia1->lansia_id, 'status_penerima' => 'penerima', 'periode_bulan' => $bulan, 'periode_tahun' => $tahun, 'approved_at' => null]);
    BantuanGizi::factory()->create(['lansia_id' => $lansia2->lansia_id, 'status_penerima' => 'penerima', 'periode_bulan' => $bulan, 'periode_tahun' => $tahun, 'approved_at' => null]);

    Livewire::actingAs($lurah)
        ->test(ApprovalTable::class)
        ->call('approveAll');

    expect(BantuanGizi::where('status_penerima', 'penerima')
        ->where('periode_bulan', $bulan)
        ->where('periode_tahun', $tahun)
        ->whereNull('approved_at')
        ->count()
    )->toBe(0);
});
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test --compact --filter=LurahApprovalTest
```

Expected: FAIL.

- [ ] **Step 3: Replace `app/Livewire/Lurah/ApprovalTable.php`**

```php
<?php

namespace App\Livewire\Lurah;

use App\Models\BantuanGizi;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.lurah')]
#[Title('Approval Bantuan Gizi')]
class ApprovalTable extends Component
{
    use WithPagination;

    public int $periodeBulan;
    public int $periodeTahun;

    public function mount(): void
    {
        $this->periodeBulan = now()->month;
        $this->periodeTahun = now()->year;
    }

    public function approve(int $bantuanId): void
    {
        BantuanGizi::findOrFail($bantuanId)->update([
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
    }

    public function approveAll(): void
    {
        BantuanGizi::where('status_penerima', 'penerima')
            ->where('periode_bulan', $this->periodeBulan)
            ->where('periode_tahun', $this->periodeTahun)
            ->whereNull('approved_at')
            ->update([
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
    }

    public function updatingPeriodeBulan(): void
    {
        $this->resetPage();
    }

    public function updatingPeriodeTahun(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = BantuanGizi::with(['lansia', 'approver'])
            ->where('periode_bulan', $this->periodeBulan)
            ->where('periode_tahun', $this->periodeTahun)
            ->orderByDesc('skor_ranking');

        $totalPenerima = (clone $query)->where('status_penerima', 'penerima')->count();
        $totalApproved = (clone $query)->where('status_penerima', 'penerima')->whereNotNull('approved_at')->count();
        $hasPending = (clone $query)->where('status_penerima', 'penerima')->whereNull('approved_at')->exists();

        return view('livewire.lurah.approval-table', [
            'bantuanList' => $query->paginate(20),
            'totalPenerima' => $totalPenerima,
            'totalApproved' => $totalApproved,
            'hasPending' => $hasPending,
        ]);
    }
}
```

- [ ] **Step 4: Create `resources/views/livewire/lurah/approval-table.blade.php`**

```html
<div>
    {{-- Header + Filter --}}
    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 mb-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Approval Penerima Bantuan Gizi</h2>
                @if($totalPenerima > 0)
                <p class="text-sm text-gray-500 mt-1">
                    <span class="text-green-600 font-medium">{{ $totalApproved }}</span> dari
                    <span class="font-medium">{{ $totalPenerima }}</span> penerima sudah disetujui
                </p>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <select wire:model.live="periodeBulan" class="border border-gray-300 rounded px-3 py-2 text-sm">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                    @endforeach
                </select>
                <input wire:model.live="periodeTahun" type="number" min="2020" class="w-24 border border-gray-300 rounded px-3 py-2 text-sm">

                @if($hasPending)
                <button wire:click="approveAll" wire:confirm="Setujui semua penerima periode ini?" class="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700 flex items-center gap-1">
                    ✓ Approve Semua Penerima
                </button>
                @else
                <span class="text-sm text-green-600 font-medium bg-green-50 px-3 py-2 rounded border border-green-200">✓ Semua sudah diapprove</span>
                @endif
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-t">
                    <tr>
                        <th class="text-left px-4 py-3 text-gray-600">No.</th>
                        <th class="text-left px-4 py-3 text-gray-600">Nama</th>
                        <th class="text-left px-4 py-3 text-gray-600">Usia</th>
                        <th class="text-left px-4 py-3 text-gray-600">RW</th>
                        <th class="text-left px-4 py-3 text-gray-600">Skor</th>
                        <th class="text-left px-4 py-3 text-gray-600">Status</th>
                        <th class="text-left px-4 py-3 text-gray-600">Approval</th>
                        <th class="text-left px-4 py-3 text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bantuanList as $bantuan)
                    <tr class="border-b last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-500">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $bantuan->lansia?->nama }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $bantuan->lansia?->usia }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $bantuan->lansia?->rw }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-blue-600">{{ $bantuan->skor_ranking ? number_format($bantuan->skor_ranking, 4) : '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs {{ $bantuan->status_penerima === 'penerima' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $bantuan->status_penerima === 'penerima' ? 'Penerima' : 'Tidak Penerima' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($bantuan->approved_at)
                                <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-700">
                                    ✓ Approved
                                </span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-700">Pending</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($bantuan->status_penerima === 'penerima' && !$bantuan->approved_at)
                            <button wire:click="approve({{ $bantuan->bantuan_id }})" class="text-green-600 hover:text-green-800 text-xs font-medium">
                                Approve
                            </button>
                            @elseif($bantuan->approved_at)
                            <span class="text-xs text-gray-400">{{ $bantuan->approver?->name }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-400 text-sm">Tidak ada data bantuan untuk periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $bantuanList->links() }}
        </div>
    </div>
</div>
```

- [ ] **Step 5: Run tests**

```bash
php artisan test --compact --filter=LurahApprovalTest
```

Expected: 4/4 PASS.

- [ ] **Step 6: Run Pint**

```bash
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 7: Commit**

```bash
git add app/Livewire/Lurah/ApprovalTable.php resources/views/livewire/lurah/approval-table.blade.php tests/Feature/Web/LurahApprovalTest.php
git commit -m "feat: add lurah approval page with individual and bulk approve"
```

---

## Task 4: Laporan Page

**Files:**
- Create: `app/Livewire/Lurah/LaporanTable.php`
- Create: `resources/views/livewire/lurah/laporan-table.blade.php`
- Create: `tests/Feature/Web/LurahLaporanTest.php`

- [ ] **Step 1: Write failing test**

Create `tests/Feature/Web/LurahLaporanTest.php`:

```php
<?php

use App\Livewire\Lurah\LaporanTable;
use App\Models\BantuanGizi;
use App\Models\Lansia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('lurah can view laporan page', function () {
    $lurah = User::factory()->lurah()->create();
    $this->actingAs($lurah)->get('/lurah/laporan')->assertOk();
});

test('laporan shows bantuan data', function () {
    $lurah = User::factory()->lurah()->create();
    $operator = User::factory()->operator()->create();
    $lansia = Lansia::factory()->create(['nama' => 'Pak Lurah Test', 'created_by' => $operator->id]);
    BantuanGizi::factory()->create(['lansia_id' => $lansia->lansia_id, 'status_penerima' => 'penerima']);

    Livewire::actingAs($lurah)
        ->test(LaporanTable::class)
        ->assertSeeText('Pak Lurah Test');
});

test('laporan can filter by jenis', function () {
    $lurah = User::factory()->lurah()->create();
    $operator = User::factory()->operator()->create();
    $l1 = Lansia::factory()->create(['nama' => 'Si Penerima', 'created_by' => $operator->id]);
    $l2 = Lansia::factory()->create(['nama' => 'Si Bukan', 'created_by' => $operator->id]);
    BantuanGizi::factory()->create(['lansia_id' => $l1->lansia_id, 'status_penerima' => 'penerima']);
    BantuanGizi::factory()->create(['lansia_id' => $l2->lansia_id, 'status_penerima' => 'tidak_penerima']);

    Livewire::actingAs($lurah)
        ->test(LaporanTable::class)
        ->set('filterJenis', 'penerima')
        ->assertSeeText('Si Penerima')
        ->assertDontSeeText('Si Bukan');
});

test('lurah can access print view', function () {
    $lurah = User::factory()->lurah()->create();
    $this->actingAs($lurah)->get('/lurah/laporan/print')->assertOk();
});
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test --compact --filter=LurahLaporanTest
```

Expected: FAIL.

- [ ] **Step 3: Replace `app/Livewire/Lurah/LaporanTable.php`**

```php
<?php

namespace App\Livewire\Lurah;

use App\Models\BantuanGizi;
use App\Models\Lansia;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.lurah')]
#[Title('Laporan Bantuan Gizi')]
class LaporanTable extends Component
{
    use WithPagination;

    public string $filterRw = '';
    public string $filterJenis = '';
    public int $filterLimit = 15;

    public function download(): StreamedResponse
    {
        $data = $this->buildQuery()->get();
        $filename = 'laporan-bantuan-gizi-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['No', 'NIK', 'Nama', 'Usia', 'RW', 'Periode', 'Status', 'Approval', 'Skor']);
            $no = 1;
            foreach ($data as $item) {
                fputcsv($handle, [
                    $no++,
                    $item->lansia?->nik,
                    $item->lansia?->nama,
                    $item->lansia?->usia,
                    $item->lansia?->rw,
                    "{$item->periode_bulan}/{$item->periode_tahun}",
                    $item->status_penerima,
                    $item->approved_at ? 'Approved' : 'Pending',
                    $item->skor_ranking,
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function buildQuery()
    {
        $query = BantuanGizi::with(['lansia', 'approver']);

        if ($this->filterRw) {
            $query->whereHas('lansia', fn ($q) => $q->where('rw', $this->filterRw));
        }

        if ($this->filterJenis && $this->filterJenis !== 'semua') {
            $query->where('status_penerima', $this->filterJenis);
        }

        return $query;
    }

    public function render()
    {
        $rwOptions = Lansia::distinct()->orderBy('rw')->pluck('rw');

        return view('livewire.lurah.laporan-table', [
            'laporanList' => $this->buildQuery()->paginate($this->filterLimit),
            'rwOptions' => $rwOptions,
        ]);
    }
}
```

- [ ] **Step 4: Create `resources/views/livewire/lurah/laporan-table.blade.php`**

```html
<div>
    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Laporan Penyaluran Bantuan Gizi</h2>

        {{-- Filter Row --}}
        <div class="flex flex-wrap items-center gap-3 mb-4">
            <select wire:model.live="filterRw" class="border border-gray-300 rounded px-3 py-2 text-sm">
                <option value="">Semua RW</option>
                @foreach($rwOptions as $rw)
                    <option value="{{ $rw }}">RW {{ $rw }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterJenis" class="border border-gray-300 rounded px-3 py-2 text-sm">
                <option value="semua">Semua</option>
                <option value="penerima">Penerima</option>
                <option value="tidak_penerima">Tidak Penerima</option>
            </select>

            <div class="flex flex-wrap gap-2 ml-auto">
                <button wire:click="download" class="flex items-center gap-1 px-3 py-2 border border-gray-300 rounded text-sm text-gray-600 hover:bg-gray-50">
                    ↓ Unduh Laporan
                </button>
                <a href="{{ route('lurah.laporan.print', array_filter(['rw' => $filterRw, 'jenis' => $filterJenis])) }}" target="_blank" class="flex items-center gap-1 px-3 py-2 bg-green-600 text-white rounded text-sm hover:bg-green-700">
                    🖨 Cetak Laporan
                </a>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-t">
                    <tr>
                        <th class="text-left px-4 py-3 text-gray-600">No.</th>
                        <th class="text-left px-4 py-3 text-gray-600">NIK</th>
                        <th class="text-left px-4 py-3 text-gray-600">Nama</th>
                        <th class="text-left px-4 py-3 text-gray-600">Usia</th>
                        <th class="text-left px-4 py-3 text-gray-600">RW</th>
                        <th class="text-left px-4 py-3 text-gray-600">Periode</th>
                        <th class="text-left px-4 py-3 text-gray-600">Status</th>
                        <th class="text-left px-4 py-3 text-gray-600">Approval</th>
                        <th class="text-left px-4 py-3 text-gray-600">Skor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($laporanList as $item)
                    <tr class="border-b last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-500">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $item->lansia?->nik }}</td>
                        <td class="px-4 py-3 font-medium">{{ $item->lansia?->nama }}</td>
                        <td class="px-4 py-3">{{ $item->lansia?->usia }}</td>
                        <td class="px-4 py-3">{{ $item->lansia?->rw }}</td>
                        <td class="px-4 py-3">{{ $item->periode_bulan }}/{{ $item->periode_tahun }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs {{ $item->status_penerima === 'penerima' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $item->status_penerima === 'penerima' ? 'Penerima' : 'Tidak Penerima' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($item->approved_at)
                                <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-700">✓ Approved</span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-700">Pending</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-blue-600">{{ $item->skor_ranking ? number_format($item->skor_ranking, 4) : '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-400 text-sm">Tidak ada data laporan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <p class="text-sm text-gray-500">Menampilkan {{ $laporanList->firstItem() ?? 0 }} sampai {{ $laporanList->lastItem() ?? 0 }} dari {{ $laporanList->total() }} data</p>
            {{ $laporanList->links() }}
        </div>
    </div>
</div>
```

- [ ] **Step 5: Run tests**

```bash
php artisan test --compact --filter=LurahLaporanTest
```

Expected: 4/4 PASS.

- [ ] **Step 6: Run all web tests — confirm nothing broken**

```bash
php artisan test --compact tests/Feature/Web/
```

Expected: All PASS (27 existing + 15 new = 42 total).

- [ ] **Step 7: Run Pint**

```bash
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 8: Commit**

```bash
git add app/Livewire/Lurah/LaporanTable.php resources/views/livewire/lurah/laporan-table.blade.php tests/Feature/Web/LurahLaporanTest.php
git commit -m "feat: add lurah laporan page with filter, download, and print"
```

---

## Self-Review

**Spec coverage:**
- ✅ Login/Logout — Task 1 (shared login, role redirect, logout)
- ✅ Dashboard monitoring (total lansia, distribusi usia, kondisi, per RW) — Task 2
- ✅ Monitoring penerima bantuan per RW — Task 2 (distribusiPerRw table)
- ✅ Persetujuan akhir penerima bantuan gizi — Task 3 (approve + approveAll)
- ✅ Persetujuan laporan penyaluran bantuan — Task 3 (same action = approve bantuan)
- ✅ Lihat laporan bantuan — Task 4
- ✅ Monitoring hasil evaluasi bantuan — Task 4 (approval status column in laporan)

**No placeholders found.**

**Type consistency:** `bantuan_id` (int) used consistently in `approve()`. `periodeBulan`/`periodeTahun` (int) consistent across ApprovalTable.
