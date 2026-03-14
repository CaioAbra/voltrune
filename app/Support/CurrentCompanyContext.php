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
     * @return Collection<int, Company>
     */
    public static function available(User $user): Collection
    {
        return $user->companies()
            ->orderByDesc('company_user.is_owner')
            ->orderBy('companies.name')
            ->get();
    }

    public static function resolve(?User $user, ?Session $session = null): ?Company
    {
        if (! $user) {
            return null;
        }

        $companies = self::available($user);

        if ($companies->isEmpty()) {
            return null;
        }

        $activeCompanyId = $session?->get(self::SESSION_KEY);

        if ($activeCompanyId !== null) {
            $matched = $companies->firstWhere('id', (int) $activeCompanyId);

            if ($matched instanceof Company) {
                return $matched;
            }
        }

        /** @var Company $fallback */
        $fallback = $companies->first();
        $session?->put(self::SESSION_KEY, $fallback->id);

        return $fallback;
    }

    public static function remember(User $user, Session $session, int $companyId): ?Company
    {
        $company = self::available($user)->firstWhere('id', $companyId);

        if (! $company instanceof Company) {
            return null;
        }

        $session->put(self::SESSION_KEY, $company->id);

        return $company;
    }

    public static function forget(Session $session): void
    {
        $session->forget(self::SESSION_KEY);
    }
}
