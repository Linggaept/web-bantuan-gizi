# API Bantuan Gizi Lansia Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a versioned REST API (`/api/v1/`) for the elderly nutrition aid system with role-based access (operator, admin, lurah), Sanctum token auth, and auto-ranking algorithm.

**Architecture:** Sanctum token auth → RoleMiddleware → API Resources response transform. Controllers thin, logic in RankingService. All endpoints tested with Pest feature tests.

**Tech Stack:** Laravel 13, PHP 8.3, Laravel Sanctum, Pest v4, Laravel API Resources

---

## File Map

**New files:**
```
app/Http/Controllers/Api/V1/AuthController.php
app/Http/Controllers/Api/V1/LansiaController.php
app/Http/Controllers/Api/V1/PemeriksaanController.php
app/Http/Controllers/Api/V1/PendataanController.php
app/Http/Controllers/Api/V1/BantuanController.php
app/Http/Controllers/Api/V1/DashboardController.php
app/Http/Controllers/Api/V1/LaporanController.php
app/Http/Middleware/RoleMiddleware.php
app/Http/Resources/LansiaResource.php
app/Http/Resources/PemeriksaanResource.php
app/Http/Resources/PendataanResource.php
app/Http/Resources/BantuanResource.php
app/Http/Resources/DashboardStatsResource.php
app/Models/Lansia.php
app/Models/PemeriksaanKesehatan.php
app/Models/Pendataan.php
app/Models/BantuanGizi.php
app/Services/RankingService.php
database/factories/LansiaFactory.php
database/factories/PemeriksaanKesehatanFactory.php
database/factories/PendataanFactory.php
database/factories/BantuanGiziFactory.php
database/migrations/2026_05_14_000001_add_fields_to_users_table.php
database/migrations/2026_05_14_000002_create_lansia_table.php
database/migrations/2026_05_14_000003_create_pemeriksaan_kesehatan_table.php
database/migrations/2026_05_14_000004_create_pendataan_table.php
database/migrations/2026_05_14_000005_create_bantuan_gizi_table.php
routes/api.php
tests/Feature/Api/AuthTest.php
tests/Feature/Api/LansiaTest.php
tests/Feature/Api/PemeriksaanTest.php
tests/Feature/Api/PendataanTest.php
tests/Feature/Api/BantuanTest.php
tests/Feature/Api/DashboardTest.php
tests/Feature/Api/LaporanTest.php
```

**Modified files:**
```
app/Models/User.php
bootstrap/app.php
composer.json (via composer require)
```

---

## Task 1: Install Sanctum & Run Migrations

**Files:**
- Modify: `composer.json` (via composer)
- Modify: `app/Models/User.php`
- Create: `database/migrations/2026_05_14_000001_add_fields_to_users_table.php`
- Create: `database/migrations/2026_05_14_000002_create_lansia_table.php`
- Create: `database/migrations/2026_05_14_000003_create_pemeriksaan_kesehatan_table.php`
- Create: `database/migrations/2026_05_14_000004_create_pendataan_table.php`
- Create: `database/migrations/2026_05_14_000005_create_bantuan_gizi_table.php`

- [ ] **Step 1: Install Sanctum**

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --no-interaction
```

Expected: `config/sanctum.php` created.

- [ ] **Step 2: Create migration — add fields to users table**

```bash
php artisan make:migration add_fields_to_users_table --no-interaction
```

Edit the generated migration file:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->after('name');
            $table->enum('role', ['operator', 'admin', 'lurah'])->default('operator')->after('username');
            $table->boolean('is_active')->default(true)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'role', 'is_active']);
        });
    }
};
```

- [ ] **Step 3: Create migration — lansia table**

```bash
php artisan make:migration create_lansia_table --no-interaction
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lansia', function (Blueprint $table) {
            $table->id('lansia_id');
            $table->string('nik', 16)->unique();
            $table->string('nama');
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->text('alamat');
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5);
            $table->string('foto_ktp')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lansia');
    }
};
```

- [ ] **Step 4: Create migration — pemeriksaan_kesehatan table**

```bash
php artisan make:migration create_pemeriksaan_kesehatan_table --no-interaction
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemeriksaan_kesehatan', function (Blueprint $table) {
            $table->id('pemeriksaan_id');
            $table->foreignId('lansia_id')->constrained('lansia', 'lansia_id')->cascadeOnDelete();
            $table->date('tanggal_periksa');
            $table->decimal('berat_badan', 5, 2)->nullable();
            $table->string('tekanan_darah', 20)->nullable();
            $table->enum('hasil_periksa', ['baik', 'sedang', 'buruk']);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemeriksaan_kesehatan');
    }
};
```

- [ ] **Step 5: Create migration — pendataan table**

```bash
php artisan make:migration create_pendataan_table --no-interaction
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendataan', function (Blueprint $table) {
            $table->id('pendataan_id');
            $table->foreignId('lansia_id')->constrained('lansia', 'lansia_id')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->enum('status_verifikasi', ['menunggu', 'terverifikasi', 'ditolak'])->default('menunggu');
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->date('tanggal_input');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendataan');
    }
};
```

- [ ] **Step 6: Create migration — bantuan_gizi table**

```bash
php artisan make:migration create_bantuan_gizi_table --no-interaction
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bantuan_gizi', function (Blueprint $table) {
            $table->id('bantuan_id');
            $table->foreignId('lansia_id')->constrained('lansia', 'lansia_id')->cascadeOnDelete();
            $table->unsignedTinyInteger('periode_bulan');
            $table->unsignedSmallInteger('periode_tahun');
            $table->decimal('skor_ranking', 8, 4)->nullable();
            $table->enum('status_penerima', ['penerima', 'tidak_penerima', 'pending'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['lansia_id', 'periode_bulan', 'periode_tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bantuan_gizi');
    }
};
```

- [ ] **Step 7: Run migrations**

```bash
php artisan migrate --no-interaction
```

Expected: 5 new tables created without errors.

- [ ] **Step 8: Commit**

```bash
git add database/migrations/ composer.json composer.lock config/sanctum.php
git commit -m "feat: install sanctum and create database migrations"
```

---

## Task 2: Models & Factories

**Files:**
- Modify: `app/Models/User.php`
- Create: `app/Models/Lansia.php`
- Create: `app/Models/PemeriksaanKesehatan.php`
- Create: `app/Models/Pendataan.php`
- Create: `app/Models/BantuanGizi.php`
- Create: `database/factories/LansiaFactory.php`
- Create: `database/factories/PemeriksaanKesehatanFactory.php`
- Create: `database/factories/PendataanFactory.php`
- Create: `database/factories/BantuanGiziFactory.php`

- [ ] **Step 1: Update User model**

Replace `app/Models/User.php`:

```php
<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'username', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
}
```

- [ ] **Step 2: Update UserFactory to include new fields**

Edit `database/factories/UserFactory.php`, add `username`, `role`, `is_active` to definition:

```php
public function definition(): array
{
    return [
        'name' => fake()->name(),
        'username' => fake()->unique()->userName(),
        'email' => fake()->unique()->safeEmail(),
        'email_verified_at' => now(),
        'password' => static::$password ??= Hash::make('password'),
        'remember_token' => Str::random(10),
        'role' => 'operator',
        'is_active' => true,
    ];
}
```

Also add state methods after definition():

```php
public function admin(): static
{
    return $this->state(['role' => 'admin']);
}

public function lurah(): static
{
    return $this->state(['role' => 'lurah']);
}

public function operator(): static
{
    return $this->state(['role' => 'operator']);
}
```

- [ ] **Step 3: Create Lansia model**

```bash
php artisan make:model Lansia --factory --no-interaction
```

Replace generated `app/Models/Lansia.php`:

```php
<?php

namespace App\Models;

use Database\Factories\LansiaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['nik', 'nama', 'tanggal_lahir', 'jenis_kelamin', 'alamat', 'rt', 'rw', 'foto_ktp', 'created_by'])]
class Lansia extends Model
{
    /** @use HasFactory<LansiaFactory> */
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'lansia_id';

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }

    public function pemeriksaan()
    {
        return $this->hasMany(PemeriksaanKesehatan::class, 'lansia_id', 'lansia_id');
    }

    public function pendataan()
    {
        return $this->hasMany(Pendataan::class, 'lansia_id', 'lansia_id');
    }

    public function bantuanGizi()
    {
        return $this->hasMany(BantuanGizi::class, 'lansia_id', 'lansia_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getUsiaAttribute(): int
    {
        return $this->tanggal_lahir->age;
    }
}
```

- [ ] **Step 4: Fill LansiaFactory**

Replace `database/factories/LansiaFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lansia>
 */
class LansiaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nik' => fake()->unique()->numerify('################'),
            'nama' => fake()->name(),
            'tanggal_lahir' => fake()->dateTimeBetween('-90 years', '-60 years')->format('Y-m-d'),
            'jenis_kelamin' => fake()->randomElement(['L', 'P']),
            'alamat' => fake()->address(),
            'rt' => fake()->numerify('0#'),
            'rw' => fake()->numerify('0#'),
            'foto_ktp' => null,
            'created_by' => User::factory(),
        ];
    }
}
```

- [ ] **Step 5: Create PemeriksaanKesehatan model**

```bash
php artisan make:model PemeriksaanKesehatan --factory --no-interaction
```

Replace `app/Models/PemeriksaanKesehatan.php`:

```php
<?php

namespace App\Models;

use Database\Factories\PemeriksaanKesehatanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['lansia_id', 'tanggal_periksa', 'berat_badan', 'tekanan_darah', 'hasil_periksa', 'catatan'])]
class PemeriksaanKesehatan extends Model
{
    /** @use HasFactory<PemeriksaanKesehatanFactory> */
    use HasFactory;

    protected $primaryKey = 'pemeriksaan_id';

    protected function casts(): array
    {
        return [
            'tanggal_periksa' => 'date',
            'berat_badan' => 'float',
        ];
    }

    public function lansia()
    {
        return $this->belongsTo(Lansia::class, 'lansia_id', 'lansia_id');
    }
}
```

Replace `database/factories/PemeriksaanKesehatanFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\Lansia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PemeriksaanKesehatan>
 */
class PemeriksaanKesehatanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lansia_id' => Lansia::factory(),
            'tanggal_periksa' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'berat_badan' => fake()->randomFloat(2, 40, 80),
            'tekanan_darah' => fake()->numerify('###/##'),
            'hasil_periksa' => fake()->randomElement(['baik', 'sedang', 'buruk']),
            'catatan' => fake()->optional()->sentence(),
        ];
    }
}
```

- [ ] **Step 6: Create Pendataan model**

```bash
php artisan make:model Pendataan --factory --no-interaction
```

Replace `app/Models/Pendataan.php`:

```php
<?php

namespace App\Models;

use Database\Factories\PendataanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['lansia_id', 'user_id', 'status_verifikasi', 'verified_by', 'verified_at', 'tanggal_input'])]
class Pendataan extends Model
{
    /** @use HasFactory<PendataanFactory> */
    use HasFactory;

    protected $primaryKey = 'pendataan_id';

    protected function casts(): array
    {
        return [
            'tanggal_input' => 'date',
            'verified_at' => 'datetime',
        ];
    }

    public function lansia()
    {
        return $this->belongsTo(Lansia::class, 'lansia_id', 'lansia_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
```

Replace `database/factories/PendataanFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\Lansia;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pendataan>
 */
class PendataanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lansia_id' => Lansia::factory(),
            'user_id' => User::factory()->operator(),
            'status_verifikasi' => 'menunggu',
            'verified_by' => null,
            'verified_at' => null,
            'tanggal_input' => now()->toDateString(),
        ];
    }

    public function terverifikasi(): static
    {
        return $this->state(function () {
            $admin = User::factory()->admin()->create();

            return [
                'status_verifikasi' => 'terverifikasi',
                'verified_by' => $admin->id,
                'verified_at' => now(),
            ];
        });
    }
}
```

- [ ] **Step 7: Create BantuanGizi model**

```bash
php artisan make:model BantuanGizi --factory --no-interaction
```

Replace `app/Models/BantuanGizi.php`:

```php
<?php

namespace App\Models;

use Database\Factories\BantuanGiziFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['lansia_id', 'periode_bulan', 'periode_tahun', 'skor_ranking', 'status_penerima', 'approved_by', 'approved_at'])]
class BantuanGizi extends Model
{
    /** @use HasFactory<BantuanGiziFactory> */
    use HasFactory;

    protected $primaryKey = 'bantuan_id';

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'skor_ranking' => 'float',
        ];
    }

    public function lansia()
    {
        return $this->belongsTo(Lansia::class, 'lansia_id', 'lansia_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
```

Replace `database/factories/BantuanGiziFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\Lansia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BantuanGizi>
 */
class BantuanGiziFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lansia_id' => Lansia::factory(),
            'periode_bulan' => now()->month,
            'periode_tahun' => now()->year,
            'skor_ranking' => null,
            'status_penerima' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
        ];
    }
}
```

- [ ] **Step 8: Commit**

```bash
git add app/Models/ database/factories/
git commit -m "feat: add models and factories for lansia, pemeriksaan, pendataan, bantuan gizi"
```

---

## Task 3: Middleware, Routes & API Resources

**Files:**
- Create: `app/Http/Middleware/RoleMiddleware.php`
- Create: `routes/api.php`
- Modify: `bootstrap/app.php`
- Create: `app/Http/Resources/LansiaResource.php`
- Create: `app/Http/Resources/PemeriksaanResource.php`
- Create: `app/Http/Resources/PendataanResource.php`
- Create: `app/Http/Resources/BantuanResource.php`
- Create: `app/Http/Resources/DashboardStatsResource.php`

- [ ] **Step 1: Create RoleMiddleware**

```bash
php artisan make:middleware RoleMiddleware --no-interaction
```

Replace `app/Http/Middleware/RoleMiddleware.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user() || ! in_array($request->user()->role, $roles)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return $next($request);
    }
}
```

- [ ] **Step 2: Register middleware alias in bootstrap/app.php**

In `bootstrap/app.php`, inside the `->withMiddleware(function (Middleware $middleware) {` block, add:

```php
$middleware->alias([
    'role' => \App\Http\Middleware\RoleMiddleware::class,
]);
```

- [ ] **Step 3: Create routes/api.php**

Create `routes/api.php`:

```php
<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BantuanController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\LansiaController;
use App\Http\Controllers\Api\V1\LaporanController;
use App\Http\Controllers\Api\V1\PemeriksaanController;
use App\Http\Controllers\Api\V1\PendataanController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public
    Route::post('auth/login', [AuthController::class, 'login']);

    // Authenticated
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);

        // Operator + Admin: lansia CRUD
        Route::middleware('role:operator,admin')->group(function () {
            Route::apiResource('lansia', LansiaController::class, [
                'parameters' => ['lansia' => 'lansia'],
            ]);
            Route::post('lansia/{lansia}/foto-ktp', [LansiaController::class, 'uploadFotoKtp']);
            Route::apiResource('lansia.pemeriksaan', PemeriksaanController::class)->shallow();
        });

        // Lurah: read-only lansia
        Route::middleware('role:lurah')->group(function () {
            Route::get('lansia', [LansiaController::class, 'index']);
            Route::get('lansia/{lansia}', [LansiaController::class, 'show']);
            Route::get('lansia/{lansia}/status-bantuan', [LansiaController::class, 'statusBantuan']);
        });

        // Admin only
        Route::middleware('role:admin')->group(function () {
            Route::get('pendataan', [PendataanController::class, 'index']);
            Route::post('pendataan/{pendataan}/verifikasi', [PendataanController::class, 'verifikasi']);
            Route::post('bantuan/ranking', [BantuanController::class, 'ranking']);
            Route::post('bantuan/kuota', [BantuanController::class, 'setKuota']);
        });

        // Admin + Lurah
        Route::middleware('role:admin,lurah')->group(function () {
            Route::get('bantuan', [BantuanController::class, 'index']);
            Route::get('bantuan/kuota', [BantuanController::class, 'getKuota']);
            Route::get('dashboard/stats', [DashboardController::class, 'stats']);
            Route::get('laporan', [LaporanController::class, 'index']);
            Route::get('laporan/download', [LaporanController::class, 'download']);
        });

        // Lurah only
        Route::middleware('role:lurah')->group(function () {
            Route::post('bantuan/{bantuan}/approve', [BantuanController::class, 'approve']);
        });

        // Operator: status bantuan
        Route::middleware('role:operator,admin')->group(function () {
            Route::get('lansia/{lansia}/status-bantuan', [LansiaController::class, 'statusBantuan']);
        });
    });
});
```

- [ ] **Step 4: Register api.php in bootstrap/app.php**

In `bootstrap/app.php`, inside `->withRouting(function () {` block, add:

```php
Route::middleware('api')
    ->prefix('api')
    ->group(base_path('routes/api.php'));
```

Full `bootstrap/app.php` after edit:

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(function () {
        Route::middleware('web')
            ->group(base_path('routes/web.php'));

        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));
    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

- [ ] **Step 5: Create LansiaResource**

```bash
php artisan make:resource LansiaResource --no-interaction
```

Replace `app/Http/Resources/LansiaResource.php`:

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LansiaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'lansia_id' => $this->lansia_id,
            'nik' => $this->nik,
            'nama' => $this->nama,
            'tanggal_lahir' => $this->tanggal_lahir?->toDateString(),
            'usia' => $this->usia,
            'jenis_kelamin' => $this->jenis_kelamin,
            'alamat' => $this->alamat,
            'rt' => $this->rt,
            'rw' => $this->rw,
            'foto_ktp' => $this->foto_ktp ? asset('storage/' . $this->foto_ktp) : null,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
```

- [ ] **Step 6: Create PemeriksaanResource**

```bash
php artisan make:resource PemeriksaanResource --no-interaction
```

Replace `app/Http/Resources/PemeriksaanResource.php`:

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PemeriksaanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'pemeriksaan_id' => $this->pemeriksaan_id,
            'lansia_id' => $this->lansia_id,
            'tanggal_periksa' => $this->tanggal_periksa?->toDateString(),
            'berat_badan' => $this->berat_badan,
            'tekanan_darah' => $this->tekanan_darah,
            'hasil_periksa' => $this->hasil_periksa,
            'catatan' => $this->catatan,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
```

- [ ] **Step 7: Create PendataanResource**

```bash
php artisan make:resource PendataanResource --no-interaction
```

Replace `app/Http/Resources/PendataanResource.php`:

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PendataanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'pendataan_id' => $this->pendataan_id,
            'lansia_id' => $this->lansia_id,
            'user_id' => $this->user_id,
            'status_verifikasi' => $this->status_verifikasi,
            'verified_by' => $this->verified_by,
            'verified_at' => $this->verified_at?->toIso8601String(),
            'tanggal_input' => $this->tanggal_input?->toDateString(),
            'lansia' => new LansiaResource($this->whenLoaded('lansia')),
        ];
    }
}
```

- [ ] **Step 8: Create BantuanResource**

```bash
php artisan make:resource BantuanResource --no-interaction
```

Replace `app/Http/Resources/BantuanResource.php`:

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BantuanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'bantuan_id' => $this->bantuan_id,
            'lansia_id' => $this->lansia_id,
            'periode_bulan' => $this->periode_bulan,
            'periode_tahun' => $this->periode_tahun,
            'skor_ranking' => $this->skor_ranking,
            'status_penerima' => $this->status_penerima,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at?->toIso8601String(),
            'lansia' => new LansiaResource($this->whenLoaded('lansia')),
        ];
    }
}
```

- [ ] **Step 9: Commit**

```bash
git add app/Http/Middleware/ app/Http/Resources/ routes/api.php bootstrap/app.php
git commit -m "feat: add role middleware, api routes, and api resources"
```

---

## Task 4: Auth Endpoints

**Files:**
- Create: `app/Http/Controllers/Api/V1/AuthController.php`
- Create: `tests/Feature/Api/AuthTest.php`

- [ ] **Step 1: Write failing test**

```bash
php artisan make:test --pest ApiAuthTest --no-interaction
```

Rename generated file to `tests/Feature/Api/AuthTest.php` (create `Api` directory if needed):

```php
<?php

use App\Models\User;

test('user can login with valid credentials', function () {
    $user = User::factory()->create([
        'username' => 'testoperator',
        'password' => bcrypt('password123'),
        'is_active' => true,
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'username' => 'testoperator',
        'password' => 'password123',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'data' => ['token', 'user' => ['id', 'nama', 'role']],
        ]);
});

test('login fails with wrong password', function () {
    User::factory()->create(['username' => 'testop2', 'password' => bcrypt('secret')]);

    $this->postJson('/api/v1/auth/login', [
        'username' => 'testop2',
        'password' => 'wrong',
    ])->assertUnprocessable();
});

test('login fails for inactive user', function () {
    User::factory()->create([
        'username' => 'inactive',
        'password' => bcrypt('password'),
        'is_active' => false,
    ]);

    $this->postJson('/api/v1/auth/login', [
        'username' => 'inactive',
        'password' => 'password',
    ])->assertUnprocessable();
});

test('authenticated user can logout', function () {
    $user = User::factory()->create(['username' => 'testlogout']);

    $token = $user->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/v1/auth/logout')
        ->assertNoContent();
});

test('logout requires authentication', function () {
    $this->postJson('/api/v1/auth/logout')->assertUnauthorized();
});
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test --compact --filter=AuthTest
```

Expected: FAIL — controller not found.

- [ ] **Step 3: Create AuthController**

```bash
mkdir -p app/Http/Controllers/Api/V1
php artisan make:controller Api/V1/AuthController --no-interaction
```

Replace `app/Http/Controllers/Api/V1/AuthController.php`:

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', $request->username)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Kredensial tidak valid.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'username' => ['Akun tidak aktif.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'nama' => $user->name,
                    'role' => $user->role,
                ],
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(null, 204);
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

```bash
php artisan test --compact --filter=AuthTest
```

Expected: All 5 tests PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Api/V1/AuthController.php tests/Feature/Api/AuthTest.php
git commit -m "feat: add auth login and logout endpoints with tests"
```

---

## Task 5: Lansia Endpoints

**Files:**
- Create: `app/Http/Controllers/Api/V1/LansiaController.php`
- Create: `tests/Feature/Api/LansiaTest.php`

- [ ] **Step 1: Write failing tests**

```bash
php artisan make:test --pest ApiLansiaTest --no-interaction
```

Move/rename to `tests/Feature/Api/LansiaTest.php`:

```php
<?php

use App\Models\BantuanGizi;
use App\Models\Lansia;
use App\Models\User;

beforeEach(function () {
    $this->operator = User::factory()->operator()->create();
    $this->admin = User::factory()->admin()->create();
    $this->lurah = User::factory()->lurah()->create();
});

test('operator can list lansia', function () {
    Lansia::factory(3)->create(['created_by' => $this->operator->id]);

    $this->actingAs($this->operator)
        ->getJson('/api/v1/lansia')
        ->assertOk()
        ->assertJsonStructure(['data', 'meta']);
});

test('list lansia supports filter by nama', function () {
    Lansia::factory()->create(['nama' => 'Budi Santoso', 'created_by' => $this->operator->id]);
    Lansia::factory()->create(['nama' => 'Siti Aminah', 'created_by' => $this->operator->id]);

    $response = $this->actingAs($this->operator)
        ->getJson('/api/v1/lansia?nama=Budi');

    $response->assertOk();
    $data = $response->json('data');
    expect($data)->toHaveCount(1);
    expect($data[0]['nama'])->toBe('Budi Santoso');
});

test('list lansia supports filter by rw', function () {
    Lansia::factory()->create(['rw' => '01', 'created_by' => $this->operator->id]);
    Lansia::factory()->create(['rw' => '02', 'created_by' => $this->operator->id]);

    $response = $this->actingAs($this->operator)
        ->getJson('/api/v1/lansia?rw=01');

    $response->assertOk();
    $data = $response->json('data');
    expect($data)->toHaveCount(1);
});

test('operator can create lansia', function () {
    $payload = [
        'nik' => '3201234567890001',
        'nama' => 'Ahmad Wahyudi',
        'tanggal_lahir' => '1950-03-15',
        'jenis_kelamin' => 'L',
        'alamat' => 'Jl. Merdeka No. 1',
        'rt' => '01',
        'rw' => '03',
    ];

    $response = $this->actingAs($this->operator)
        ->postJson('/api/v1/lansia', $payload);

    $response->assertCreated()
        ->assertJsonPath('data.nama', 'Ahmad Wahyudi')
        ->assertJsonPath('data.nik', '3201234567890001');

    $this->assertDatabaseHas('lansia', ['nik' => '3201234567890001', 'created_by' => $this->operator->id]);
});

test('create lansia fails with duplicate nik', function () {
    Lansia::factory()->create(['nik' => '3201234567890002', 'created_by' => $this->operator->id]);

    $this->actingAs($this->operator)
        ->postJson('/api/v1/lansia', [
            'nik' => '3201234567890002',
            'nama' => 'Other Person',
            'tanggal_lahir' => '1955-01-01',
            'jenis_kelamin' => 'P',
            'alamat' => 'Jl. Test',
            'rw' => '01',
        ])
        ->assertUnprocessable();
});

test('operator can view lansia detail', function () {
    $lansia = Lansia::factory()->create(['created_by' => $this->operator->id]);

    $this->actingAs($this->operator)
        ->getJson("/api/v1/lansia/{$lansia->lansia_id}")
        ->assertOk()
        ->assertJsonPath('data.lansia_id', $lansia->lansia_id);
});

test('operator can update lansia', function () {
    $lansia = Lansia::factory()->create(['created_by' => $this->operator->id]);

    $this->actingAs($this->operator)
        ->putJson("/api/v1/lansia/{$lansia->lansia_id}", ['nama' => 'Nama Baru'])
        ->assertOk()
        ->assertJsonPath('data.nama', 'Nama Baru');
});

test('operator can delete lansia', function () {
    $lansia = Lansia::factory()->create(['created_by' => $this->operator->id]);

    $this->actingAs($this->operator)
        ->deleteJson("/api/v1/lansia/{$lansia->lansia_id}")
        ->assertNoContent();

    $this->assertSoftDeleted('lansia', ['lansia_id' => $lansia->lansia_id]);
});

test('lurah cannot create lansia', function () {
    $this->actingAs($this->lurah)
        ->postJson('/api/v1/lansia', [
            'nik' => '3201234567890099',
            'nama' => 'Test',
            'tanggal_lahir' => '1950-01-01',
            'jenis_kelamin' => 'L',
            'alamat' => 'Test',
            'rw' => '01',
        ])
        ->assertForbidden();
});

test('unauthenticated user cannot access lansia', function () {
    $this->getJson('/api/v1/lansia')->assertUnauthorized();
});

test('operator can view status bantuan lansia', function () {
    $lansia = Lansia::factory()->create(['created_by' => $this->operator->id]);
    BantuanGizi::factory()->create([
        'lansia_id' => $lansia->lansia_id,
        'status_penerima' => 'penerima',
    ]);

    $this->actingAs($this->operator)
        ->getJson("/api/v1/lansia/{$lansia->lansia_id}/status-bantuan")
        ->assertOk()
        ->assertJsonPath('data.status_penerima', 'penerima');
});
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test --compact --filter=LansiaTest
```

Expected: FAIL — controller not found.

- [ ] **Step 3: Create LansiaController**

```bash
php artisan make:controller Api/V1/LansiaController --api --no-interaction
```

Replace `app/Http/Controllers/Api/V1/LansiaController.php`:

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BantuanResource;
use App\Http\Resources\LansiaResource;
use App\Models\Lansia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LansiaController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Lansia::query();

        if ($request->filled('nama')) {
            $query->where('nama', 'like', '%' . $request->nama . '%');
        }

        if ($request->filled('rw')) {
            $query->where('rw', $request->rw);
        }

        if ($request->filled('kondisi_kesehatan')) {
            $query->whereHas('pemeriksaan', function ($q) use ($request) {
                $q->where('hasil_periksa', $request->kondisi_kesehatan)
                    ->whereIn('pemeriksaan_id', function ($sub) {
                        $sub->selectRaw('MAX(pemeriksaan_id)')
                            ->from('pemeriksaan_kesehatan')
                            ->groupBy('lansia_id');
                    });
            });
        }

        if ($request->filled('status_bantuan')) {
            $query->whereHas('bantuanGizi', function ($q) use ($request) {
                $q->where('status_penerima', $request->status_bantuan);
            });
        }

        return LansiaResource::collection($query->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nik' => ['required', 'string', 'size:16', 'unique:lansia,nik'],
            'nama' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'alamat' => ['required', 'string'],
            'rt' => ['nullable', 'string', 'max:5'],
            'rw' => ['required', 'string', 'max:5'],
        ]);

        $validated['created_by'] = $request->user()->id;

        $lansia = Lansia::create($validated);

        return (new LansiaResource($lansia))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $lansia): JsonResponse
    {
        $model = Lansia::findOrFail($lansia);

        return (new LansiaResource($model))->response();
    }

    public function update(Request $request, int $lansia): JsonResponse
    {
        $model = Lansia::findOrFail($lansia);

        $validated = $request->validate([
            'nik' => ['sometimes', 'string', 'size:16', 'unique:lansia,nik,' . $model->lansia_id . ',lansia_id'],
            'nama' => ['sometimes', 'string', 'max:255'],
            'tanggal_lahir' => ['sometimes', 'date'],
            'jenis_kelamin' => ['sometimes', 'in:L,P'],
            'alamat' => ['sometimes', 'string'],
            'rt' => ['nullable', 'string', 'max:5'],
            'rw' => ['sometimes', 'string', 'max:5'],
        ]);

        $model->update($validated);

        return (new LansiaResource($model))->response();
    }

    public function destroy(int $lansia): JsonResponse
    {
        Lansia::findOrFail($lansia)->delete();

        return response()->json(null, 204);
    }

    public function uploadFotoKtp(Request $request, int $lansia): JsonResponse
    {
        $request->validate([
            'foto_ktp' => ['required', 'image', 'max:2048'],
        ]);

        $model = Lansia::findOrFail($lansia);
        $path = $request->file('foto_ktp')->store('foto-ktp', 'public');
        $model->update(['foto_ktp' => $path]);

        return (new LansiaResource($model))->response();
    }

    public function statusBantuan(int $lansia): JsonResponse
    {
        $model = Lansia::findOrFail($lansia);
        $bantuan = $model->bantuanGizi()
            ->orderByDesc('periode_tahun')
            ->orderByDesc('periode_bulan')
            ->first();

        if (! $bantuan) {
            return response()->json(['data' => ['status_penerima' => null, 'lansia_id' => $lansia]]);
        }

        return (new BantuanResource($bantuan))->response();
    }
}
```

- [ ] **Step 4: Run tests**

```bash
php artisan test --compact --filter=LansiaTest
```

Expected: All tests PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Api/V1/LansiaController.php tests/Feature/Api/LansiaTest.php
git commit -m "feat: add lansia CRUD endpoints with tests"
```

---

## Task 6: Pemeriksaan Endpoints

**Files:**
- Create: `app/Http/Controllers/Api/V1/PemeriksaanController.php`
- Create: `tests/Feature/Api/PemeriksaanTest.php`

- [ ] **Step 1: Write failing tests**

```bash
php artisan make:test --pest ApiPemeriksaanTest --no-interaction
```

Move/rename to `tests/Feature/Api/PemeriksaanTest.php`:

```php
<?php

use App\Models\Lansia;
use App\Models\PemeriksaanKesehatan;
use App\Models\User;

beforeEach(function () {
    $this->operator = User::factory()->operator()->create();
    $this->lansia = Lansia::factory()->create(['created_by' => $this->operator->id]);
});

test('operator can list pemeriksaan for a lansia', function () {
    PemeriksaanKesehatan::factory(3)->create(['lansia_id' => $this->lansia->lansia_id]);

    $this->actingAs($this->operator)
        ->getJson("/api/v1/lansia/{$this->lansia->lansia_id}/pemeriksaan")
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

test('operator can create pemeriksaan', function () {
    $payload = [
        'tanggal_periksa' => '2026-05-10',
        'berat_badan' => 55.5,
        'tekanan_darah' => '120/80',
        'hasil_periksa' => 'baik',
        'catatan' => 'Normal',
    ];

    $this->actingAs($this->operator)
        ->postJson("/api/v1/lansia/{$this->lansia->lansia_id}/pemeriksaan", $payload)
        ->assertCreated()
        ->assertJsonPath('data.hasil_periksa', 'baik');

    $this->assertDatabaseHas('pemeriksaan_kesehatan', [
        'lansia_id' => $this->lansia->lansia_id,
        'hasil_periksa' => 'baik',
    ]);
});

test('create pemeriksaan fails with invalid hasil_periksa', function () {
    $this->actingAs($this->operator)
        ->postJson("/api/v1/lansia/{$this->lansia->lansia_id}/pemeriksaan", [
            'tanggal_periksa' => '2026-05-10',
            'hasil_periksa' => 'invalid_value',
        ])
        ->assertUnprocessable();
});

test('operator can view single pemeriksaan', function () {
    $periksa = PemeriksaanKesehatan::factory()->create(['lansia_id' => $this->lansia->lansia_id]);

    $this->actingAs($this->operator)
        ->getJson("/api/v1/pemeriksaan/{$periksa->pemeriksaan_id}")
        ->assertOk()
        ->assertJsonPath('data.pemeriksaan_id', $periksa->pemeriksaan_id);
});

test('operator can update pemeriksaan', function () {
    $periksa = PemeriksaanKesehatan::factory()->create(['lansia_id' => $this->lansia->lansia_id]);

    $this->actingAs($this->operator)
        ->putJson("/api/v1/pemeriksaan/{$periksa->pemeriksaan_id}", ['hasil_periksa' => 'buruk'])
        ->assertOk()
        ->assertJsonPath('data.hasil_periksa', 'buruk');
});
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test --compact --filter=PemeriksaanTest
```

Expected: FAIL.

- [ ] **Step 3: Create PemeriksaanController**

```bash
php artisan make:controller Api/V1/PemeriksaanController --no-interaction
```

Replace `app/Http/Controllers/Api/V1/PemeriksaanController.php`:

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PemeriksaanResource;
use App\Models\Lansia;
use App\Models\PemeriksaanKesehatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PemeriksaanController extends Controller
{
    public function index(int $lansia): AnonymousResourceCollection
    {
        $model = Lansia::findOrFail($lansia);

        return PemeriksaanResource::collection(
            $model->pemeriksaan()->orderByDesc('tanggal_periksa')->get()
        );
    }

    public function store(Request $request, int $lansia): JsonResponse
    {
        Lansia::findOrFail($lansia);

        $validated = $request->validate([
            'tanggal_periksa' => ['required', 'date'],
            'berat_badan' => ['nullable', 'numeric', 'min:1', 'max:300'],
            'tekanan_darah' => ['nullable', 'string', 'max:20'],
            'hasil_periksa' => ['required', 'in:baik,sedang,buruk'],
            'catatan' => ['nullable', 'string'],
        ]);

        $validated['lansia_id'] = $lansia;
        $periksa = PemeriksaanKesehatan::create($validated);

        return (new PemeriksaanResource($periksa))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $pemeriksaan): JsonResponse
    {
        $model = PemeriksaanKesehatan::findOrFail($pemeriksaan);

        return (new PemeriksaanResource($model))->response();
    }

    public function update(Request $request, int $pemeriksaan): JsonResponse
    {
        $model = PemeriksaanKesehatan::findOrFail($pemeriksaan);

        $validated = $request->validate([
            'tanggal_periksa' => ['sometimes', 'date'],
            'berat_badan' => ['nullable', 'numeric', 'min:1', 'max:300'],
            'tekanan_darah' => ['nullable', 'string', 'max:20'],
            'hasil_periksa' => ['sometimes', 'in:baik,sedang,buruk'],
            'catatan' => ['nullable', 'string'],
        ]);

        $model->update($validated);

        return (new PemeriksaanResource($model))->response();
    }
}
```

- [ ] **Step 4: Run tests**

```bash
php artisan test --compact --filter=PemeriksaanTest
```

Expected: All PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Api/V1/PemeriksaanController.php tests/Feature/Api/PemeriksaanTest.php
git commit -m "feat: add pemeriksaan kesehatan endpoints with tests"
```

---

## Task 7: Pendataan / Verifikasi Endpoints

**Files:**
- Create: `app/Http/Controllers/Api/V1/PendataanController.php`
- Create: `tests/Feature/Api/PendataanTest.php`

- [ ] **Step 1: Write failing tests**

```bash
php artisan make:test --pest ApiPendataanTest --no-interaction
```

Move to `tests/Feature/Api/PendataanTest.php`:

```php
<?php

use App\Models\Lansia;
use App\Models\Pendataan;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->operator = User::factory()->operator()->create();
    $this->lurah = User::factory()->lurah()->create();
});

test('admin can list pendataan', function () {
    Pendataan::factory(3)->create(['user_id' => $this->operator->id]);

    $this->actingAs($this->admin)
        ->getJson('/api/v1/pendataan')
        ->assertOk()
        ->assertJsonStructure(['data', 'meta']);
});

test('operator cannot access pendataan list', function () {
    $this->actingAs($this->operator)
        ->getJson('/api/v1/pendataan')
        ->assertForbidden();
});

test('admin can verify pendataan', function () {
    $pendataan = Pendataan::factory()->create(['user_id' => $this->operator->id]);

    $this->actingAs($this->admin)
        ->postJson("/api/v1/pendataan/{$pendataan->pendataan_id}/verifikasi", [
            'status_verifikasi' => 'terverifikasi',
        ])
        ->assertOk()
        ->assertJsonPath('data.status_verifikasi', 'terverifikasi');

    $this->assertDatabaseHas('pendataan', [
        'pendataan_id' => $pendataan->pendataan_id,
        'status_verifikasi' => 'terverifikasi',
        'verified_by' => $this->admin->id,
    ]);
});

test('admin can reject pendataan', function () {
    $pendataan = Pendataan::factory()->create(['user_id' => $this->operator->id]);

    $this->actingAs($this->admin)
        ->postJson("/api/v1/pendataan/{$pendataan->pendataan_id}/verifikasi", [
            'status_verifikasi' => 'ditolak',
        ])
        ->assertOk()
        ->assertJsonPath('data.status_verifikasi', 'ditolak');
});

test('verifikasi fails with invalid status', function () {
    $pendataan = Pendataan::factory()->create(['user_id' => $this->operator->id]);

    $this->actingAs($this->admin)
        ->postJson("/api/v1/pendataan/{$pendataan->pendataan_id}/verifikasi", [
            'status_verifikasi' => 'invalid',
        ])
        ->assertUnprocessable();
});
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test --compact --filter=PendataanTest
```

Expected: FAIL.

- [ ] **Step 3: Create PendataanController**

```bash
php artisan make:controller Api/V1/PendataanController --no-interaction
```

Replace `app/Http/Controllers/Api/V1/PendataanController.php`:

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PendataanResource;
use App\Models\Pendataan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PendataanController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PendataanResource::collection(
            Pendataan::with('lansia')->paginate(15)
        );
    }

    public function verifikasi(Request $request, int $pendataan): JsonResponse
    {
        $model = Pendataan::findOrFail($pendataan);

        $request->validate([
            'status_verifikasi' => ['required', 'in:terverifikasi,ditolak'],
        ]);

        $model->update([
            'status_verifikasi' => $request->status_verifikasi,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        return (new PendataanResource($model))->response();
    }
}
```

- [ ] **Step 4: Run tests**

```bash
php artisan test --compact --filter=PendataanTest
```

Expected: All PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Api/V1/PendataanController.php tests/Feature/Api/PendataanTest.php
git commit -m "feat: add pendataan verifikasi endpoints with tests"
```

---

## Task 8: RankingService & Bantuan Endpoints

**Files:**
- Create: `app/Services/RankingService.php`
- Create: `app/Http/Controllers/Api/V1/BantuanController.php`
- Create: `tests/Feature/Api/BantuanTest.php`

- [ ] **Step 1: Write failing tests**

```bash
php artisan make:test --pest ApiBantuanTest --no-interaction
```

Move to `tests/Feature/Api/BantuanTest.php`:

```php
<?php

use App\Models\BantuanGizi;
use App\Models\Lansia;
use App\Models\Pendataan;
use App\Models\PemeriksaanKesehatan;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->lurah = User::factory()->lurah()->create();
    $this->operator = User::factory()->operator()->create();
});

test('admin can set kuota bantuan', function () {
    $this->actingAs($this->admin)
        ->postJson('/api/v1/bantuan/kuota', [
            'kuota' => 50,
            'periode_bulan' => 5,
            'periode_tahun' => 2026,
        ])
        ->assertOk()
        ->assertJsonPath('data.kuota', 50);
});

test('admin can get kuota bantuan', function () {
    cache()->put('bantuan_kuota_5_2026', ['kuota' => 50, 'periode_bulan' => 5, 'periode_tahun' => 2026]);

    $this->actingAs($this->admin)
        ->getJson('/api/v1/bantuan/kuota?periode_bulan=5&periode_tahun=2026')
        ->assertOk()
        ->assertJsonPath('data.kuota', 50);
});

test('admin can trigger ranking and top lansia become penerima', function () {
    cache()->put('bantuan_kuota_5_2026', ['kuota' => 2, 'periode_bulan' => 5, 'periode_tahun' => 2026]);

    $operator = User::factory()->operator()->create();

    // Create 3 lansia with different ages and health conditions
    $lansiaOld = Lansia::factory()->create([
        'tanggal_lahir' => '1940-01-01', // oldest
        'created_by' => $operator->id,
    ]);
    Pendataan::factory()->terverifikasi()->create(['lansia_id' => $lansiaOld->lansia_id, 'user_id' => $operator->id]);
    PemeriksaanKesehatan::factory()->create(['lansia_id' => $lansiaOld->lansia_id, 'hasil_periksa' => 'buruk']);

    $lansiaMiddle = Lansia::factory()->create([
        'tanggal_lahir' => '1950-01-01',
        'created_by' => $operator->id,
    ]);
    Pendataan::factory()->terverifikasi()->create(['lansia_id' => $lansiaMiddle->lansia_id, 'user_id' => $operator->id]);
    PemeriksaanKesehatan::factory()->create(['lansia_id' => $lansiaMiddle->lansia_id, 'hasil_periksa' => 'sedang']);

    $lansiaYoung = Lansia::factory()->create([
        'tanggal_lahir' => '1960-01-01', // youngest
        'created_by' => $operator->id,
    ]);
    Pendataan::factory()->terverifikasi()->create(['lansia_id' => $lansiaYoung->lansia_id, 'user_id' => $operator->id]);
    PemeriksaanKesehatan::factory()->create(['lansia_id' => $lansiaYoung->lansia_id, 'hasil_periksa' => 'baik']);

    $response = $this->actingAs($this->admin)
        ->postJson('/api/v1/bantuan/ranking', [
            'periode_bulan' => 5,
            'periode_tahun' => 2026,
        ]);

    $response->assertOk()->assertJsonPath('data.total_penerima', 2);

    $this->assertDatabaseHas('bantuan_gizi', [
        'lansia_id' => $lansiaOld->lansia_id,
        'status_penerima' => 'penerima',
    ]);
    $this->assertDatabaseHas('bantuan_gizi', [
        'lansia_id' => $lansiaYoung->lansia_id,
        'status_penerima' => 'tidak_penerima',
    ]);
});

test('operator cannot trigger ranking', function () {
    $this->actingAs($this->operator)
        ->postJson('/api/v1/bantuan/ranking', ['periode_bulan' => 5, 'periode_tahun' => 2026])
        ->assertForbidden();
});

test('admin can list bantuan penerima', function () {
    BantuanGizi::factory(3)->create(['status_penerima' => 'penerima']);

    $this->actingAs($this->admin)
        ->getJson('/api/v1/bantuan')
        ->assertOk()
        ->assertJsonStructure(['data', 'meta']);
});

test('lurah can approve bantuan', function () {
    $bantuan = BantuanGizi::factory()->create(['status_penerima' => 'penerima']);

    $this->actingAs($this->lurah)
        ->postJson("/api/v1/bantuan/{$bantuan->bantuan_id}/approve")
        ->assertOk()
        ->assertJsonPath('data.approved_by', $this->lurah->id);

    $this->assertDatabaseHas('bantuan_gizi', [
        'bantuan_id' => $bantuan->bantuan_id,
        'approved_by' => $this->lurah->id,
    ]);
});

test('operator cannot approve bantuan', function () {
    $bantuan = BantuanGizi::factory()->create(['status_penerima' => 'penerima']);

    $this->actingAs($this->operator)
        ->postJson("/api/v1/bantuan/{$bantuan->bantuan_id}/approve")
        ->assertForbidden();
});
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test --compact --filter=BantuanTest
```

Expected: FAIL.

- [ ] **Step 3: Create RankingService**

```bash
mkdir -p app/Services
php artisan make:class Services/RankingService --no-interaction
```

Replace `app/Services/RankingService.php`:

```php
<?php

namespace App\Services;

use App\Models\BantuanGizi;
use App\Models\Lansia;
use App\Models\PemeriksaanKesehatan;

class RankingService
{
    /**
     * @var array<string, int>
     */
    private array $healthScoreMap = [
        'buruk' => 10,
        'sedang' => 6,
        'baik' => 3,
    ];

    public function rank(int $periodeBulan, int $periodeTabun, int $kuota): array
    {
        $lansiaIds = \App\Models\Pendataan::where('status_verifikasi', 'terverifikasi')
            ->pluck('lansia_id');

        $ranked = Lansia::whereIn('lansia_id', $lansiaIds)
            ->get()
            ->map(function (Lansia $lansia) use ($periodeBulan, $periodeTabun) {
                $latestPeriksa = PemeriksaanKesehatan::where('lansia_id', $lansia->lansia_id)
                    ->orderByDesc('tanggal_periksa')
                    ->orderByDesc('pemeriksaan_id')
                    ->first();

                $hasilPeriksa = $latestPeriksa?->hasil_periksa ?? 'sedang';
                $healthScore = $this->healthScoreMap[$hasilPeriksa] ?? 6;

                $usia = $lansia->usia;
                $skor = ($usia / 100 * 0.6) + ($healthScore / 10 * 0.4);

                return [
                    'lansia_id' => $lansia->lansia_id,
                    'skor_ranking' => round($skor, 4),
                    'periode_bulan' => $periodeBulan,
                    'periode_tahun' => $periodeTabun,
                ];
            })
            ->sortByDesc('skor_ranking')
            ->values();

        $penerima = $ranked->take($kuota)->pluck('lansia_id');

        foreach ($ranked as $item) {
            BantuanGizi::updateOrCreate(
                [
                    'lansia_id' => $item['lansia_id'],
                    'periode_bulan' => $periodeBulan,
                    'periode_tahun' => $periodeTabun,
                ],
                [
                    'skor_ranking' => $item['skor_ranking'],
                    'status_penerima' => $penerima->contains($item['lansia_id']) ? 'penerima' : 'tidak_penerima',
                ]
            );
        }

        return [
            'total_diproses' => $ranked->count(),
            'total_penerima' => min($kuota, $ranked->count()),
        ];
    }
}
```

- [ ] **Step 4: Create BantuanController**

```bash
php artisan make:controller Api/V1/BantuanController --no-interaction
```

Replace `app/Http/Controllers/Api/V1/BantuanController.php`:

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BantuanResource;
use App\Models\BantuanGizi;
use App\Services\RankingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BantuanController extends Controller
{
    public function __construct(public RankingService $rankingService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = BantuanGizi::with('lansia');

        if ($request->filled('periode_bulan')) {
            $query->where('periode_bulan', $request->periode_bulan);
        }

        if ($request->filled('periode_tahun')) {
            $query->where('periode_tahun', $request->periode_tahun);
        }

        if ($request->filled('status_penerima')) {
            $query->where('status_penerima', $request->status_penerima);
        }

        return BantuanResource::collection($query->paginate(15));
    }

    public function setKuota(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kuota' => ['required', 'integer', 'min:1'],
            'periode_bulan' => ['required', 'integer', 'min:1', 'max:12'],
            'periode_tahun' => ['required', 'integer', 'min:2020'],
        ]);

        $key = "bantuan_kuota_{$validated['periode_bulan']}_{$validated['periode_tahun']}";
        cache()->put($key, $validated);

        return response()->json(['data' => $validated]);
    }

    public function getKuota(Request $request): JsonResponse
    {
        $request->validate([
            'periode_bulan' => ['required', 'integer', 'min:1', 'max:12'],
            'periode_tahun' => ['required', 'integer', 'min:2020'],
        ]);

        $key = "bantuan_kuota_{$request->periode_bulan}_{$request->periode_tahun}";
        $kuota = cache()->get($key);

        if (! $kuota) {
            return response()->json(['message' => 'Kuota belum diatur.'], 404);
        }

        return response()->json(['data' => $kuota]);
    }

    public function ranking(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'periode_bulan' => ['required', 'integer', 'min:1', 'max:12'],
            'periode_tahun' => ['required', 'integer', 'min:2020'],
        ]);

        $key = "bantuan_kuota_{$validated['periode_bulan']}_{$validated['periode_tahun']}";
        $kuotaData = cache()->get($key);

        $kuota = $kuotaData['kuota'] ?? 999;

        $result = $this->rankingService->rank(
            $validated['periode_bulan'],
            $validated['periode_tahun'],
            $kuota
        );

        return response()->json(['data' => $result]);
    }

    public function approve(Request $request, int $bantuan): JsonResponse
    {
        $model = BantuanGizi::findOrFail($bantuan);

        $model->update([
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return (new BantuanResource($model))->response();
    }
}
```

- [ ] **Step 5: Run tests**

```bash
php artisan test --compact --filter=BantuanTest
```

Expected: All PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Services/RankingService.php app/Http/Controllers/Api/V1/BantuanController.php tests/Feature/Api/BantuanTest.php
git commit -m "feat: add bantuan gizi endpoints with ranking service and tests"
```

---

## Task 9: Dashboard Stats Endpoint

**Files:**
- Create: `app/Http/Controllers/Api/V1/DashboardController.php`
- Create: `tests/Feature/Api/DashboardTest.php`

- [ ] **Step 1: Write failing tests**

```bash
php artisan make:test --pest ApiDashboardTest --no-interaction
```

Move to `tests/Feature/Api/DashboardTest.php`:

```php
<?php

use App\Models\BantuanGizi;
use App\Models\Lansia;
use App\Models\PemeriksaanKesehatan;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->lurah = User::factory()->lurah()->create();
    $this->operator = User::factory()->operator()->create();
});

test('admin can access dashboard stats', function () {
    Lansia::factory(5)->create(['created_by' => $this->operator->id]);

    $this->actingAs($this->admin)
        ->getJson('/api/v1/dashboard/stats')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'total_lansia',
                'per_rw',
                'distribusi_usia',
                'kondisi_kesehatan',
                'total_penerima_bantuan',
            ],
        ]);
});

test('lurah can access dashboard stats', function () {
    $this->actingAs($this->lurah)
        ->getJson('/api/v1/dashboard/stats')
        ->assertOk();
});

test('operator cannot access dashboard stats', function () {
    $this->actingAs($this->operator)
        ->getJson('/api/v1/dashboard/stats')
        ->assertForbidden();
});

test('dashboard stats show correct total lansia', function () {
    Lansia::factory(7)->create(['created_by' => $this->operator->id]);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/v1/dashboard/stats');

    expect($response->json('data.total_lansia'))->toBe(7);
});

test('dashboard stats include per rw breakdown', function () {
    Lansia::factory(3)->create(['rw' => '01', 'created_by' => $this->operator->id]);
    Lansia::factory(2)->create(['rw' => '02', 'created_by' => $this->operator->id]);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/v1/dashboard/stats');

    $perRw = collect($response->json('data.per_rw'));
    expect($perRw->firstWhere('rw', '01')['total'])->toBe(3);
    expect($perRw->firstWhere('rw', '02')['total'])->toBe(2);
});
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test --compact --filter=DashboardTest
```

Expected: FAIL.

- [ ] **Step 3: Create DashboardController**

```bash
php artisan make:controller Api/V1/DashboardController --no-interaction
```

Replace `app/Http/Controllers/Api/V1/DashboardController.php`:

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BantuanGizi;
use App\Models\Lansia;
use App\Models\PemeriksaanKesehatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $totalLansia = Lansia::count();

        $perRw = Lansia::select('rw', DB::raw('count(*) as total'))
            ->groupBy('rw')
            ->orderBy('rw')
            ->get()
            ->map(fn ($row) => ['rw' => $row->rw, 'total' => $row->total]);

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
        })->map(fn ($group) => $group->count());

        $kondisiKesehatan = PemeriksaanKesehatan::whereIn(
            'pemeriksaan_id',
            PemeriksaanKesehatan::select(DB::raw('MAX(pemeriksaan_id)'))->groupBy('lansia_id')
        )
            ->select('hasil_periksa', DB::raw('count(*) as total'))
            ->groupBy('hasil_periksa')
            ->get()
            ->map(fn ($row) => ['kondisi' => $row->hasil_periksa, 'total' => $row->total]);

        $totalPenerimaBantuan = BantuanGizi::where('status_penerima', 'penerima')->count();

        return response()->json([
            'data' => [
                'total_lansia' => $totalLansia,
                'per_rw' => $perRw,
                'distribusi_usia' => $distribusiUsia,
                'kondisi_kesehatan' => $kondisiKesehatan,
                'total_penerima_bantuan' => $totalPenerimaBantuan,
            ],
        ]);
    }
}
```

- [ ] **Step 4: Run tests**

```bash
php artisan test --compact --filter=DashboardTest
```

Expected: All PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Api/V1/DashboardController.php tests/Feature/Api/DashboardTest.php
git commit -m "feat: add dashboard stats endpoint with tests"
```

---

## Task 10: Laporan Endpoints

**Files:**
- Create: `app/Http/Controllers/Api/V1/LaporanController.php`
- Create: `tests/Feature/Api/LaporanTest.php`

- [ ] **Step 1: Write failing tests**

```bash
php artisan make:test --pest ApiLaporanTest --no-interaction
```

Move to `tests/Feature/Api/LaporanTest.php`:

```php
<?php

use App\Models\BantuanGizi;
use App\Models\Lansia;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->lurah = User::factory()->lurah()->create();
    $this->operator = User::factory()->operator()->create();
});

test('admin can get laporan', function () {
    BantuanGizi::factory(5)->create(['status_penerima' => 'penerima']);

    $this->actingAs($this->admin)
        ->getJson('/api/v1/laporan')
        ->assertOk()
        ->assertJsonStructure(['data', 'meta']);
});

test('laporan supports filter by rw', function () {
    $lansiaRw1 = Lansia::factory()->create(['rw' => '01', 'created_by' => $this->operator->id]);
    $lansiaRw2 = Lansia::factory()->create(['rw' => '02', 'created_by' => $this->operator->id]);
    BantuanGizi::factory()->create(['lansia_id' => $lansiaRw1->lansia_id, 'status_penerima' => 'penerima']);
    BantuanGizi::factory()->create(['lansia_id' => $lansiaRw2->lansia_id, 'status_penerima' => 'penerima']);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/v1/laporan?rw=01');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
});

test('laporan supports filter by jenis penerima', function () {
    BantuanGizi::factory(2)->create(['status_penerima' => 'penerima']);
    BantuanGizi::factory(3)->create(['status_penerima' => 'tidak_penerima']);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/v1/laporan?jenis=penerima');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(2);
});

test('admin can download laporan as csv', function () {
    BantuanGizi::factory(3)->create(['status_penerima' => 'penerima']);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/v1/laporan/download');

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
});

test('operator cannot access laporan', function () {
    $this->actingAs($this->operator)
        ->getJson('/api/v1/laporan')
        ->assertForbidden();
});

test('lurah can access laporan', function () {
    $this->actingAs($this->lurah)
        ->getJson('/api/v1/laporan')
        ->assertOk();
});
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test --compact --filter=LaporanTest
```

Expected: FAIL.

- [ ] **Step 3: Create LaporanController**

```bash
php artisan make:controller Api/V1/LaporanController --no-interaction
```

Replace `app/Http/Controllers/Api/V1/LaporanController.php`:

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BantuanResource;
use App\Models\BantuanGizi;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = BantuanGizi::with('lansia');

        if ($request->filled('rw')) {
            $query->whereHas('lansia', fn ($q) => $q->where('rw', $request->rw));
        }

        if ($request->filled('jenis') && $request->jenis !== 'semua') {
            $query->where('status_penerima', $request->jenis);
        }

        if ($request->filled('periode_bulan')) {
            $query->where('periode_bulan', $request->periode_bulan);
        }

        if ($request->filled('periode_tahun')) {
            $query->where('periode_tahun', $request->periode_tahun);
        }

        $limit = min((int) ($request->limit ?? 15), 200);

        return BantuanResource::collection($query->paginate($limit));
    }

    public function download(Request $request): StreamedResponse
    {
        $query = BantuanGizi::with('lansia');

        if ($request->filled('rw')) {
            $query->whereHas('lansia', fn ($q) => $q->where('rw', $request->rw));
        }

        if ($request->filled('jenis') && $request->jenis !== 'semua') {
            $query->where('status_penerima', $request->jenis);
        }

        if ($request->filled('periode_bulan')) {
            $query->where('periode_bulan', $request->periode_bulan);
        }

        if ($request->filled('periode_tahun')) {
            $query->where('periode_tahun', $request->periode_tahun);
        }

        $filename = 'laporan-bantuan-gizi-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['No', 'NIK', 'Nama', 'Usia', 'RW', 'Periode', 'Status', 'Skor']);

            $no = 1;
            $query->chunk(200, function ($items) use ($handle, &$no) {
                foreach ($items as $item) {
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
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
```

- [ ] **Step 4: Run tests**

```bash
php artisan test --compact --filter=LaporanTest
```

Expected: All PASS.

- [ ] **Step 5: Run full test suite**

```bash
php artisan test --compact
```

Expected: All tests PASS.

- [ ] **Step 6: Run Pint formatter**

```bash
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/Api/V1/LaporanController.php tests/Feature/Api/LaporanTest.php
git commit -m "feat: add laporan endpoints with csv download and tests"
```

---

## Self-Review

### Spec Coverage Check

| Spec requirement | Task |
|---|---|
| Auth login/logout | Task 4 |
| Lansia CRUD + filter nama/rw/kondisi/status | Task 5 |
| Upload foto KTP | Task 5 |
| Status bantuan per lansia | Task 5 |
| Pemeriksaan CRUD | Task 6 |
| Pendataan verifikasi | Task 7 |
| Kuota set/get | Task 8 |
| Auto-ranking (usia 60% + kesehatan 40%) | Task 8 |
| Bantuan approval (lurah) | Task 8 |
| Dashboard stats | Task 9 |
| Laporan + filter + CSV download | Task 10 |
| Role middleware (operator/admin/lurah) | Task 3 |
| Sanctum token auth | Task 1 |
| API Resources response transform | Task 3 |
| `/api/v1/` prefix | Task 3 |

✅ All spec requirements covered.

### Route Conflict Note

`routes/api.php` defines `GET /bantuan/kuota` and `GET /bantuan` inside same middleware group. Laravel resolves these fine since `kuota` is a literal segment, not `{bantuan}`. No conflict.
