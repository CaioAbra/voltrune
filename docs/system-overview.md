# System Overview

## What Voltrune Is

Voltrune is a Laravel-based platform with multiple layers:

- public site for acquisition and positioning
- Hub for account access and customer navigation
- Hub Admin for internal operations
- product modules under gated access

The most developed product module today is Solar.

## High-Level Domains

### 1. Public Site

Purpose:

- present the company
- capture interest
- generate leads
- communicate service/product value

Main route source:

- [routes/web.php](/d:/projects/voltrune/routes/web.php)

### 2. Hub

Purpose:

- authenticate users
- register companies
- expose customer dashboard and account area
- act as the access gateway to products

Main route source:

- [routes/hub.php](/d:/projects/voltrune/routes/hub.php)

### 3. Hub Admin

Purpose:

- manage companies
- manage product access
- manage contracts
- manage billing state

This is the internal operating console for Voltrune staff.

### 4. Solar

Purpose:

- enable solar installers to create commercial pre-budgets fast

Main route source:

- [routes/solar.php](/d:/projects/voltrune/routes/solar.php)

## Runtime Model

At runtime, the system behaves like this:

1. a user reaches the public site
2. the user registers or logs in through Hub
3. the user is associated with a company
4. access is checked against company status and product entitlement
5. enabled modules become accessible

This access chain is one of the most important architectural choices in the platform.

It allows Voltrune to behave like a multi-product SaaS without requiring a separate app per product.

## Core Platform Idea

The current platform is built around:

- company-centered access
- product gating
- gradual module maturity

This means:

- users do not just log into a generic dashboard
- they log into a company context
- products are enabled according to commercial state

That architecture is especially visible in Hub and Solar.
