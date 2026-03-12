# Hub Admin

## Purpose

Hub Admin is the internal operating console for the Voltrune team.

It exists to manage:

- company state
- product contracts
- product access
- billing records
- internal account behavior

Main references:

- [routes/hub.php](/d:/projects/voltrune/routes/hub.php)
- [CompanyAdminController.php](/d:/projects/voltrune/app/Http/Controllers/Hub/Admin/CompanyAdminController.php)
- [resources/views/hub/admin](/d:/projects/voltrune/resources/views/hub/admin)

## What Admin Can Do

Current responsibilities include:

- list companies
- filter by company status
- filter by billing status
- filter by product access
- inspect a company in detail
- update company status
- manage contract data per product
- manage access state per product
- store billing records

## Why This Layer Exists

The platform uses controlled activation and product gating.

Because of that, there must be an internal interface where the Voltrune team can control:

- who is active
- what each company bought
- what each company can actually access
- what financial state is currently registered

This is not a public feature.

It is operational infrastructure.

## Admin Philosophy

Hub Admin is intentionally pragmatic.

It is not a polished BI dashboard.

Its job is to make core internal actions reliable:

- approve
- suspend
- enable product
- register commercial state

That is why the current controller is direct and model-centered.
