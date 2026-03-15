<?php

namespace App\Support;

use App\Models\Company;
use App\Models\User;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

class CurrentCompanyContext
{
    public const SESSION_KEY = 'active_company_id';

    /**
     * @var array<int, Collection<int, Company>>
     */
    private static array $availableCompaniesCache = [];

    /**
     * @var array<string, ?Company>
     */
    private static array $resolvedCompanyCache = [];

    /**
     * @return Collection<int, Company>
     */
    public static function available(User $user): Collection
    {
        $userKey = $user->getKey();

        if (array_key_exists($userKey, self::$availableCompaniesCache)) {
            return self::$availableCompaniesCache[$userKey];
        }

        self::$availableCompaniesCache[$userKey] = $user->companies()
            ->orderByDesc('company_user.is_owner')
            ->orderBy('companies.name')
            ->get();

        return self::$availableCompaniesCache[$userKey];
    }

    public static function resolve(?User $user, ?Session $session = null): ?Company
    {
        if (! $user) {
            return null;
        }

        $activeCompanyId = (int) ($session?->get(self::SESSION_KEY) ?? 0);
        $cacheKey = self::resolvedCompanyCacheKey($user, $activeCompanyId);

        if (array_key_exists($cacheKey, self::$resolvedCompanyCache)) {
            return self::$resolvedCompanyCache[$cacheKey];
        }

        $companies = self::available($user);

        if ($companies->isEmpty()) {
            self::$resolvedCompanyCache[$cacheKey] = null;

            return null;
        }

        if ($activeCompanyId > 0) {
            $matched = $companies->firstWhere('id', (int) $activeCompanyId);

            if ($matched instanceof Company) {
                self::$resolvedCompanyCache[$cacheKey] = $matched;

                return $matched;
            }
        }

        /** @var Company $fallback */
        $fallback = $companies->first();
        $session?->put(self::SESSION_KEY, $fallback->id);
        self::$resolvedCompanyCache[self::resolvedCompanyCacheKey($user, $fallback->id)] = $fallback;

        return $fallback;
    }

    public static function remember(User $user, Session $session, int $companyId): ?Company
    {
        $company = self::available($user)->firstWhere('id', $companyId);

        if (! $company instanceof Company) {
            return null;
        }

        $session->put(self::SESSION_KEY, $company->id);
        self::$resolvedCompanyCache[self::resolvedCompanyCacheKey($user, $company->id)] = $company;

        return $company;
    }

    public static function forget(Session $session): void
    {
        $session->forget(self::SESSION_KEY);
    }

    private static function resolvedCompanyCacheKey(User $user, int $companyId): string
    {
        return $user->getKey() . ':' . $companyId;
    }
}
