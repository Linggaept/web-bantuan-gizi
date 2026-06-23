<?php

namespace App\Services;

use Carbon\Carbon;

class PeriodeService
{
    // Quarter start months: Jan=1, Apr=4, Jul=7, Oct=10
    private const QUARTER_STARTS = [1, 4, 7, 10];

    /**
     * Get the current active periode (quarter start month + year).
     *
     * @return array{bulan: int, tahun: int}
     */
    public static function current(): array
    {
        return self::fromDate(Carbon::now());
    }

    /**
     * Get periode from a given date.
     *
     * @return array{bulan: int, tahun: int}
     */
    public static function fromDate(Carbon $date): array
    {
        $month = $date->month;
        $bulan = 1;

        foreach (array_reverse(self::QUARTER_STARTS) as $start) {
            if ($month >= $start) {
                $bulan = $start;
                break;
            }
        }

        return ['bulan' => $bulan, 'tahun' => $date->year];
    }

    /**
     * Get all 4 periods for a given year.
     *
     * @return array<int, array{bulan: int, tahun: int, label: string}>
     */
    public static function listForYear(int $tahun): array
    {
        return array_map(fn ($m) => [
            'bulan' => $m,
            'tahun' => $tahun,
            'label' => self::label($m, $tahun),
        ], self::QUARTER_STARTS);
    }

    /**
     * Human-readable label, e.g. "Q1 2026 (Jan–Mar)"
     */
    public static function label(int $bulan, int $tahun): string
    {
        $quarters = [
            1 => ['Q1', 'Jan–Mar'],
            4 => ['Q2', 'Apr–Jun'],
            7 => ['Q3', 'Jul–Sep'],
            10 => ['Q4', 'Okt–Des'],
        ];

        [$q, $range] = $quarters[$bulan] ?? ['Q?', '?'];

        return "{$q} {$tahun} ({$range})";
    }

    /**
     * Get the previous periode.
     *
     * @return array{bulan: int, tahun: int}
     */
    public static function previous(int $bulan, int $tahun): array
    {
        $idx = array_search($bulan, self::QUARTER_STARTS);

        if ($idx === 0) {
            return ['bulan' => 10, 'tahun' => $tahun - 1];
        }

        return ['bulan' => self::QUARTER_STARTS[$idx - 1], 'tahun' => $tahun];
    }
}
