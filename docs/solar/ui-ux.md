# UI And UX Decisions

## General Direction

The Solar UI is designed as a commercial SaaS interface with technical support data.

The order of importance is:

1. commercial outcome
2. project context
3. technical explanation

This priority is visible in both edit and show screens.

## Project Edit Screen

Main goal:

- help an installer build a pre-budget quickly

Why the edit screen is structured as a guided flow:

- users think in stages
- commercial work is easier when the interface follows that sequence

Current sequence:

1. system summary
2. client and location
3. energy consumption
4. suggested system
5. pre-budget
6. financial simulation
7. notes and status

References:

- [form.blade.php](/d:/projects/voltrune/resources/views/solar/projects/partials/form.blade.php)
- [app.js](/d:/projects/voltrune/resources/js/app.js)

## Project Show Screen

Main goal:

- provide an immediate commercial reading of a persisted project

Why the show screen was recently refined:

- the previous structure gave equal weight to too many cards
- the commercial outcome needed stronger hierarchy
- technical context needed separation from business result

Current reading order:

1. commercial hero
2. technical base
3. project context
4. technical system summary
5. pre-budget
6. financial simulation

Reference:

- [show.blade.php](/d:/projects/voltrune/resources/views/solar/projects/show.blade.php)

## Microinteractions

Current microinteraction strategy:

- lightweight number animation
- subtle hover on primary cards
- status feedback for automations
- small badges instead of large explanatory text when possible

Why:

- the product must feel responsive
- visual motion should reinforce meaning, not distract

## Visual Rules

### Stronger for commercial metrics

Large cards are used for:

- power
- price
- savings
- payback

Why:

- these are the values most likely to be discussed with the client

### Lighter for technical indicators

Technical signals stay visible, but visually quieter.

Why:

- they support credibility
- they should not compete with the sales message

### Progressive disclosure instead of text walls

Long helper texts were reduced where possible.

Why:

- heavy explanatory copy slows scanning
- installers need quick interpretation first

## UX Principle Behind The Module

The Solar module is not trying to be a dense engineering console.

It is trying to behave like:

- a commercial assistant for solar installers

That single principle explains many UI decisions.
