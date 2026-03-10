<?php

namespace App\Modules\Solar\Services;

use App\Modules\Solar\Models\EnergyUtility;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class EnergyUtilityResolverService
{
    public function resolveUtilityByCity(string $city, string $state): ?EnergyUtility
    {
        $normalizedCity = $this->normalize($city);
        $normalizedState = strtoupper(trim($state));

        if ($normalizedCity === '' || $normalizedState === '') {
            return null;
        }

        return EnergyUtility::query()
            ->where('state', $normalizedState)
            ->get()
            ->first(function (EnergyUtility $utility) use ($normalizedCity): bool {
                return collect($utility->cities_json ?? [])
                    ->map(fn (mixed $city): string => $this->normalize((string) $city))
                    ->contains($normalizedCity);
            });
    }

    /**
     * @param Collection<int, EnergyUtility> $utilities
     * @return array<int, array{id: int, name: string, state: string, cities: array<int, string>}>
     */
    public function toFrontendLookup(Collection $utilities): array
    {
        return $utilities
            ->map(fn (EnergyUtility $utility): array => [
                'id' => $utility->id,
                'name' => $utility->name,
                'state' => strtoupper($utility->state),
                'cities' => array_values(array_map(
                    fn (mixed $city): string => $this->normalize((string) $city),
                    $utility->cities_json ?? [],
                )),
            ])
            ->values()
            ->all();
    }

    private function normalize(string $value): string
    {
        return Str::lower(trim(Str::ascii($value)));
    }
}
