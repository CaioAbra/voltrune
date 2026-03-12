# Current State And Limitations

## What Is Mature Enough Today

Solar is already strong in:

- pre-budget generation
- first commercial conversation support
- location-assisted automation
- regional solar factor usage
- market fallback pricing
- guided project editing
- commercial reading of saved projects
- simulation snapshots linked to projects
- simulation-level composition and cost visibility

## Current Limitations

### 1. No real equipment catalog yet

Today the module does not manage:

- inverter brands as structured data
- module brands as structured data
- supplier stock
- SKU-level costs

Why:

- product focus has been on commercial velocity, not procurement depth

Current workaround:

- company defaults
- descriptive composition
- estimated kit breakdown
- simulation-level persisted cost groups and gross profit

### 2. External dependency sensitivity

Solar still depends on public services for:

- CEP enrichment
- geocoding
- PVGIS factor

What has already been done:

- caching
- fallback factor
- persisted factor reuse
- safe degradation

What this means:

- the system remains usable
- but real-time automation can still be affected by third-party latency

### 3. Utility data is strong but not magical

The utility catalog now covers Brazil using public sources, but it still depends on:

- source data consistency
- correct city and state inference

Why this matters:

- location mistakes can create wrong utility suggestions
- UX reliability depends on clean location data

### 4. Financial simulation is intentionally simple

Current simulation does not model:

- tariff inflation
- financing
- tax complexity
- advanced payback scenarios

Why:

- the current goal is a clear and fast first commercial estimate

### 5. Transition between project and simulation is incremental

Today:

- project still persists much of the calculated snapshot
- simulation is synchronized from project as a real entity
- quote is prepared to point to simulation, but the full proposal workflow is not complete yet

Why:

- the module needs continuity while the domain model matures
- this avoids breaking current project editing and show flows

## Why These Tradeoffs Were Accepted

The current product strategy values:

- fast usefulness
- commercial clarity
- operational simplicity

over:

- deep engineering detail
- procurement-grade catalog management
- highly complex financial modeling

This is the correct tradeoff for the current stage of the module.

## Recommended Future Evolution

When the product needs the next maturity step, the most coherent sequence is:

1. structured equipment catalog by company
2. supplier and cost source integration
3. quote workflow fully driven by simulation
4. richer financial models
5. engineering validation depth

Why this order:

- it preserves the current commercial-first identity
- it adds depth without destroying usability
