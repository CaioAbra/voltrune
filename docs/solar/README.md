# Solar Module Documentation

## Purpose

This folder documents the current state of the Solar module inside Voltrune.

The goal of this documentation is to support:

- internal maintenance
- onboarding of new developers
- product alignment
- architectural review
- future case-study material

This documentation reflects what exists in code today. It is not a roadmap document.

## What The Solar Module Is

Solar is a Laravel module focused on commercial pre-budgeting for solar installers.

It already supports:

- customer registration
- project creation and editing
- CEP-assisted address input
- geocoding with latitude and longitude
- solar factor lookup through PVGIS
- automatic utility suggestion
- suggested system sizing
- suggested price generation
- financial simulation
- project read mode with commercial emphasis

The module is intentionally practical:

- first it enables fast commercial output
- then it allows manual adjustment
- it does not depend on a full equipment catalog to operate

## Core Design Principles

### 1. Fast first value

New installers must be able to generate a budget quickly, even without complete commercial setup.

Because of that, Solar uses layered fallbacks:

- company configuration when available
- market/regional defaults when company data is missing
- national fallback when regional data is unavailable

### 2. Automation with override

Solar suggests values automatically, but does not lock the user into them.

This is why most fields remain editable in the project form.

### 3. Commercial-first interface

The module is not only technical sizing software.

Its UI prioritizes:

- suggested power
- suggested price
- monthly savings
- estimated payback

Technical data remains visible, but with lower visual weight.

### 4. Safe degradation

External dependencies can fail.

The system is designed to keep working when:

- PVGIS is unavailable
- geocoding is slow or incomplete
- a company has no configured commercial defaults
- utilities are not manually configured yet

## Documentation Structure

- [Architecture](./architecture.md)
- [Data Model](./data-model.md)
- [Automation Flows](./automation-flows.md)
- [UI And UX Decisions](./ui-ux.md)
- [Operations And Commands](./operations.md)
- [Current State And Limitations](./current-state.md)

## Main Source References

- Routes: [routes/solar.php](/d:/projects/voltrune/routes/solar.php)
- Project controller: [ProjectController.php](/d:/projects/voltrune/app/Modules/Solar/Controllers/ProjectController.php)
- Sizing engine: [SolarSizingService.php](/d:/projects/voltrune/app/Modules/Solar/Services/SolarSizingService.php)
- Geocoding engine: [SolarGeocodingService.php](/d:/projects/voltrune/app/Modules/Solar/Services/SolarGeocodingService.php)
- Radiation engine: [SolarRadiationService.php](/d:/projects/voltrune/app/Modules/Solar/Services/SolarRadiationService.php)
- Utility resolution: [EnergyUtilityResolverService.php](/d:/projects/voltrune/app/Modules/Solar/Services/EnergyUtilityResolverService.php)
- Project form view: [form.blade.php](/d:/projects/voltrune/resources/views/solar/projects/partials/form.blade.php)
- Project show view: [show.blade.php](/d:/projects/voltrune/resources/views/solar/projects/show.blade.php)
- Frontend behavior: [app.js](/d:/projects/voltrune/resources/js/app.js)
- Styles: [_solar.scss](/d:/projects/voltrune/resources/scss/pages/_solar.scss)
