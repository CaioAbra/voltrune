# Voltrune Documentation

## Purpose

This `docs/` directory documents the current Voltrune system as it exists in code today.

It was organized to support:

- technical onboarding
- maintenance
- architectural review
- internal alignment
- future case-study preparation

The documentation is split by product/domain so each part of the platform can be studied independently.

## Documentation Map

- [System Overview](./system-overview.md)
- [Public Site](./public-site/README.md)
- [Hub](./hub/README.md)
- [Hub Admin](./hub-admin/README.md)
- [Shared Platform](./shared/README.md)
- [Solar Module](./solar/README.md)

## Why This Structure Exists

Voltrune is not just a single product page or a single module.

Today it is composed of:

- a public-facing marketing site
- a Hub with authentication and customer area
- an admin backoffice
- product modules, with Solar being the most evolved one

Because of that, documenting everything in one flat file would make the material harder to maintain and harder to use for study purposes.

This folder is intentionally separated by area.
