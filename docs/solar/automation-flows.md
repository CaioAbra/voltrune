# Automation Flows

## Overview

Solar automation is concentrated around one idea:

- reduce manual work without removing user control

The project form is where these automations converge.

Main references:

- [ProjectController.php](/d:/projects/voltrune/app/Modules/Solar/Controllers/ProjectController.php)
- [form.blade.php](/d:/projects/voltrune/resources/views/solar/projects/partials/form.blade.php)
- [app.js](/d:/projects/voltrune/resources/js/app.js)

## Flow 1: CEP And Address Autofill

### What happens

1. user fills CEP
2. frontend calls ViaCEP
3. street, district, city and state are filled
4. form emits follow-up events
5. downstream automations react

### Why it exists

- reduce input friction
- improve first-use experience
- prepare location context early

### Product reason

Installers do not want to type the whole address to start a pre-budget.

## Flow 2: Geocoding

### What happens

1. project data is normalized
2. the system checks if coordinates should be refreshed
3. geocoding is attempted with best available precision
4. coordinates and precision are persisted

Reference:

- [SolarGeocodingService.php](/d:/projects/voltrune/app/Modules/Solar/Services/SolarGeocodingService.php)

Precision strategy:

- refined address when street + number + city + state exist
- city approximation when address is incomplete
- fallback when location cannot be resolved

### Why it exists

- PVGIS needs latitude and longitude
- commercial confidence improves when location precision improves

### Product reason

The installer should get a useful result even before the full address is known.

That is why the system supports precision upgrades over time.

## Flow 3: Solar Factor Through PVGIS

### What happens

1. if the project already has a valid persisted factor, it is reused
2. if coordinates exist and factor is missing, PVGIS is queried
3. result is cached and attached to the project
4. if PVGIS fails, the system falls back safely

Reference:

- [SolarRadiationService.php](/d:/projects/voltrune/app/Modules/Solar/Services/SolarRadiationService.php)

### Why it exists

- regional solar factor is a major quality improvement over a fixed default
- persisted values avoid unnecessary external calls

### Product reason

The system should feel smart, but not brittle.

If PVGIS fails, Solar must still generate a pre-budget.

## Flow 4: Utility Suggestion

### What happens

1. location is inferred from CEP or manual address data
2. utility compatibility is checked by city and state
3. incompatible selections are invalidated
4. the most coherent utility is suggested

References:

- [EnergyUtilityResolverService.php](/d:/projects/voltrune/app/Modules/Solar/Services/EnergyUtilityResolverService.php)
- [SyncSolarUtilitiesNationalCommand.php](/d:/projects/voltrune/app/Console/Commands/SyncSolarUtilitiesNationalCommand.php)

### Why it exists

- utilities vary by region
- showing utilities from other states breaks user trust

### Product reason

Commercial software must look location-aware.

Even when the utility is editable, the suggestion must already make sense.

## Flow 5: Suggested Sizing

### What happens

The sizing service estimates:

- required power
- module quantity
- generation
- suggested price
- area
- payback
- ROI

Reference:

- [SolarSizingService.php](/d:/projects/voltrune/app/Modules/Solar/Services/SolarSizingService.php)

### Why it exists

- the project screen must generate a viable first solution quickly
- the installer needs a commercial starting point before a full engineering phase

### Product reason

This is not final engineering design.

It is a sales acceleration engine.

## Flow 6: Automation Preview Endpoint

Route:

- `/solar/projects/automation-preview`

Purpose:

- update UI feedback live during editing
- avoid full page refresh
- preserve backend validation and service logic

Why it exists:

- the project form needs perceived real-time behavior
- business rules should remain server-backed

This endpoint is one of the key bridges between UX and backend safety.
