# Public Site

## Purpose

The public site is the acquisition and positioning layer of Voltrune.

It exists to:

- present services and products
- communicate brand direction
- drive contact and lead generation
- route interested users into Hub or sales conversations

## Main Route Surface

References:

- [routes/web.php](/d:/projects/voltrune/routes/web.php)
- [resources/views/pages](/d:/projects/voltrune/resources/views/pages)

Main public pages:

- home
- services
- portfolio
- systems
- solar landing page
- contact
- vigilante landing
- portal redirect layer

## Lead Capture

Lead-oriented flows currently include:

- contact form
- contact prefill
- vigilante lead submission

Relevant controllers:

- [ContactController.php](/d:/projects/voltrune/app/Http/Controllers/ContactController.php)
- [VigilanteLeadController.php](/d:/projects/voltrune/app/Http/Controllers/VigilanteLeadController.php)

## Why The Public Site Matters To The Platform

The public site is not isolated marketing fluff.

It acts as the top of the platform funnel.

Its job is to:

- create demand
- establish premium perception
- make product entry points discoverable

This is why it sits in the same codebase:

- brand, acquisition and product narrative remain aligned

## Key Design Logic

The public site uses a strong branded presentation because it is responsible for:

- first trust
- first positioning
- first conversion intent

That role is different from Hub and Solar, which are operational interfaces.
