# Operations And Commands

## Main Runtime Dependencies

Solar currently depends on:

- Laravel application runtime
- `solar_mysql` connection
- ViaCEP for CEP enrichment
- Nominatim/OpenStreetMap for geocoding
- PVGIS for solar factor
- ANEEL + IBGE public data for utility coverage sync

## Important Commands

### Run Solar-only migrations

Command:

`php artisan voltrune:migrate-solar`

Why:

- keeps Solar schema changes isolated from the rest of the platform

Reference:

- [MigrateSolarCommand.php](/d:/projects/voltrune/app/Console/Commands/MigrateSolarCommand.php)

### Seed or sync utilities

Command:

`php artisan voltrune:seed-solar-utilities`

Behavior:

- tries national sync first
- falls back to local seed if needed

Why:

- improves resilience during setup

Reference:

- [SeedSolarUtilitiesCommand.php](/d:/projects/voltrune/app/Console/Commands/SeedSolarUtilitiesCommand.php)

### National utility sync

Command:

`php artisan voltrune:sync-solar-utilities-national --prune`

Why:

- keeps the utility catalog aligned with public data
- reduces state/city mismatches in the project form

Reference:

- [SyncSolarUtilitiesNationalCommand.php](/d:/projects/voltrune/app/Console/Commands/SyncSolarUtilitiesNationalCommand.php)

### Scheduled utility refresh

Defined in:

- [routes/console.php](/d:/projects/voltrune/routes/console.php)

Current behavior:

- daily sync at `03:30`

Why:

- keeps national coverage fresh without manual intervention

## Tests That Protect Critical Logic

Main unit tests:

- [SolarSizingServiceTest.php](/d:/projects/voltrune/tests/Unit/SolarSizingServiceTest.php)
- [SolarGeocodingServiceTest.php](/d:/projects/voltrune/tests/Unit/SolarGeocodingServiceTest.php)
- [SolarRadiationServiceTest.php](/d:/projects/voltrune/tests/Unit/SolarRadiationServiceTest.php)
- [EnergyUtilityResolverServiceTest.php](/d:/projects/voltrune/tests/Unit/EnergyUtilityResolverServiceTest.php)
- [ProjectControllerLocationPreparationTest.php](/d:/projects/voltrune/tests/Unit/ProjectControllerLocationPreparationTest.php)

Why these tests matter:

- sizing is the commercial core
- geocoding influences radiation quality
- radiation influences system sizing credibility
- utility resolution affects trust in location coherence

## Safe Maintenance Guidance

When changing Solar, the safest order of attention is:

1. services
2. project controller contract
3. form/show views
4. frontend interactions
5. styling

Why:

- styling regressions are visible
- service regressions change business outcome
- controller regressions can silently break persistence
