<?php declare(strict_types=1);

namespace App\Services;

abstract class PrideService
{
    public static function isPrideMonth(): bool {
        return now()->month === 6;
    }

    public static function getCssClassesForPrideFlag(): ?string {
        // only run in june
        if (!self::isPrideMonth()) {
            return null;
        }
        $rand = random_int(0, 100);

        if ($rand < 70) {
            return 'Gay text-pride';
        }

        $classes = ['BiPlus', 'Trans', 'NonBinary', 'Asexual', 'Pansexual', 'GayMale', 'Lesbian', 'Intersex', 'GenderFluid',
                    'Agender', ' Polyamorous', 'Omnisexual', 'Polysexual', 'AroAce', 'Genderqueer', 'Queer'];

        return $classes[array_rand($classes)] . ' text-pride';
    }
}
