# Solar Data Model

## Main Entities

### SolarCustomer

Purpose:

- stores the customer identity and basic context

Why it exists:

- projects need a commercial owner
- the same customer may generate multiple projects

### SolarProject

Purpose:

- stores the pre-budget state end-to-end

Key groups of fields:

- customer and identity
- address and geolocation
- consumption and bill value
- technical sizing
- solar automation state
- commercial suggestion
- financial simulation context
- utility and project status

Important note:

`SolarProject` is the central aggregate of the module.

Why this is useful:

- all relevant context stays on one record
- the UI can render quickly from persisted state
- the project can be viewed as a commercial object, not only a technical one

### SolarCompanySetting

Purpose:

- stores company-specific defaults

Current fields of interest:

- default module power
- default inverter model
- price per kWp
- margin percent

Why it exists:

- advanced users need company-level behavior
- new users should still work without it

This is the reason for the three-level pricing priority:

1. company price
2. regional market reference
3. national fallback

### SolarMarketDefault

Purpose:

- stores market reference values by state and national fallback

Current use:

- price per kWp fallback
- average component references

Why it exists:

- Solar must operate without mandatory initial setup
- the first commercial experience cannot depend on supplier registration

### EnergyUtility

Purpose:

- stores utility coverage by state and city list

Why it exists:

- utility choice needs to make sense for the project location
- the select should not suggest utilities from unrelated states

Coverage source:

- ANEEL + IBGE public data sync

Reference command:

- [SyncSolarUtilitiesNationalCommand.php](/d:/projects/voltrune/app/Console/Commands/SyncSolarUtilitiesNationalCommand.php)

## Migration Evolution

The migration sequence shows the product evolution clearly.

Examples:

- base customer/project/quote tables
- address expansion
- CEP and geocoding support
- connection type
- sizing fields
- utility catalog
- company settings
- prepricing fields
- solar radiation fields
- geocoding precision
- market defaults

Reference:

- [database/migrations/solar](/d:/projects/voltrune/database/migrations/solar)

Why this matters:

- the module was not designed in one shot
- it evolved feature by feature
- documentation must preserve that context to explain current compromises

## Important Current Modeling Choices

### Address is stored both atomically and as a combined string

Atomic fields:

- street
- number
- complement
- district
- city
- state
- zip code

Combined field:

- `address`

Why:

- atomic fields support automation and geocoding
- combined address is useful for direct display and quick summaries

### Inverter and module data are not a real equipment catalog yet

Today the project stores:

- module power
- module quantity
- inverter model

Why this is still enough:

- the module is focused on commercial pre-budget
- it needs fast operation before catalog complexity

What this means:

- there is no supplier/SKU engine yet
- some system composition output is descriptive, not catalog-driven
