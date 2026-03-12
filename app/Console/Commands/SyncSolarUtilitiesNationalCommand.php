<?php

namespace App\Console\Commands;

use App\Modules\Solar\Models\EnergyUtility;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class SyncSolarUtilitiesNationalCommand extends Command
{
    private const ANEEL_SQL_ENDPOINT = 'https://dadosabertos.aneel.gov.br/api/3/action/datastore_search_sql';
    private const ANEEL_RESOURCE_ID = 'fd10c9d4-cb76-4020-a322-e79afb13eaf7';
    private const IBGE_MUNICIPALITIES_ENDPOINT = 'https://servicodados.ibge.gov.br/api/v1/localidades/municipios';
    private const CHUNK_SIZE = 1000;

    protected $signature = 'voltrune:sync-solar-utilities-national
        {--prune : Remove concessionarias que nao existem mais na base nacional}
        {--dry-run : So mostra contagens, sem gravar no banco}';

    protected $description = 'Sync Solar energy utilities for all Brazil using ANEEL + IBGE public data';

    public function handle(): int
    {
        $this->components->info('Sincronizando concessionarias nacionais (ANEEL + IBGE)...');

        try {
            $municipalityByCode = $this->fetchMunicipalityMap();
            $pairs = $this->fetchAneelUtilityCoveragePairs();
            $catalog = $this->buildCatalog($pairs, $municipalityByCode);
        } catch (Throwable $exception) {
            $this->components->error('Falha ao carregar dados publicos: '.$exception->getMessage());

            return self::FAILURE;
        }

        $totalUtilities = count($catalog);
        $totalCities = array_sum(array_map(
            static fn (array $utility): int => count($utility['cities_json']),
            $catalog
        ));
        $this->line(sprintf('Cobertura preparada: %d concessionarias, %d cidades.', $totalUtilities, $totalCities));

        if ((bool) $this->option('dry-run')) {
            $this->components->info('Dry-run concluido sem persistencia.');

            return self::SUCCESS;
        }

        $persistedIds = [];
        foreach ($catalog as $entry) {
            $utility = EnergyUtility::query()->updateOrCreate(
                [
                    'name' => $entry['name'],
                    'state' => $entry['state'],
                ],
                [
                    'cities_json' => $entry['cities_json'],
                ],
            );

            $persistedIds[] = $utility->id;
        }

        $removedCount = 0;
        if ((bool) $this->option('prune') && ! empty($persistedIds)) {
            $removedCount = (int) EnergyUtility::query()
                ->whereNotIn('id', $persistedIds)
                ->delete();
        }

        $this->components->info(sprintf(
            'Sincronizacao concluida: %d concessionarias atualizadas.%s',
            count($persistedIds),
            $removedCount > 0 ? sprintf(' %d registros antigos removidos.', $removedCount) : ''
        ));

        return self::SUCCESS;
    }

    /**
     * @return array<string, array{city: string, state: string}>
     */
    private function fetchMunicipalityMap(): array
    {
        $response = Http::acceptJson()
            ->timeout(60)
            ->retry(2, 400)
            ->get(self::IBGE_MUNICIPALITIES_ENDPOINT)
            ->throw()
            ->json();

        if (! is_array($response)) {
            throw new RuntimeException('Resposta invalida do IBGE para municipios.');
        }

        $map = [];

        foreach ($response as $record) {
            if (! is_array($record)) {
                continue;
            }

            $code = trim((string) ($record['id'] ?? ''));
            $city = trim((string) ($record['nome'] ?? ''));
            $state = strtoupper(trim((string) data_get(
                $record,
                'microrregiao.mesorregiao.UF.sigla',
                data_get($record, 'regiao-imediata.regiao-intermediaria.UF.sigla', '')
            )));

            if ($code === '' || $city === '' || $state === '') {
                continue;
            }

            $map[$code] = [
                'city' => $city,
                'state' => $state,
            ];
        }

        if (empty($map)) {
            throw new RuntimeException('Mapa de municipios IBGE retornou vazio.');
        }

        return $map;
    }

    /**
     * @return array<int, array{name: string, city_code: string}>
     */
    private function fetchAneelUtilityCoveragePairs(): array
    {
        $total = $this->countDistinctAneelPairs();
        $pairs = [];

        for ($offset = 0; $offset < $total; $offset += self::CHUNK_SIZE) {
            $query = sprintf(
                'SELECT DISTINCT "NomAgente", "SigAgente", "CodMunicipioIBGE" '.
                'FROM "%s" '.
                'WHERE "CodMunicipioIBGE" IS NOT NULL AND "CodMunicipioIBGE" <> \'\' '.
                'ORDER BY "NomAgente", "CodMunicipioIBGE" '.
                'LIMIT %d OFFSET %d',
                self::ANEEL_RESOURCE_ID,
                self::CHUNK_SIZE,
                $offset,
            );

            $records = $this->runAneelSqlQuery($query);

            foreach ($records as $record) {
                $cityCode = preg_replace('/\D+/', '', (string) ($record['CodMunicipioIBGE'] ?? ''));
                $name = $this->resolveUtilityDisplayName((string) ($record['SigAgente'] ?? ''), (string) ($record['NomAgente'] ?? ''));

                if ($cityCode === '' || $name === '') {
                    continue;
                }

                $pairs[] = [
                    'name' => $name,
                    'city_code' => $cityCode,
                ];
            }
        }

        if (empty($pairs)) {
            throw new RuntimeException('Base ANEEL nao retornou pares de cobertura.');
        }

        return $pairs;
    }

    private function countDistinctAneelPairs(): int
    {
        $query = sprintf(
            'SELECT COUNT(*) AS total '.
            'FROM ('.
            'SELECT DISTINCT "NomAgente", "SigAgente", "CodMunicipioIBGE" '.
            'FROM "%s" '.
            'WHERE "CodMunicipioIBGE" IS NOT NULL AND "CodMunicipioIBGE" <> \'\''.
            ') AS coverage',
            self::ANEEL_RESOURCE_ID,
        );

        $records = $this->runAneelSqlQuery($query);
        $total = (int) ($records[0]['total'] ?? 0);

        if ($total <= 0) {
            throw new RuntimeException('Base ANEEL retornou 0 registros de cobertura.');
        }

        return $total;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function runAneelSqlQuery(string $query): array
    {
        $response = Http::acceptJson()
            ->timeout(45)
            ->retry(2, 500)
            ->get(self::ANEEL_SQL_ENDPOINT, ['sql' => $query])
            ->throw()
            ->json();

        if (! is_array($response) || ($response['success'] ?? false) !== true) {
            throw new RuntimeException('Consulta ANEEL retornou formato inesperado.');
        }

        $records = data_get($response, 'result.records');

        if (! is_array($records)) {
            throw new RuntimeException('Consulta ANEEL sem registros validos.');
        }

        return $records;
    }

    /**
     * @param array<int, array{name: string, city_code: string}> $pairs
     * @param array<string, array{city: string, state: string}> $municipalityByCode
     * @return array<int, array{name: string, state: string, cities_json: array<int, string>}>
     */
    private function buildCatalog(array $pairs, array $municipalityByCode): array
    {
        $catalog = [];

        foreach ($pairs as $pair) {
            $municipality = $municipalityByCode[$pair['city_code']] ?? null;

            if ($municipality === null) {
                continue;
            }

            $state = strtoupper(trim($municipality['state']));
            $name = trim($pair['name']);
            $city = trim($municipality['city']);

            if ($state === '' || $name === '' || $city === '') {
                continue;
            }

            $key = $state.'|'.$this->normalizeKey($name);

            if (! isset($catalog[$key])) {
                $catalog[$key] = [
                    'name' => $name,
                    'state' => $state,
                    'cities' => [],
                ];
            }

            $catalog[$key]['cities'][$this->normalizeKey($city)] = $city;
        }

        $result = [];

        foreach ($catalog as $entry) {
            $cities = array_values($entry['cities']);
            sort($cities, SORT_NATURAL | SORT_FLAG_CASE);

            $result[] = [
                'name' => $entry['name'],
                'state' => $entry['state'],
                'cities_json' => $cities,
            ];
        }

        usort($result, static fn (array $a, array $b): int => [$a['state'], $a['name']] <=> [$b['state'], $b['name']]);

        return $result;
    }

    private function resolveUtilityDisplayName(string $shortName, string $officialName): string
    {
        $candidate = trim($shortName) !== '' ? $shortName : $officialName;
        $candidate = Str::of($candidate)->squish()->value();

        return trim($candidate);
    }

    private function normalizeKey(string $value): string
    {
        return Str::lower(trim(Str::ascii($value)));
    }
}
