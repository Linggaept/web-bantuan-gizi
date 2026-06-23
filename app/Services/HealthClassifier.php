<?php

namespace App\Services;

class HealthClassifier
{
    // Systolic 90-139 mmHg, diastolic 60-89 mmHg
    private const SYSTOLIC_MIN = 90;
    private const SYSTOLIC_MAX = 139;
    private const DIASTOLIC_MIN = 60;
    private const DIASTOLIC_MAX = 89;

    // BMI normal range
    private const BMI_MIN = 18.5;
    private const BMI_MAX = 24.9;

    /**
     * Classify health status based on weight, height, and blood pressure.
     * Returns 'sehat' if both BMI and blood pressure are in normal range, else 'sakit'.
     */
    public static function classify(?float $beratBadan, ?float $tinggiBadan, ?string $tekananDarah): string
    {
        $bmiOk = self::isBmiNormal($beratBadan, $tinggiBadan);
        $tensiOk = self::isTensiNormal($tekananDarah);

        if ($bmiOk && $tensiOk) {
            return 'sehat';
        }

        return 'sakit';
    }

    private static function isBmiNormal(?float $bb, ?float $tb): bool
    {
        if ($bb === null || $tb === null || $tb <= 0) {
            return true; // insufficient data — don't penalize
        }

        $tbM = $tb / 100;
        $bmi = $bb / ($tbM * $tbM);

        return $bmi >= self::BMI_MIN && $bmi <= self::BMI_MAX;
    }

    private static function isTensiNormal(?string $tekananDarah): bool
    {
        if (empty($tekananDarah)) {
            return true; // insufficient data — don't penalize
        }

        // Expected format: "120/80"
        if (! preg_match('/^(\d+)\/(\d+)$/', trim($tekananDarah), $matches)) {
            return true;
        }

        $systolic = (int) $matches[1];
        $diastolic = (int) $matches[2];

        return $systolic >= self::SYSTOLIC_MIN
            && $systolic <= self::SYSTOLIC_MAX
            && $diastolic >= self::DIASTOLIC_MIN
            && $diastolic <= self::DIASTOLIC_MAX;
    }
}
