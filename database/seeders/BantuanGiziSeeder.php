<?php

namespace Database\Seeders;

use App\Models\BantuanGizi;
use App\Models\User;
use App\Services\PeriodeService;
use App\Services\RankingService;
use Illuminate\Database\Seeder;

class BantuanGiziSeeder extends Seeder
{
    public function __construct(public RankingService $rankingService) {}

    public function run(): void
    {
        $lurah = User::where('role', 'lurah')->first();

        // Set kuota 20 penerima
        $kuota = 20;
        $periode = PeriodeService::current();
        $periodeBulan = $periode['bulan'];
        $periodeTahun = $periode['tahun'];

        // Run ranking
        $this->rankingService->rank($periodeBulan, $periodeTahun, $kuota);

        // Lurah approve semua penerima
        BantuanGizi::where('status_penerima', 'penerima')->each(function ($bantuan) use ($lurah) {
            $bantuan->update([
                'approved_by' => $lurah->id,
                'approved_at' => now(),
            ]);
        });
    }
}
