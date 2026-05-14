# Admin Kelurahan Web Dashboard Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a full web dashboard for Admin Kelurahan using Livewire v3 + Blade + Tailwind CSS v4 + Chart.js with session auth, covering login, lansia management, bantuan ranking, laporan, and monitoring.

**Architecture:** Livewire v3 components query Eloquent directly (no API calls). Session auth via `Auth::attempt()`. `WebRoleMiddleware` guards all `/dashboard/*` routes for `role=admin`. Chart.js initialized via Alpine.js (bundled with Livewire).

**Tech Stack:** Laravel 13, PHP 8.3, Livewire v3, Tailwind CSS v4, Chart.js, Alpine.js, Pest v4

---

## File Map

**New files:**
```
app/Http/Controllers/Auth/AdminAuthController.php
app/Http/Middleware/WebRoleMiddleware.php
app/Livewire/Admin/Dashboard.php
app/Livewire/Admin/LansiaTable.php
app/Livewire/Admin/LansiaForm.php
app/Livewire/Admin/BantuanManagement.php
app/Livewire/Admin/LaporanTable.php
app/Livewire/Admin/MonitoringTable.php
resources/views/layouts/admin.blade.php
resources/views/auth/login.blade.php
resources/views/livewire/admin/dashboard.blade.php
resources/views/livewire/admin/lansia-table.blade.php
resources/views/livewire/admin/lansia-form.blade.php
resources/views/livewire/admin/bantuan-management.blade.php
resources/views/livewire/admin/laporan-table.blade.php
resources/views/livewire/admin/laporan-print.blade.php
resources/views/livewire/admin/monitoring-table.blade.php
tests/Feature/Web/AuthWebTest.php
tests/Feature/Web/DashboardWebTest.php
tests/Feature/Web/LansiaWebTest.php
tests/Feature/Web/BantuanWebTest.php
tests/Feature/Web/LaporanWebTest.php
tests/Feature/Web/MonitoringWebTest.php
```

**Modified files:**
```
routes/web.php
bootstrap/app.php
resources/js/app.js
package.json (via npm install)
```

---

## Task 1: Install Livewire + Chart.js

**Files:**
- Modify: `composer.json` (via composer)
- Modify: `resources/js/app.js`
- Modify: `package.json` (via npm)

- [ ] **Step 1: Install Livewire v3**

```bash
composer require livewire/livewire --no-interaction
```

Expected: `livewire/livewire` appears in `composer.json`.

- [ ] **Step 2: Install Chart.js**

```bash
npm install chart.js --save
```

- [ ] **Step 3: Update resources/js/app.js to expose Chart.js globally**

Replace `resources/js/app.js`:

```js
import './bootstrap';
import Chart from 'chart.js/auto';
window.Chart = Chart;
```

- [ ] **Step 4: Build assets**

```bash
npm run build
```

Expected: build succeeds without errors.

- [ ] **Step 5: Commit**

```bash
git add composer.json composer.lock package.json package-lock.json resources/js/app.js
git commit -m "feat: install livewire v3 and chart.js"
```

---

## Task 2: WebRoleMiddleware + Routes

**Files:**
- Create: `app/Http/Middleware/WebRoleMiddleware.php`
- Modify: `bootstrap/app.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Create WebRoleMiddleware**

```bash
php artisan make:middleware WebRoleMiddleware --no-interaction
```

Replace `app/Http/Middleware/WebRoleMiddleware.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WebRoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! auth()->check() || ! in_array(auth()->user()->role, $roles)) {
            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}
```

- [ ] **Step 2: Register alias in bootstrap/app.php**

In `bootstrap/app.php`, inside `->withMiddleware(function (Middleware $middleware)`, add `web.role` alias alongside existing `role`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\RoleMiddleware::class,
        'web.role' => \App\Http\Middleware\WebRoleMiddleware::class,
    ]);
})
```

- [ ] **Step 3: Update routes/web.php**

Replace `routes/web.php`:

```php
<?php

use App\Http\Controllers\Auth\AdminAuthController;
use App\Livewire\Admin\BantuanManagement;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\LansiaForm;
use App\Livewire\Admin\LansiaTable;
use App\Livewire\Admin\LaporanTable;
use App\Livewire\Admin\MonitoringTable;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post')->middleware('guest');
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::prefix('dashboard')->middleware(['auth', 'web.role:admin'])->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/lansia', LansiaTable::class)->name('dashboard.lansia');
    Route::get('/lansia/{id}/edit', LansiaForm::class)->name('dashboard.lansia.edit');
    Route::get('/bantuan', BantuanManagement::class)->name('dashboard.bantuan');
    Route::get('/laporan', LaporanTable::class)->name('dashboard.laporan');
    Route::get('/laporan/print', [AdminAuthController::class, 'laporanPrint'])->name('dashboard.laporan.print');
    Route::get('/monitoring', MonitoringTable::class)->name('dashboard.monitoring');
});
```

- [ ] **Step 4: Write failing test**

Create `tests/Feature/Web/AuthWebTest.php`:

```php
<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest is redirected to login', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('admin can view login page', function () {
    $this->get('/login')->assertOk()->assertSeeText('Masuk');
});

test('admin can login with valid credentials', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
    ]);

    $this->post('/login', ['email' => 'admin@test.com', 'password' => 'password'])
        ->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($admin);
});

test('login fails with wrong password', function () {
    User::factory()->admin()->create(['email' => 'admin@test.com', 'password' => bcrypt('secret')]);

    $this->post('/login', ['email' => 'admin@test.com', 'password' => 'wrong'])
        ->assertSessionHasErrors('email');
});

test('operator cannot access admin dashboard', function () {
    $operator = User::factory()->operator()->create();

    $this->actingAs($operator)->get('/dashboard')->assertForbidden();
});

test('admin can logout', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/logout')->assertRedirect('/login');

    $this->assertGuest();
});
```

- [ ] **Step 5: Run test to verify it fails**

```bash
php artisan test --compact --filter=AuthWebTest
```

Expected: FAIL — controller not found.

- [ ] **Step 6: Create AdminAuthController**

```bash
php artisan make:controller Auth/AdminAuthController --no-interaction
```

Replace `app/Http/Controllers/Auth/AdminAuthController.php`:

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\BantuanGizi;
use App\Models\Lansia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

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

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function laporanPrint(Request $request): View
    {
        $query = BantuanGizi::with('lansia');

        if ($request->filled('rw')) {
            $query->whereHas('lansia', fn ($q) => $q->where('rw', $request->rw));
        }

        if ($request->filled('jenis') && $request->jenis !== 'semua') {
            $query->where('status_penerima', $request->jenis);
        }

        $data = $query->get();

        return view('livewire.admin.laporan-print', compact('data'));
    }
}
```

- [ ] **Step 7: Create login view**

Create `resources/views/auth/login.blade.php`:

```html
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Bantuan Gizi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-md p-8 w-full max-w-sm">
        <div class="flex items-center gap-2 mb-6">
            <div class="w-8 h-8 bg-blue-600 rounded flex items-center justify-center">
                <span class="text-white text-xs font-bold">L</span>
            </div>
            <span class="font-semibold text-gray-800">Sistem Bantuan Gizi Lansia</span>
        </div>

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="Email"
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror"
                    required
                    autofocus
                >
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input
                    type="password"
                    name="password"
                    placeholder="Password"
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                >
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded font-medium text-sm hover:bg-blue-700 transition">
                Masuk
            </button>
        </form>
    </div>
</body>
</html>
```

- [ ] **Step 8: Run tests**

```bash
php artisan test --compact --filter=AuthWebTest
```

Expected: 6/6 PASS.

- [ ] **Step 9: Run Pint**

```bash
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 10: Commit**

```bash
git add app/Http/Middleware/WebRoleMiddleware.php app/Http/Controllers/Auth/AdminAuthController.php bootstrap/app.php routes/web.php resources/views/auth/login.blade.php tests/Feature/Web/AuthWebTest.php
git commit -m "feat: add web auth, role middleware, and routes for admin dashboard"
```

---

## Task 3: Admin Layout

**Files:**
- Create: `resources/views/layouts/admin.blade.php`

- [ ] **Step 1: Create admin layout**

Create `resources/views/layouts/admin.blade.php`:

```html
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }} - Admin Kelurahan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 min-h-screen">

    {{-- Navbar --}}
    <nav class="bg-white border-b border-gray-200 px-6 py-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-8">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 bg-blue-600 rounded flex items-center justify-center">
                        <span class="text-white text-xs font-bold">L</span>
                    </div>
                    <span class="font-semibold text-gray-800">Lansia</span>
                </div>

                <div class="flex items-center gap-1">
                    <a href="{{ route('dashboard') }}"
                       class="px-4 py-2 text-sm rounded {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-600 hover:text-gray-800' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('dashboard.lansia') }}"
                       class="px-4 py-2 text-sm rounded {{ request()->routeIs('dashboard.lansia*') ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-600 hover:text-gray-800' }}">
                        Data Lansia
                    </a>
                    <a href="{{ route('dashboard.bantuan') }}"
                       class="px-4 py-2 text-sm rounded {{ request()->routeIs('dashboard.bantuan') ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-600 hover:text-gray-800' }}">
                        Bantuan Gizi
                    </a>
                    <a href="{{ route('dashboard.laporan') }}"
                       class="px-4 py-2 text-sm rounded {{ request()->routeIs('dashboard.laporan*') ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-600 hover:text-gray-800' }}">
                        Laporan
                    </a>
                    <a href="{{ route('dashboard.monitoring') }}"
                       class="px-4 py-2 text-sm rounded {{ request()->routeIs('dashboard.monitoring') ? 'bg-blue-50 text-blue-600 font-medium' : 'text-gray-600 hover:text-gray-800' }}">
                        Monitoring
                    </a>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-red-500 hover:text-red-700">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    {{-- Content --}}
    <main class="p-6">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/layouts/admin.blade.php
git commit -m "feat: add admin layout with navbar"
```

---

## Task 4: Dashboard Page

**Files:**
- Create: `app/Livewire/Admin/Dashboard.php`
- Create: `resources/views/livewire/admin/dashboard.blade.php`
- Create: `tests/Feature/Web/DashboardWebTest.php`

- [ ] **Step 1: Write failing test**

Create `tests/Feature/Web/DashboardWebTest.php`:

```php
<?php

use App\Livewire\Admin\Dashboard;
use App\Models\Lansia;
use App\Models\PemeriksaanKesehatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('admin can view dashboard page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/dashboard')->assertOk();
});

test('dashboard shows correct total lansia', function () {
    $admin = User::factory()->admin()->create();
    Lansia::factory(5)->create(['created_by' => $admin->id]);

    Livewire::actingAs($admin)
        ->test(Dashboard::class)
        ->assertSee('5');
});

test('dashboard shows stat cards', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Dashboard::class)
        ->assertSeeText('Total Lansia');
});

test('unauthenticated cannot view dashboard', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test --compact --filter=DashboardWebTest
```

Expected: FAIL.

- [ ] **Step 3: Create Dashboard Livewire component**

```bash
php artisan make:livewire Admin/Dashboard --no-interaction
```

Replace `app/Livewire/Admin/Dashboard.php`:

```php
<?php

namespace App\Livewire\Admin;

use App\Models\BantuanGizi;
use App\Models\Lansia;
use App\Models\PemeriksaanKesehatan;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        $totalLansia = Lansia::count();

        $perRw = Lansia::select('rw', DB::raw('count(*) as total'))
            ->groupBy('rw')
            ->orderByDesc('total')
            ->first();

        $kondisiStats = PemeriksaanKesehatan::whereIn(
            'pemeriksaan_id',
            PemeriksaanKesehatan::select(DB::raw('MAX(pemeriksaan_id)'))->groupBy('lansia_id')
        )
            ->select('hasil_periksa', DB::raw('count(*) as total'))
            ->groupBy('hasil_periksa')
            ->get()
            ->keyBy('hasil_periksa');

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

        $lansiaTerbaru = Lansia::with(['pemeriksaan' => fn ($q) => $q->latest('tanggal_periksa')->limit(1)])
            ->latest()
            ->limit(5)
            ->get();

        $totalPenerima = BantuanGizi::where('status_penerima', 'penerima')->count();

        return view('livewire.admin.dashboard', [
            'totalLansia' => $totalLansia,
            'rwTerbanyak' => $perRw,
            'kondisiSehat' => $kondisiStats['baik']->total ?? 0,
            'kondisiSakit' => ($kondisiStats['sedang']->total ?? 0) + ($kondisiStats['buruk']->total ?? 0),
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'lansiaTerbaru' => $lansiaTerbaru,
            'totalPenerima' => $totalPenerima,
        ]);
    }
}
```

- [ ] **Step 4: Create dashboard view**

Create `resources/views/livewire/admin/dashboard.blade.php`:

```html
<div>
    {{-- Stat Cards --}}
    <div class="grid grid-cols-4 gap-4 mb-6">
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
                <p class="text-sm text-gray-500">RW {{ $rwTerbanyak?->rw ?? '-' }}</p>
                <p class="text-3xl font-bold text-gray-800">{{ $rwTerbanyak?->total ?? 0 }}</p>
                <p class="text-xs text-gray-400">Lansia</p>
            </div>
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                </svg>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Sehat</p>
                <p class="text-3xl font-bold text-green-600">{{ $kondisiSehat }}</p>
            </div>
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-600 font-bold text-sm">
                {{ $kondisiSehat }}
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Sakit/Ringan</p>
                <p class="text-3xl font-bold text-red-500">{{ $kondisiSakit }}</p>
            </div>
            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center text-red-500 font-bold text-sm">
                {{ $kondisiSakit }}
            </div>
        </div>
    </div>

    {{-- Chart + Table Row --}}
    <div class="grid grid-cols-5 gap-4">
        {{-- Bar Chart --}}
        <div class="col-span-3 bg-white rounded-lg shadow-sm p-4">
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
                                backgroundColor: '#3b82f6',
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

        {{-- Latest Lansia Table --}}
        <div class="col-span-2 bg-white rounded-lg shadow-sm p-4">
            <h3 class="text-sm font-medium text-gray-700 mb-4">Data Lansia Terbaru</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="pb-2">RW</th>
                        <th class="pb-2">Nama</th>
                        <th class="pb-2">Usia</th>
                        <th class="pb-2">Kondisi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lansiaTerbaru as $l)
                    <tr class="border-b last:border-0">
                        <td class="py-2">{{ $l->rw }}</td>
                        <td class="py-2">{{ $l->nama }}</td>
                        <td class="py-2">{{ $l->usia }}</td>
                        <td class="py-2">
                            @php $kondisi = $l->pemeriksaan->first()?->hasil_periksa ?? '-' @endphp
                            <span class="px-2 py-0.5 rounded text-xs {{ $kondisi === 'baik' ? 'bg-green-100 text-green-700' : ($kondisi === 'sedang' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                {{ ucfirst($kondisi) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
```

- [ ] **Step 5: Run tests**

```bash
php artisan test --compact --filter=DashboardWebTest
```

Expected: 4/4 PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Livewire/Admin/Dashboard.php resources/views/livewire/admin/dashboard.blade.php tests/Feature/Web/DashboardWebTest.php
git commit -m "feat: add dashboard page with stat cards and chart"
```

---

## Task 5: Lansia Table Page

**Files:**
- Create: `app/Livewire/Admin/LansiaTable.php`
- Create: `resources/views/livewire/admin/lansia-table.blade.php`
- Create: `tests/Feature/Web/LansiaWebTest.php`

- [ ] **Step 1: Write failing test**

Create `tests/Feature/Web/LansiaWebTest.php`:

```php
<?php

use App\Livewire\Admin\LansiaTable;
use App\Models\Lansia;
use App\Models\Pendataan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('admin can view lansia table page', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->get('/dashboard/lansia')->assertOk();
});

test('lansia table shows all lansia', function () {
    $admin = User::factory()->admin()->create();
    Lansia::factory(3)->create(['nama' => 'Budi Test', 'created_by' => $admin->id]);

    Livewire::actingAs($admin)
        ->test(LansiaTable::class)
        ->assertSeeText('Budi Test');
});

test('lansia table can filter by nama', function () {
    $admin = User::factory()->admin()->create();
    Lansia::factory()->create(['nama' => 'Ahmad Setiawan', 'created_by' => $admin->id]);
    Lansia::factory()->create(['nama' => 'Siti Rahayu', 'created_by' => $admin->id]);

    Livewire::actingAs($admin)
        ->test(LansiaTable::class)
        ->set('search', 'Ahmad')
        ->assertSeeText('Ahmad Setiawan')
        ->assertDontSeeText('Siti Rahayu');
});

test('lansia table can filter by rw', function () {
    $admin = User::factory()->admin()->create();
    Lansia::factory()->create(['rw' => '01', 'nama' => 'Lansia RW01', 'created_by' => $admin->id]);
    Lansia::factory()->create(['rw' => '02', 'nama' => 'Lansia RW02', 'created_by' => $admin->id]);

    Livewire::actingAs($admin)
        ->test(LansiaTable::class)
        ->set('filterRw', '01')
        ->assertSeeText('Lansia RW01')
        ->assertDontSeeText('Lansia RW02');
});

test('admin can delete lansia from table', function () {
    $admin = User::factory()->admin()->create();
    $lansia = Lansia::factory()->create(['created_by' => $admin->id]);

    Livewire::actingAs($admin)
        ->test(LansiaTable::class)
        ->call('deleteLansia', $lansia->lansia_id)
        ->assertDispatched('lansia-deleted');

    $this->assertSoftDeleted('lansia', ['lansia_id' => $lansia->lansia_id]);
});

test('admin can verify pendataan from table', function () {
    $admin = User::factory()->admin()->create();
    $operator = User::factory()->operator()->create();
    $lansia = Lansia::factory()->create(['created_by' => $operator->id]);
    $pendataan = Pendataan::factory()->create(['lansia_id' => $lansia->lansia_id, 'user_id' => $operator->id]);

    Livewire::actingAs($admin)
        ->test(LansiaTable::class)
        ->call('verifikasiLansia', $pendataan->pendataan_id, 'terverifikasi');

    $this->assertDatabaseHas('pendataan', [
        'pendataan_id' => $pendataan->pendataan_id,
        'status_verifikasi' => 'terverifikasi',
        'verified_by' => $admin->id,
    ]);
});
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test --compact --filter=LansiaWebTest
```

Expected: FAIL.

- [ ] **Step 3: Create LansiaTable Livewire component**

```bash
php artisan make:livewire Admin/LansiaTable --no-interaction
```

Replace `app/Livewire/Admin/LansiaTable.php`:

```php
<?php

namespace App\Livewire\Admin;

use App\Models\Lansia;
use App\Models\Pendataan;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Data Lansia')]
class LansiaTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterRw = '';
    public string $filterKondisi = '';
    public string $filterStatus = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function deleteLansia(int $lansiaId): void
    {
        Lansia::findOrFail($lansiaId)->delete();
        $this->dispatch('lansia-deleted');
    }

    public function verifikasiLansia(int $pendataanId, string $status): void
    {
        $pendataan = Pendataan::findOrFail($pendataanId);
        $pendataan->update([
            'status_verifikasi' => $status,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);
    }

    public function render()
    {
        $query = Lansia::with(['pemeriksaan' => fn ($q) => $q->latest('tanggal_periksa')->limit(1), 'pendataan' => fn ($q) => $q->latest()]);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nama', 'like', '%' . $this->search . '%')
                    ->orWhere('nik', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterRw) {
            $query->where('rw', $this->filterRw);
        }

        if ($this->filterKondisi) {
            $query->whereHas('pemeriksaan', function ($q) {
                $q->where('hasil_periksa', $this->filterKondisi)
                    ->whereIn('pemeriksaan_id', function ($sub) {
                        $sub->selectRaw('MAX(pemeriksaan_id)')->from('pemeriksaan_kesehatan')->groupBy('lansia_id');
                    });
            });
        }

        if ($this->filterStatus) {
            $query->whereHas('pendataan', fn ($q) => $q->where('status_verifikasi', $this->filterStatus));
        }

        $rwOptions = Lansia::distinct()->orderBy('rw')->pluck('rw');

        return view('livewire.admin.lansia-table', [
            'lansiaList' => $query->paginate(15),
            'rwOptions' => $rwOptions,
        ]);
    }
}
```

- [ ] **Step 4: Create lansia-table view**

Create `resources/views/livewire/admin/lansia-table.blade.php`:

```html
<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Cari berdasarkan nama atau NIK..."
                class="border border-gray-300 rounded px-3 py-2 text-sm w-64 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            <select wire:model.live="filterRw" class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none">
                <option value="">Semua RW</option>
                @foreach($rwOptions as $rw)
                    <option value="{{ $rw }}">RW {{ $rw }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterKondisi" class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none">
                <option value="">Semua Kondisi</option>
                <option value="baik">Sehat</option>
                <option value="sedang">Sakit Ringan</option>
                <option value="buruk">Sakit</option>
            </select>
            <select wire:model.live="filterStatus" class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none">
                <option value="">Semua Status</option>
                <option value="menunggu">Menunggu</option>
                <option value="terverifikasi">Terverifikasi</option>
                <option value="ditolak">Ditolak</option>
            </select>
        </div>
        <a href="{{ route('dashboard.lansia.edit', 'new') }}" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700 flex items-center gap-1">
            + Tambah Data
        </a>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">No.</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Nama</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Usia</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">RW</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Kondisi</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Status Verifikasi</th>
                    <th class="text-left px-4 py-3 text-gray-600 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lansiaList as $lansia)
                @php
                    $kondisi = $lansia->pemeriksaan->first()?->hasil_periksa ?? null;
                    $pendataan = $lansia->pendataan->first();
                    $statusVerif = $pendataan?->status_verifikasi ?? 'belum_ada';
                @endphp
                <tr class="border-b last:border-0 hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-500">{{ $loop->iteration }}</td>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $lansia->nama }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $lansia->usia }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $lansia->rw }}</td>
                    <td class="px-4 py-3">
                        @if($kondisi)
                        <span class="px-2 py-1 rounded-full text-xs {{ $kondisi === 'baik' ? 'bg-green-100 text-green-700' : ($kondisi === 'sedang' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                            {{ $kondisi === 'baik' ? 'Sehat' : ($kondisi === 'sedang' ? 'Sakit Ringan' : 'Sakit') }}
                        </span>
                        @else
                        <span class="text-gray-400 text-xs">Belum periksa</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($statusVerif === 'terverifikasi')
                            <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-700">Terverifikasi</span>
                        @elseif($statusVerif === 'ditolak')
                            <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-700">Ditolak</span>
                        @elseif($statusVerif === 'menunggu')
                            <span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-700">Menunggu</span>
                        @else
                            <span class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-500">Belum ada</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2" x-data="{ confirmDelete: false }">
                            <a href="{{ route('dashboard.lansia.edit', $lansia->lansia_id) }}" class="text-blue-500 hover:text-blue-700 text-xs">Edit</a>

                            @if($pendataan && $statusVerif === 'menunggu')
                            <button wire:click="verifikasiLansia({{ $pendataan->pendataan_id }}, 'terverifikasi')" class="text-green-500 hover:text-green-700 text-xs">Verifikasi</button>
                            @endif

                            <button @click="confirmDelete = true" class="text-red-400 hover:text-red-600 text-xs">Hapus</button>

                            <div x-show="confirmDelete" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50" style="display:none">
                                <div class="bg-white rounded-lg p-6 shadow-lg">
                                    <p class="text-sm mb-4">Hapus lansia <strong>{{ $lansia->nama }}</strong>?</p>
                                    <div class="flex gap-3">
                                        <button @click="confirmDelete = false" class="px-3 py-1 text-sm border rounded">Batal</button>
                                        <button wire:click="deleteLansia({{ $lansia->lansia_id }})" @click="confirmDelete = false" class="px-3 py-1 text-sm bg-red-500 text-white rounded">Hapus</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-400 text-sm">Tidak ada data lansia.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-4 py-3 border-t">
            {{ $lansiaList->links() }}
        </div>
    </div>
</div>
```

- [ ] **Step 5: Run tests**

```bash
php artisan test --compact --filter=LansiaWebTest
```

Expected: 6/6 PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Livewire/Admin/LansiaTable.php resources/views/livewire/admin/lansia-table.blade.php tests/Feature/Web/LansiaWebTest.php
git commit -m "feat: add lansia table page with search, filter, delete, verifikasi"
```

---

## Task 6: Lansia Form Page (Edit)

**Files:**
- Create: `app/Livewire/Admin/LansiaForm.php`
- Create: `resources/views/livewire/admin/lansia-form.blade.php`

- [ ] **Step 1: Write failing test**

Add to `tests/Feature/Web/LansiaWebTest.php`:

```php
test('admin can edit lansia data', function () {
    $admin = User::factory()->admin()->create();
    $lansia = Lansia::factory()->create(['created_by' => $admin->id]);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\Admin\LansiaForm::class, ['id' => $lansia->lansia_id])
        ->set('nama', 'Nama Diubah')
        ->call('simpan')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('lansia', ['lansia_id' => $lansia->lansia_id, 'nama' => 'Nama Diubah']);
});
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test --compact --filter="admin can edit lansia"
```

Expected: FAIL.

- [ ] **Step 3: Create LansiaForm Livewire component**

```bash
php artisan make:livewire Admin/LansiaForm --no-interaction
```

Replace `app/Livewire/Admin/LansiaForm.php`:

```php
<?php

namespace App\Livewire\Admin;

use App\Models\Lansia;
use App\Models\PemeriksaanKesehatan;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
#[Title('Edit Data Lansia')]
class LansiaForm extends Component
{
    use WithFileUploads;

    public ?int $id = null;
    public ?Lansia $lansia = null;

    #[Validate('required|string|max:255')]
    public string $nama = '';

    #[Validate('required|string|max:5')]
    public string $rw = '';

    #[Validate('required|string|size:16')]
    public string $nik = '';

    #[Validate('required|date')]
    public string $tanggal_lahir = '';

    #[Validate('required|in:L,P')]
    public string $jenis_kelamin = 'L';

    #[Validate('required|string')]
    public string $alamat = '';

    #[Validate('nullable|string|max:5')]
    public string $rt = '';

    #[Validate('nullable|in:baik,sedang,buruk')]
    public string $kondisi_kesehatan = '';

    public $foto_ktp = null;

    public function mount(string $id): void
    {
        if ($id !== 'new') {
            $this->id = (int) $id;
            $this->lansia = Lansia::findOrFail($this->id);
            $this->nama = $this->lansia->nama;
            $this->rw = $this->lansia->rw;
            $this->nik = $this->lansia->nik;
            $this->tanggal_lahir = $this->lansia->tanggal_lahir->toDateString();
            $this->jenis_kelamin = $this->lansia->jenis_kelamin;
            $this->alamat = $this->lansia->alamat;
            $this->rt = $this->lansia->rt ?? '';

            $latestPeriksa = $this->lansia->pemeriksaan()->latest('tanggal_periksa')->first();
            $this->kondisi_kesehatan = $latestPeriksa?->hasil_periksa ?? '';
        }
    }

    public function simpan(): void
    {
        $this->validate();

        $data = [
            'nama' => $this->nama,
            'rw' => $this->rw,
            'nik' => $this->nik,
            'tanggal_lahir' => $this->tanggal_lahir,
            'jenis_kelamin' => $this->jenis_kelamin,
            'alamat' => $this->alamat,
            'rt' => $this->rt ?: null,
        ];

        if ($this->id) {
            $this->lansia->update($data);
            $lansia = $this->lansia;
        } else {
            $data['created_by'] = auth()->id();
            $lansia = Lansia::create($data);
        }

        if ($this->foto_ktp) {
            $path = $this->foto_ktp->store('foto-ktp', 'public');
            $lansia->update(['foto_ktp' => $path]);
        }

        if ($this->kondisi_kesehatan) {
            PemeriksaanKesehatan::create([
                'lansia_id' => $lansia->lansia_id,
                'tanggal_periksa' => now()->toDateString(),
                'hasil_periksa' => $this->kondisi_kesehatan,
            ]);
        }

        $this->redirect(route('dashboard.lansia'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.lansia-form');
    }
}
```

- [ ] **Step 4: Create lansia-form view**

Create `resources/views/livewire/admin/lansia-form.blade.php`:

```html
<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-6">{{ $id ? 'Edit Data Lansia' : 'Tambah Data Lansia' }}</h2>

        <form wire:submit="simpan" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lansia</label>
                <input wire:model="nama" type="text" placeholder="Nama Lansia" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nama') border-red-500 @enderror">
                @error('nama')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">RW</label>
                <select wire:model="rw" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none @error('rw') border-red-500 @enderror">
                    <option value="">Pilih RW</option>
                    @foreach(['01','02','03','04','05','06','07','08','09','10'] as $r)
                        <option value="{{ $r }}">RW {{ $r }}</option>
                    @endforeach
                </select>
                @error('rw')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">NIK</label>
                <input wire:model="nik" type="text" placeholder="16 digit NIK" maxlength="16" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none @error('nik') border-red-500 @enderror">
                @error('nik')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Foto KTP Lansia</label>
                <input wire:model="foto_ktp" type="file" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:bg-blue-50 file:text-blue-700">
                @if($lansia?->foto_ktp)
                    <img src="{{ asset('storage/' . $lansia->foto_ktp) }}" alt="KTP" class="mt-2 w-32 h-20 object-cover rounded border">
                @endif
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                <input wire:model="tanggal_lahir" type="date" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none @error('tanggal_lahir') border-red-500 @enderror">
                @error('tanggal_lahir')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 text-sm">
                        <input wire:model="jenis_kelamin" type="radio" value="L"> Pria
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input wire:model="jenis_kelamin" type="radio" value="P"> Wanita
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kondisi Kesehatan</label>
                <select wire:model="kondisi_kesehatan" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none">
                    <option value="">Pilih kondisi kesehatan</option>
                    <option value="baik">Sehat / Baik</option>
                    <option value="sedang">Sakit Ringan / Sedang</option>
                    <option value="buruk">Sakit / Buruk</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                <textarea wire:model="alamat" placeholder="Alamat lengkap" rows="2" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none"></textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <a href="{{ route('dashboard.lansia') }}" class="px-4 py-2 border border-gray-300 rounded text-sm text-gray-600 hover:bg-gray-50">Batal</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                    <span wire:loading.remove>Simpan</span>
                    <span wire:loading>Menyimpan...</span>
                </button>
            </div>
        </form>
    </div>
</div>
```

- [ ] **Step 5: Run tests**

```bash
php artisan test --compact --filter=LansiaWebTest
```

Expected: All PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Livewire/Admin/LansiaForm.php resources/views/livewire/admin/lansia-form.blade.php
git commit -m "feat: add lansia form page for create and edit"
```

---

## Task 7: Bantuan Management Page

**Files:**
- Create: `app/Livewire/Admin/BantuanManagement.php`
- Create: `resources/views/livewire/admin/bantuan-management.blade.php`
- Create: `tests/Feature/Web/BantuanWebTest.php`

- [ ] **Step 1: Write failing test**

Create `tests/Feature/Web/BantuanWebTest.php`:

```php
<?php

use App\Livewire\Admin\BantuanManagement;
use App\Models\Lansia;
use App\Models\Pendataan;
use App\Models\PemeriksaanKesehatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('admin can view bantuan management page', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->get('/dashboard/bantuan')->assertOk();
});

test('admin can set kuota', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(BantuanManagement::class)
        ->set('kuota', 25)
        ->set('periodeBulan', 5)
        ->set('periodeTahun', 2026)
        ->call('simpanKuota')
        ->assertHasNoErrors();

    expect(cache()->get('bantuan_kuota_5_2026')['kuota'])->toBe(25);
});

test('admin can trigger ranking', function () {
    $admin = User::factory()->admin()->create();
    $operator = User::factory()->operator()->create();
    cache()->put('bantuan_kuota_5_2026', ['kuota' => 5, 'periode_bulan' => 5, 'periode_tahun' => 2026]);

    $lansia = Lansia::factory()->create(['created_by' => $operator->id, 'tanggal_lahir' => '1945-01-01']);
    Pendataan::factory()->terverifikasi()->create(['lansia_id' => $lansia->lansia_id, 'user_id' => $operator->id]);
    PemeriksaanKesehatan::factory()->create(['lansia_id' => $lansia->lansia_id, 'hasil_periksa' => 'buruk']);

    Livewire::actingAs($admin)
        ->test(BantuanManagement::class)
        ->set('periodeBulan', 5)
        ->set('periodeTahun', 2026)
        ->call('jalankanRanking')
        ->assertSeeText('Ranking selesai');
});
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test --compact --filter=BantuanWebTest
```

Expected: FAIL.

- [ ] **Step 3: Create BantuanManagement component**

```bash
php artisan make:livewire Admin/BantuanManagement --no-interaction
```

Replace `app/Livewire/Admin/BantuanManagement.php`:

```php
<?php

namespace App\Livewire\Admin;

use App\Models\BantuanGizi;
use App\Services\RankingService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Manajemen Bantuan Gizi')]
class BantuanManagement extends Component
{
    #[Validate('required|integer|min:1')]
    public int $kuota = 0;

    #[Validate('required|integer|min:1|max:12')]
    public int $periodeBulan;

    #[Validate('required|integer|min:2020')]
    public int $periodeTahun;

    public ?string $rankingMessage = null;
    public bool $rankingDone = false;

    public function mount(): void
    {
        $this->periodeBulan = now()->month;
        $this->periodeTahun = now()->year;

        $existing = cache()->get("bantuan_kuota_{$this->periodeBulan}_{$this->periodeTahun}");
        if ($existing) {
            $this->kuota = $existing['kuota'];
        }
    }

    public function simpanKuota(): void
    {
        $this->validateOnly('kuota');
        $this->validateOnly('periodeBulan');
        $this->validateOnly('periodeTahun');

        cache()->put("bantuan_kuota_{$this->periodeBulan}_{$this->periodeTahun}", [
            'kuota' => $this->kuota,
            'periode_bulan' => $this->periodeBulan,
            'periode_tahun' => $this->periodeTahun,
        ]);

        $this->dispatch('notify', message: 'Kuota berhasil disimpan.');
    }

    public function jalankanRanking(RankingService $rankingService): void
    {
        $kuotaData = cache()->get("bantuan_kuota_{$this->periodeBulan}_{$this->periodeTahun}");
        $kuota = $kuotaData['kuota'] ?? 999;

        $result = $rankingService->rank($this->periodeBulan, $this->periodeTahun, $kuota);

        $this->rankingMessage = "Ranking selesai. {$result['total_penerima']} dari {$result['total_diproses']} lansia terpilih sebagai penerima bantuan.";
        $this->rankingDone = true;
    }

    public function render()
    {
        $hasilRanking = BantuanGizi::with('lansia')
            ->where('periode_bulan', $this->periodeBulan)
            ->where('periode_tahun', $this->periodeTahun)
            ->orderByDesc('skor_ranking')
            ->get();

        return view('livewire.admin.bantuan-management', compact('hasilRanking'));
    }
}
```

- [ ] **Step 4: Create bantuan-management view**

Create `resources/views/livewire/admin/bantuan-management.blade.php`:

```html
<div>
    <div class="grid grid-cols-2 gap-6 mb-6">
        {{-- Kuota Form --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="font-medium text-gray-800 mb-4">Pengaturan Kuota Bantuan</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Periode</label>
                    <div class="flex gap-2">
                        <select wire:model="periodeBulan" class="flex-1 border border-gray-300 rounded px-3 py-2 text-sm">
                            @foreach(range(1,12) as $m)
                                <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                            @endforeach
                        </select>
                        <input wire:model="periodeTahun" type="number" min="2020" class="w-24 border border-gray-300 rounded px-3 py-2 text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Jumlah Maksimal Penerima</label>
                    <input wire:model="kuota" type="number" min="1" placeholder="Masukkan kuota" class="w-full border border-gray-300 rounded px-3 py-2 text-sm @error('kuota') border-red-500 @enderror">
                    @error('kuota')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <button wire:click="simpanKuota" class="w-full bg-blue-600 text-white py-2 rounded text-sm hover:bg-blue-700">
                    Simpan Kuota
                </button>
            </div>
        </div>

        {{-- Ranking Trigger --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="font-medium text-gray-800 mb-4">Ranking Otomatis Penerima Bantuan</h3>
            <p class="text-sm text-gray-500 mb-4">Sistem akan menranking lansia berdasarkan usia (60%) dan kondisi kesehatan (40%). Lansia dengan skor tertinggi diprioritaskan.</p>

            @if($rankingMessage)
            <div class="bg-green-50 border border-green-200 rounded p-3 mb-4">
                <p class="text-green-700 text-sm">{{ $rankingMessage }}</p>
            </div>
            @endif

            <button wire:click="jalankanRanking" wire:loading.attr="disabled" class="w-full bg-green-600 text-white py-2 rounded text-sm hover:bg-green-700 disabled:opacity-50">
                <span wire:loading.remove>Jalankan Ranking Otomatis</span>
                <span wire:loading>Memproses ranking...</span>
            </button>
        </div>
    </div>

    {{-- Hasil Ranking --}}
    @if($hasilRanking->count() > 0)
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h3 class="font-medium text-gray-800">Hasil Ranking Penerima Bantuan — {{ \Carbon\Carbon::create()->month($periodeBulan)->translatedFormat('F') }} {{ $periodeTahun }}</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-3 text-gray-600">No.</th>
                    <th class="text-left px-4 py-3 text-gray-600">Nama</th>
                    <th class="text-left px-4 py-3 text-gray-600">Usia</th>
                    <th class="text-left px-4 py-3 text-gray-600">RW</th>
                    <th class="text-left px-4 py-3 text-gray-600">Skor</th>
                    <th class="text-left px-4 py-3 text-gray-600">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($hasilRanking as $bantuan)
                <tr class="border-b last:border-0">
                    <td class="px-4 py-3 text-gray-500">{{ $loop->iteration }}</td>
                    <td class="px-4 py-3 font-medium">{{ $bantuan->lansia?->nama }}</td>
                    <td class="px-4 py-3">{{ $bantuan->lansia?->usia }}</td>
                    <td class="px-4 py-3">{{ $bantuan->lansia?->rw }}</td>
                    <td class="px-4 py-3 text-blue-600 font-mono">{{ number_format($bantuan->skor_ranking, 4) }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded-full text-xs {{ $bantuan->status_penerima === 'penerima' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $bantuan->status_penerima === 'penerima' ? 'Penerima' : 'Tidak Penerima' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
```

- [ ] **Step 5: Run tests**

```bash
php artisan test --compact --filter=BantuanWebTest
```

Expected: 3/3 PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Livewire/Admin/BantuanManagement.php resources/views/livewire/admin/bantuan-management.blade.php tests/Feature/Web/BantuanWebTest.php
git commit -m "feat: add bantuan management page with kuota and auto ranking"
```

---

## Task 8: Laporan Page

**Files:**
- Create: `app/Livewire/Admin/LaporanTable.php`
- Create: `resources/views/livewire/admin/laporan-table.blade.php`
- Create: `resources/views/livewire/admin/laporan-print.blade.php`
- Create: `tests/Feature/Web/LaporanWebTest.php`

- [ ] **Step 1: Write failing test**

Create `tests/Feature/Web/LaporanWebTest.php`:

```php
<?php

use App\Livewire\Admin\LaporanTable;
use App\Models\BantuanGizi;
use App\Models\Lansia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('admin can view laporan page', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->get('/dashboard/laporan')->assertOk();
});

test('laporan shows bantuan data', function () {
    $admin = User::factory()->admin()->create();
    $operator = User::factory()->operator()->create();
    $lansia = Lansia::factory()->create(['nama' => 'Budi Laporan', 'created_by' => $operator->id]);
    BantuanGizi::factory()->create(['lansia_id' => $lansia->lansia_id, 'status_penerima' => 'penerima']);

    Livewire::actingAs($admin)
        ->test(LaporanTable::class)
        ->assertSeeText('Budi Laporan');
});

test('laporan can filter by jenis penerima', function () {
    $admin = User::factory()->admin()->create();
    $operator = User::factory()->operator()->create();
    $lansiaPenerima = Lansia::factory()->create(['nama' => 'Si Penerima', 'created_by' => $operator->id]);
    $lansiaNoN = Lansia::factory()->create(['nama' => 'Si Bukan', 'created_by' => $operator->id]);
    BantuanGizi::factory()->create(['lansia_id' => $lansiaPenerima->lansia_id, 'status_penerima' => 'penerima']);
    BantuanGizi::factory()->create(['lansia_id' => $lansiaNoN->lansia_id, 'status_penerima' => 'tidak_penerima']);

    Livewire::actingAs($admin)
        ->test(LaporanTable::class)
        ->set('filterJenis', 'penerima')
        ->assertSeeText('Si Penerima')
        ->assertDontSeeText('Si Bukan');
});

test('admin can access print view', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->get('/dashboard/laporan/print')->assertOk();
});
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test --compact --filter=LaporanWebTest
```

Expected: FAIL.

- [ ] **Step 3: Create LaporanTable component**

```bash
php artisan make:livewire Admin/LaporanTable --no-interaction
```

Replace `app/Livewire/Admin/LaporanTable.php`:

```php
<?php

namespace App\Livewire\Admin;

use App\Models\BantuanGizi;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.admin')]
#[Title('Laporan Bantuan Gizi')]
class LaporanTable extends Component
{
    use WithPagination;

    public string $filterRw = '';
    public string $filterJenis = '';
    public string $filterKondisi = '';
    public int $filterLimit = 15;

    public function download(): StreamedResponse
    {
        $query = $this->buildQuery();
        $data = $query->get();

        $filename = 'laporan-bantuan-gizi-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['No', 'NIK', 'Nama', 'Usia', 'RW', 'Periode', 'Status', 'Skor']);
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
                    $item->skor_ranking,
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function buildQuery()
    {
        $query = BantuanGizi::with('lansia');

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
        $query = $this->buildQuery();
        $rwOptions = \App\Models\Lansia::distinct()->orderBy('rw')->pluck('rw');

        return view('livewire.admin.laporan-table', [
            'laporanList' => $query->paginate($this->filterLimit),
            'rwOptions' => $rwOptions,
        ]);
    }
}
```

- [ ] **Step 4: Create laporan-table view**

Create `resources/views/livewire/admin/laporan-table.blade.php`:

```html
<div>
    <div class="bg-white rounded-lg shadow-sm p-6 mb-4">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Cetak Laporan Pendataan Lansia</h2>

        {{-- Filter Row --}}
        <div class="flex items-center gap-3 mb-4">
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

            <div class="ml-auto flex gap-2">
                <button wire:click="download" class="flex items-center gap-1 px-3 py-2 border border-gray-300 rounded text-sm text-gray-600 hover:bg-gray-50">
                    ↓ Unduh Laporan
                </button>
                <a href="{{ route('dashboard.laporan.print', array_filter(['rw' => $filterRw, 'jenis' => $filterJenis])) }}" target="_blank" class="flex items-center gap-1 px-3 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                    🖨 Cetak Laporan
                </a>
            </div>
        </div>

        {{-- Table --}}
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
                    <th class="text-left px-4 py-3 text-gray-600">Skor</th>
                </tr>
            </thead>
            <tbody>
                @forelse($laporanList as $item)
                <tr class="border-b last:border-0">
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
                    <td class="px-4 py-3 font-mono text-xs text-blue-600">{{ $item->skor_ranking ? number_format($item->skor_ranking, 4) : '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-400 text-sm">Tidak ada data laporan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4 flex items-center justify-between">
            <p class="text-sm text-gray-500">Menampilkan {{ $laporanList->firstItem() ?? 0 }} sampai {{ $laporanList->lastItem() ?? 0 }} dari {{ $laporanList->total() }} data</p>
            {{ $laporanList->links() }}
        </div>
    </div>
</div>
```

- [ ] **Step 5: Create laporan-print view**

Create `resources/views/livewire/admin/laporan-print.blade.php`:

```html
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Bantuan Gizi</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        p.subtitle { color: #666; margin-bottom: 16px; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f5f5f5; font-weight: 600; }
        tfoot td { font-weight: 600; background: #f5f5f5; }
        .print-btn { margin-bottom: 12px; }
        @media print { .print-btn { display: none; } }
    </style>
</head>
<body>
    <div class="print-btn">
        <button onclick="window.print()">🖨 Cetak</button>
        <button onclick="window.close()">✕ Tutup</button>
    </div>

    <h1>Laporan Penyaluran Bantuan Gizi Lansia</h1>
    <p class="subtitle">Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>NIK</th>
                <th>Nama</th>
                <th>Usia</th>
                <th>RW</th>
                <th>Periode</th>
                <th>Status</th>
                <th>Skor Ranking</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->lansia?->nik }}</td>
                <td>{{ $item->lansia?->nama }}</td>
                <td>{{ $item->lansia?->usia }}</td>
                <td>{{ $item->lansia?->rw }}</td>
                <td>{{ $item->periode_bulan }}/{{ $item->periode_tahun }}</td>
                <td>{{ $item->status_penerima }}</td>
                <td>{{ $item->skor_ranking ? number_format($item->skor_ranking, 4) : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6">Total</td>
                <td colspan="2">{{ $data->count() }} data</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
```

- [ ] **Step 6: Run tests**

```bash
php artisan test --compact --filter=LaporanWebTest
```

Expected: 4/4 PASS.

- [ ] **Step 7: Commit**

```bash
git add app/Livewire/Admin/LaporanTable.php resources/views/livewire/admin/laporan-table.blade.php resources/views/livewire/admin/laporan-print.blade.php tests/Feature/Web/LaporanWebTest.php
git commit -m "feat: add laporan page with filter, download csv, and print view"
```

---

## Task 9: Monitoring Page

**Files:**
- Create: `app/Livewire/Admin/MonitoringTable.php`
- Create: `resources/views/livewire/admin/monitoring-table.blade.php`
- Create: `tests/Feature/Web/MonitoringWebTest.php`

- [ ] **Step 1: Write failing test**

Create `tests/Feature/Web/MonitoringWebTest.php`:

```php
<?php

use App\Livewire\Admin\MonitoringTable;
use App\Models\BantuanGizi;
use App\Models\Lansia;
use App\Models\Pendataan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('admin can view monitoring page', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->get('/dashboard/monitoring')->assertOk();
});

test('monitoring shows operator input data', function () {
    $admin = User::factory()->admin()->create();
    $operator = User::factory()->operator()->create(['name' => 'Kader RW 01']);
    $lansia = Lansia::factory()->create(['created_by' => $operator->id]);
    Pendataan::factory()->create(['lansia_id' => $lansia->lansia_id, 'user_id' => $operator->id]);

    Livewire::actingAs($admin)
        ->test(MonitoringTable::class)
        ->assertSeeText('Kader RW 01');
});

test('monitoring shows distribusi bantuan per rw', function () {
    $admin = User::factory()->admin()->create();
    $operator = User::factory()->operator()->create();
    $lansia = Lansia::factory()->create(['rw' => '01', 'created_by' => $operator->id]);
    BantuanGizi::factory()->create(['lansia_id' => $lansia->lansia_id, 'status_penerima' => 'penerima']);

    Livewire::actingAs($admin)
        ->test(MonitoringTable::class)
        ->assertSeeText('01');
});
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test --compact --filter=MonitoringWebTest
```

Expected: FAIL.

- [ ] **Step 3: Create MonitoringTable component**

```bash
php artisan make:livewire Admin/MonitoringTable --no-interaction
```

Replace `app/Livewire/Admin/MonitoringTable.php`:

```php
<?php

namespace App\Livewire\Admin;

use App\Models\BantuanGizi;
use App\Models\Lansia;
use App\Models\Pendataan;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Monitoring Operasional')]
class MonitoringTable extends Component
{
    public function render()
    {
        $totalInputHariIni = Pendataan::whereDate('created_at', today())->count();
        $totalPending = Pendataan::where('status_verifikasi', 'menunggu')->count();
        $totalPenerima = BantuanGizi::where('status_penerima', 'penerima')->count();

        $logInput = Pendataan::with(['user', 'lansia'])
            ->latest()
            ->limit(20)
            ->get();

        $distribusiPerRw = Lansia::select('lansia.rw',
            DB::raw('count(distinct lansia.lansia_id) as total_lansia'),
            DB::raw('sum(case when bg.status_penerima = "penerima" then 1 else 0 end) as total_penerima'),
            DB::raw('sum(case when bg.status_penerima = "tidak_penerima" then 1 else 0 end) as tidak_penerima')
        )
            ->leftJoin('bantuan_gizi as bg', 'lansia.lansia_id', '=', 'bg.lansia_id')
            ->groupBy('lansia.rw')
            ->orderBy('lansia.rw')
            ->get();

        return view('livewire.admin.monitoring-table', compact(
            'totalInputHariIni',
            'totalPending',
            'totalPenerima',
            'logInput',
            'distribusiPerRw'
        ));
    }
}
```

- [ ] **Step 4: Create monitoring-table view**

Create `resources/views/livewire/admin/monitoring-table.blade.php`:

```html
<div>
    {{-- Stat Cards --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-4">
            <p class="text-sm text-gray-500">Input Hari Ini</p>
            <p class="text-3xl font-bold text-blue-600">{{ $totalInputHariIni }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4">
            <p class="text-sm text-gray-500">Verifikasi Pending</p>
            <p class="text-3xl font-bold text-yellow-500">{{ $totalPending }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-4">
            <p class="text-sm text-gray-500">Total Penerima Bantuan</p>
            <p class="text-3xl font-bold text-green-600">{{ $totalPenerima }}</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-6">
        {{-- Log Input Operator --}}
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b">
                <h3 class="font-medium text-gray-800 text-sm">Log Input Operator (20 Terbaru)</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-4 py-2 text-gray-600 text-xs">Operator</th>
                        <th class="text-left px-4 py-2 text-gray-600 text-xs">Lansia</th>
                        <th class="text-left px-4 py-2 text-gray-600 text-xs">Status</th>
                        <th class="text-left px-4 py-2 text-gray-600 text-xs">Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logInput as $log)
                    <tr class="border-b last:border-0">
                        <td class="px-4 py-2">{{ $log->user?->name }}</td>
                        <td class="px-4 py-2">{{ $log->lansia?->nama }}</td>
                        <td class="px-4 py-2">
                            <span class="px-1.5 py-0.5 rounded text-xs {{ $log->status_verifikasi === 'terverifikasi' ? 'bg-green-100 text-green-700' : ($log->status_verifikasi === 'menunggu' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                {{ $log->status_verifikasi }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-gray-400 text-xs">{{ $log->created_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400 text-xs">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Distribusi Bantuan per RW --}}
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b">
                <h3 class="font-medium text-gray-800 text-sm">Distribusi Bantuan per RW</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-4 py-2 text-gray-600 text-xs">RW</th>
                        <th class="text-left px-4 py-2 text-gray-600 text-xs">Total Lansia</th>
                        <th class="text-left px-4 py-2 text-gray-600 text-xs">Penerima</th>
                        <th class="text-left px-4 py-2 text-gray-600 text-xs">Tidak Penerima</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($distribusiPerRw as $row)
                    <tr class="border-b last:border-0">
                        <td class="px-4 py-2 font-medium">RW {{ $row->rw }}</td>
                        <td class="px-4 py-2">{{ $row->total_lansia }}</td>
                        <td class="px-4 py-2 text-green-600 font-medium">{{ $row->total_penerima }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ $row->tidak_penerima }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400 text-xs">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
```

- [ ] **Step 5: Run tests**

```bash
php artisan test --compact --filter=MonitoringWebTest
```

Expected: 3/3 PASS.

- [ ] **Step 6: Run full suite**

```bash
php artisan test --compact
```

Expected: All tests PASS (no regressions).

- [ ] **Step 7: Run Pint**

```bash
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 8: Build assets**

```bash
npm run build
```

- [ ] **Step 9: Commit**

```bash
git add app/Livewire/Admin/MonitoringTable.php resources/views/livewire/admin/monitoring-table.blade.php tests/Feature/Web/MonitoringWebTest.php
git commit -m "feat: add monitoring operasional page with log input and distribusi bantuan"
```

---

## Self-Review

### Spec Coverage

| Requirement | Task |
|---|---|
| Login/Logout | Task 2 |
| Lihat/edit/hapus data lansia | Task 5, 6 |
| Verifikasi data lansia | Task 5 |
| Pencarian & filter data | Task 5 |
| Set kuota bantuan | Task 7 |
| Auto-ranking | Task 7 |
| Dashboard stats + chart | Task 4 |
| Statistik per RW, usia, kondisi | Task 4 |
| Generate/download/cetak laporan | Task 8 |
| Filter laporan | Task 8 |
| Monitoring input operator | Task 9 |
| Monitoring distribusi bantuan | Task 9 |

✅ All spec requirements covered.

### Type Consistency

- `LansiaTable::deleteLansia(int $lansiaId)` ✅ matches `wire:click="deleteLansia({{ $lansia->lansia_id }})"` 
- `LansiaTable::verifikasiLansia(int $pendataanId, string $status)` ✅ matches wire:click call
- `BantuanManagement::simpanKuota()` ✅ uses `$this->kuota`, `$this->periodeBulan`, `$this->periodeTahun`
- `BantuanManagement::jalankanRanking(RankingService)` ✅ injected via method injection (Livewire v3 supports this)
