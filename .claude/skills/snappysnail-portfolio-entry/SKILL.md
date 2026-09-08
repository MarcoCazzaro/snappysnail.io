---
name: snappysnail-portfolio-entry
description: "Generates a new portfolio ('Suggestion') entry for snappysnail.io from a local project's CLAUDE.md, including EN/IT copy and screenshots. Activates when the user says something like 'generate the portfolio entry for the project at <path>' or asks to add a project/work to the portfolio."
metadata:
  author: snappysnail
---

# Snappysnail Portfolio Entry Generator

Adds one new project to the portfolio: an entry in `database/seeders/data/suggestions.json`
(EN + IT translations of one `Suggestion`) plus its images in
`database/seeders/data/images/works/{folder}/`. This is the same data the
`SuggestionsSeeder` upserts idempotently — see `app/Models/Suggestion.php` and
`database/seeders/SuggestionsSeeder.php` for how it's consumed.

Only ever invoked from inside this repo (snappysnail.io), for a project the user names by
local path.

## Inputs needed

- **Target project path** — given in the trigger message.
- **Homepage URL** — a local DDEV URL (`https://<project>.ddev.site`) is needed for
  screenshots; a production URL is needed for the `url` field. If the target project isn't
  running under DDEV, ask the user how to reach its homepage locally.
- **A couple of extra page URLs** — the user said they'll supply these. **Ask if not given**,
  don't guess pages to screenshot.
- **Project "as of" year-month** — used for `sorting` (see below). Try to infer it first
  (see step 3); confirm with the user rather than silently picking a value, since it controls
  where the entry sorts on the live site.

## Procedure

### 1. Read the target project

Read `CLAUDE.md` (and `AGENTS.md` if present) at the target path for: what the project is,
who it's for, the tech stack, and anything notable about the build. Fall back to
`README.md`, `composer.json`/`package.json` (name, description) if `CLAUDE.md` is thin or
missing. If there's truly nothing to go on, ask the user for a one-line description.

### 2. Pick `key` and image folder name

- `key`: kebab-case slug, letters/numbers/hyphens only (e.g. `forbes-2021-2022`,
  `gooruf-com`). Check `database/seeders/data/suggestions.json` for collisions.
- Image folder: existing folders mirror the site's natural name and are **not** all
  kebab-case (`gooruf.com`, `forbes.it-2021`, `camping europa`, `Snappysnail`) — match that
  loosely, prefer the domain as-is when there is one.

### 3. Determine `sorting`

Convention confirmed from existing data: `sorting` is `YYYYMM` (e.g. `202501` = Jan 2025),
and the global scope orders **DESC**, so higher sorts first. Non-portfolio pages
(contact/services/curriculum) use `0`. `this-website`'s own entry deliberately uses a low
value (`200000`) to not hog the top slot.

Infer a default with `git -C <target-path> log --diff-filter=A --format=%cd --date=format:%Y%m -- . | tail -1` (first commit) or the most recent commit date if the project is ongoing —
then confirm the year-month with the user before writing it.

### 4. Logo

Look in the target project's `public/` for `logo.*`, `favicon.svg`, `apple-touch-icon*`, etc.
(svg > png; a file literally named "logo" over a generic favicon). Show the user which file
you found and get confirmation before using it — don't use it silently.

### 5. Make sure the target site is reachable

Check `ddev list`. If the target project isn't `running`/`OK`, ask the user to start it
(`ddev start` in that project's directory) — don't start another project's containers
without asking.

### 6. Capture screenshots

Run via THIS project's DDEV container (Node + Playwright + Chromium live there — see
`scripts/capture.mjs`). It reaches other running DDEV projects over the shared
`ddev_default` docker network, so `https://<target>.ddev.site` resolves fine from inside.

```bash
ddev exec -- bash -c 'PLAYWRIGHT_BROWSERS_PATH=/mnt/ddev-global-cache/playwright node .claude/skills/snappysnail-portfolio-entry/scripts/capture.mjs <url> <outfile> <width> <height>'
```

- Logo: if it's a raster favicon/logo file, just copy it — no screenshot needed.
- Homepage desktop: 1440×900.
- Homepage mobile: 390×844.
- Each extra page: 1440×900 (desktop only — confirmed with the user, no mobile shot for
  these).

Save outputs into `database/seeders/data/images/works/{folder}/`, following the existing
numeric-prefix naming convention seen in current folders (`01-<slug>-logo.webp`,
`02-<slug>-home-desktop`, `03-<slug>-home-mobile`, `04-<slug>-<page-name>`, ...) — the
seeder attaches images in filename order, so the prefix controls display order. PNG output
from the script is fine; `SuggestionsSeeder::attachImages()` runs everything through the
optimisation pipeline (→ WebP) on seed, no need to convert manually.

**Troubleshooting**: if capture fails with `error while loading shared libraries:
libnspr4.so` (or similar), the container's OS deps for Chromium were lost on a rebuild — fix
with `ddev exec -- sudo npx playwright install-deps chromium`, then retry. If the Chromium
binary itself is missing, reinstall with `ddev exec -- npm run playwright:install`.

### 7. Draft EN + IT copy

Match the exact tone/shape of existing entries (read a few from `suggestions.json` for
calibration, e.g. `forbes-2021-2022`, `gooruf-com`):

- `title`: short, e.g. project/site name, optionally with a year range in parens.
- `keywords`: comma-separated, starts with `works,portfolio,<image-folder-name>,` then the
  tech stack. IT uses `lavori,portfolio,...` instead of `works,portfolio,...`.
- `description`: **one** `<p>` element, first person, past tense, names the year and stack,
  links out to referenced tools/tech with `target="_blank" rel="noopener noreferrer"`. Keep
  it to 1–3 sentences, matching the brevity of existing entries.

### 8. Write the entry

Append one object to the `suggestions.json` array (keep existing formatting/indentation):

```json
{
    "key": "...",
    "sorting": 202xxx,
    "url": "https://production-url",
    "images": "folder-name",
    "translations": {
        "en": { "title": "...", "keywords": "...", "description": "<p>...</p>" },
        "it": { "title": "...", "keywords": "...", "description": "<p>...</p>" }
    }
}
```

### 9. Stop for review — do not seed automatically

Report what was written/created (JSON entry, image files) and hand the user the exact
command to run once they've reviewed it:

```bash
ddev artisan db:seed --class=SuggestionsSeeder
```

It's idempotent (upsert by `key`+`locale`) and skips image attachment if the suggestion
already has images, so re-running after tweaks is safe.
