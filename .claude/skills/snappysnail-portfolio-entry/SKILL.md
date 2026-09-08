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

**Background: flatten, don't leave transparent.** Checked every existing `01-*-logo.webp`
across current image folders (gooruf.com, equos.it, mediakey.it, Bertone, forbes.it-2021) —
none carry an alpha channel; all are flat, opaque, single-color backgrounds
(`magick identify -format "%[channels] alpha=%A"` reports `srgb ... alpha=Undefined` on every
one). The background color is picked per-project to match that project's own brand/header
tone (e.g. forbes.it-2021 is pure black, equos.it is its brand blue), not a uniform white.
So: if the source logo is SVG/transparent-PNG, sample the target site's actual dark/brand
background color from a screenshot (`magick <screenshot> -format "%[pixel:p{X,Y}]" info:` on
a header/nav pixel) and flatten onto it. Don't ship a transparent logo PNG.

**Square canvas, logo contained with margin — never ship the raw aspect ratio.** Same check
as above: every existing `01-*-logo.webp` is a perfect square (768×768, 768×768, 800×800,
1080×1080, 700×700) — never the logo's native (often wide, wordmark-shaped) aspect ratio. This
matters mechanically, not just cosmetically: `ImageOptimisation::generate()`
(`app/Services/ImageOptimisation.php`) makes the gallery thumbnail with `cover(400, 400)` —
Intervention's `cover()` always **center-crops**, no gravity option. Feed it a wide banner
logo and the thumb crops off most of the wordmark; feed it an already-square, padded image and
`cover()` has nothing to crop — the whole logo shows, in both the 400×400 thumb and the
`scaleDown(1920,1080)` full/lightbox view. So always compose a square canvas yourself: contain
the logo within an inner box sized so it keeps **at least a 20% margin on every side**, centered,
flattened onto the sampled brand background color, alpha stripped. One command chain:
```bash
magick -background none -density 300 logo.svg -trim +repage \
  -resize 648x648 \
  -background "rgb(R,G,B)" -gravity center -extent 1080x1080 \
  -alpha remove -alpha off \
  01-<slug>-logo.png
```
(`648 = 1080 * 0.6`, i.e. the inner box after a 20%-per-side margin; `-resize WxH` without `!`
is a contain-fit, so it only shrinks the constraining dimension — a wide logo ends up with much
bigger top/bottom margins than 20%, which is correct: 20% is a *minimum* guarantee on the
tighter axis, not an exact margin on every axis.) `-trim +repage` first removes any incidental
whitespace baked into the source SVG's own canvas, so the margin is measured against the actual
visual logo, not an arbitrary viewBox.

**If you reseed a logo/image after the suggestion was already seeded once**: `attachImages()`
in `SuggestionsSeeder` skips the whole image-sync step when `$suggestion->images()->exists()`
is true — it's all-or-nothing per suggestion, not a per-file diff. To pick up a corrected file,
delete the suggestion's existing `Image` rows first so the sync runs again:
```bash
ddev artisan tinker --execute '$s = App\Models\Suggestion::find(<id>); foreach ($s->images as $img) { $img->delete(); }'
```
(delete via Eloquent, not raw SQL, so the `ImageDeleting` event cleans up the physical files
too) then re-run `ddev artisan db:seed --class=SuggestionsSeeder`.

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

**Never capture full-page (`--full-page`) screenshots — always plain viewport shots.**
`ImageOptimisation::generate()` (`app/Services/ImageOptimisation.php`) derives *both* the
`full` and `thumbnail` variant from the same source image: `full` is `scaleDown(1920, 1080)`
(shrinks to fit, never crops — a tall full-page image gets squashed into an illegible strip),
`thumbnail` is `cover(400, 400)` (always a **center crop**, no gravity option). A full-page
screenshot's thumb would show whatever happened to be in the vertical middle of the page, not
the top/hero. There's no per-image way around this without changing that shared, site-wide
service — out of scope for this skill. If the user wants more of a homepage shown than one
viewport, take **additional separate viewport screenshots of further-down sections** instead —
each one gets its own representative thumb and a crisp, undistorted full view in the
gallery/lightbox. To pick which sections: check the homepage's **main navigation** — if its
links are in-page anchors (`#come-funziona`, `#pricing`, etc., rather than separate routes),
those anchors *are* the site's own answer to "what are this page's notable sections" — grep the
nav component/markup for `href="#..."` or matching `id="..."` section attributes, then capture
one viewport shot per anchor (`<url>#anchor-name`, scroll the element into view, screenshot).
Only fall back to asking the user when the nav doesn't expose anchors this way.

Every one of these viewport captures should also **dismiss the site's cookie-consent banner
first** (if any) — otherwise it's stuck open in the corner of every screenshot. `capture.mjs`
does not do this itself (it's the shared, minimal script); do it inline, best-effort, before
the screenshot in whatever script/invocation you're using:
```js
try {
    await page.getByText('Accetta tutti', { exact: true }).click({ timeout: 3000 }); // adjust label per site's language/copy
    await page.waitForTimeout(500); // let the fade-out finish before the screenshot
} catch {}
```

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
