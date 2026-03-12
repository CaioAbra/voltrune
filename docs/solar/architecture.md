# Solar Architecture

## High-Level Structure

Solar lives inside the Laravel application as an isolated product module.

Main backend location:

- [app/Modules/Solar](/d:/projects/voltrune/app/Modules/Solar)

Main frontend locations:

- [resources/views/solar](/d:/projects/voltrune/resources/views/solar)
- [resources/scss/pages/_solar.scss](/d:/projects/voltrune/resources/scss/pages/_solar.scss)
- [resources/js/app.js](/d:/projects/voltrune/resources/js/app.js)

Database migrations:

- [database/migrations/solar](/d:/projects/voltrune/database/migrations/solar)

## Architectural Layers

### Routes

Solar routes are grouped under `/solar` and protected by:

- `auth`
- `company.active`
- `product:solar`

Reference:

- [routes/solar.php](/d:/projects/voltrune/routes/solar.php)

### Controllers

Controllers orchestrate views and persistence, but core business logic is intentionally pushed into services.

Main controllers:

- `SolarDashboardController`
- `CustomerController`
- `ProjectController`
- `SolarCompanySettingController`
- `SimulationController`
- `QuoteController`

The most important controller today is:

- [ProjectController.php](/d:/projects/voltrune/app/Modules/Solar/Controllers/ProjectController.php)

Why this matters:

- the project screen is the operational center of the module
- project creation/editing is where automation is triggered
- the show page is where the commercial reading is consolidated

### Models

Core models:

- `SolarCustomer`
- `SolarProject`
- `SolarSimulation`
- `SolarCompanySetting`
- `SolarMarketDefault`
- `EnergyUtility`
- `SolarQuote`
- `SolarQuoteItem`

Why these models exist:

- `SolarCustomer` stores client context
- `SolarProject` stores the installation context and commercial base
- `SolarSimulation` stores technical/commercial scenarios derived from a project
- `SolarCompanySetting` stores company-specific defaults
- `SolarMarketDefault` allows zero-config commercial fallback
- `EnergyUtility` supports location-aware utility selection
- `SolarQuote` and `SolarQuoteItem` prepare the module for proposal evolution from a simulation

### Services

The service layer concentrates the operational rules.

Main services:

- [SolarSizingService.php](/d:/projects/voltrune/app/Modules/Solar/Services/SolarSizingService.php)
- [SolarGeocodingService.php](/d:/projects/voltrune/app/Modules/Solar/Services/SolarGeocodingService.php)
- [SolarRadiationService.php](/d:/projects/voltrune/app/Modules/Solar/Services/SolarRadiationService.php)
- [EnergyUtilityResolverService.php](/d:/projects/voltrune/app/Modules/Solar/Services/EnergyUtilityResolverService.php)
- `SolarNavigationService`

This separation is intentional:

- controllers stay readable
- calculations stay testable
- UI changes do not require business logic rewrites

## Why The Architecture Was Kept This Way

The module evolved under product pressure:

- it had to become useful before becoming exhaustive
- it had to work for both new installers and advanced users
- it had to tolerate incomplete commercial setup

Because of that, the architecture favors:

- pragmatic services
- low-friction defaults
- incremental enhancement

It does not yet favor:

- deep DDD boundaries
- a full product catalog engine
- a quotation workflow with strict technical validation

## Functional Positioning

The module now follows this conceptual flow:

1. `SolarCustomer`
2. `SolarProject`
3. `SolarSimulation`
4. `SolarQuote`

Why this matters:

- project is no longer the final commercial object
- multiple scenarios can coexist for the same installation context
- quotes can evolve from a selected scenario instead of mutating the base project

Current compatibility strategy:

- `SolarProject` still keeps the persisted snapshot used by the existing flow
- `SolarSimulation` is synchronized from the project incrementally
- the first simulation acts as the default scenario during transition

That tradeoff was deliberate.

The current architecture is appropriate for:

- pre-budget
- qualification
- first commercial conversation

It is not yet a full ERP for solar operations.
