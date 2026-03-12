# Hub

## Purpose

Hub is the authenticated customer layer of Voltrune.

It is the system area responsible for:

- login
- registration
- password recovery
- account access
- company-aware dashboard access
- product gateway behavior

Main references:

- [routes/hub.php](/d:/projects/voltrune/routes/hub.php)
- [AuthController.php](/d:/projects/voltrune/app/Http/Controllers/Hub/AuthController.php)
- [HubController.php](/d:/projects/voltrune/app/Http/Controllers/Hub/HubController.php)
- [resources/views/hub](/d:/projects/voltrune/resources/views/hub)

## Login And Registration

### Login

What happens:

1. credentials are validated
2. authentication is attempted
3. the current company is resolved
4. admin users are redirected to Hub Admin
5. non-admin users are routed by company status

Why it works this way:

- Hub is company-centered
- product access only makes sense inside a company context

### Registration

What happens:

1. a user is created
2. a company is created in `pending` state
3. the user is attached as owner
4. the account is redirected to activation pending

Why it works this way:

- commercial activation is controlled
- account creation does not imply immediate product usage

This is important for SaaS operations where onboarding may depend on internal approval or commercial steps.

## Main Customer Area

Hub exposes:

- dashboard
- products
- account
- billing
- help
- activation pending

These are intentionally simple today.

Why:

- Hub is a navigation and entitlement layer first
- it does not try to absorb the operational complexity of each product module

## Product Access Model

Hub computes product access per company using:

- company active status
- product access records

Relevant model logic is surfaced through the Hub controller and related models.

This means the dashboard can behave as:

- a control point for enabled products

instead of a generic post-login page.

## Why Hub Exists As A Separate Layer

Without Hub, each product would need its own authentication and entitlement logic.

That would fragment:

- identity
- company lifecycle
- billing and commercial gating

Hub centralizes that responsibility.
