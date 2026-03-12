# Shared Platform

## Purpose

This section documents the cross-cutting layers that support the full platform.

These parts are not tied to a single module.

They exist to support:

- authentication routing
- company-aware access
- admin access
- command execution
- shared domain models

## Main Shared Models

References:

- [Company.php](/d:/projects/voltrune/app/Models/Company.php)
- [User.php](/d:/projects/voltrune/app/Models/User.php)
- [CompanyContract.php](/d:/projects/voltrune/app/Models/CompanyContract.php)
- [CompanyProductAccess.php](/d:/projects/voltrune/app/Models/CompanyProductAccess.php)
- [CompanyBillingRecord.php](/d:/projects/voltrune/app/Models/CompanyBillingRecord.php)

Why these models matter:

- they are the backbone of Hub and product gating
- they define who the customer is in platform terms
- they define what the customer can access

## Shared Middleware

Main middleware:

- [EnsureCompanyIsActive.php](/d:/projects/voltrune/app/Http/Middleware/EnsureCompanyIsActive.php)
- [EnsureProductAccessIsActive.php](/d:/projects/voltrune/app/Http/Middleware/EnsureProductAccessIsActive.php)
- [EnsureHubAdmin.php](/d:/projects/voltrune/app/Http/Middleware/EnsureHubAdmin.php)

Why they exist:

- authentication alone is not enough
- access depends on company state and product entitlement
- admin access must be explicitly separated

These middleware classes are central to platform behavior.

## Shared Commands

Current commands include:

- Hub migrations
- Solar migrations
- Hub admin seeding
- Solar utility sync

Why commands are important in this project:

- the platform uses multiple logical areas
- migration and operational setup are product-aware
- some system capabilities depend on scheduled or manual sync processes

## Shared Architectural Principle

Voltrune is structured around:

- one identity layer
- one company layer
- multiple product layers

The shared platform code is what keeps those layers coherent.
