# Image optimisation: refactor plan

**Date:** 2026-09-08
**Status:** implemented; three bugs found and fixed post-deploy (see §2), then
the root design that caused them was replaced (see §3)

## TL;DR

The admin panel (controller) and the database seeder used to produce **different
image files for the same source** (see §1, Investigation, below). The fix is a
single `ImageOptimisationContract` service, called synchronously from exactly one
place (`HasImages::syncImages()`), used by both the controller and the seeder.
No more "raw copy now, maybe re-optimise later via an unwired event/job" split.

This mirrors the pattern from a previous project
(`deboraantonello.com`'s `App\Services\ImageOptimisation` /
`ImageOptimisationContract` / deferred `ImageOptimisationServiceProvider`), adapted
to this app's polymorphic, multi-image-per-model `Image` model.

## Decisions — LOCKED (2026-09-08)

1. **Execution model:** ✅ **Fully synchronous.** The service resizes and
   re-encodes inline, in the same request/seeder call that creates the `Image`
   row. No job, no queue, no event. Suggestion uploads are small batches (a
   handful of images), so a couple of seconds of admin-panel latency is
   acceptable, and this removes the entire class of "event never wired up"
   bugs that caused the original mismatch.
2. **Thumbnail style:** ✅ **Cropped square, cover-fill.** `cover(400, 400)` —
   a true 400×400 square, no transparent-canvas letterboxing. Replaces the
   previous mismatch where the job produced a 400×400 padded image but the
   filename said `_200_200`.
3. **`optimised_at` column:** ✅ **Dropped.** Every `Image` row is now always
   optimised by the same code path at creation time, so the column (and the
   `?optimised` URL-suffix branching in `Image::getURLAttribute()` /
   `getThumbnailURLAttribute()`) is dead weight once nothing is ever
   "un-optimised."
4. **Legacy production images:** ✅ **Backfill command included.** Images
   uploaded through the admin panel before this refactor (native size,
   `file_path` == `thumbnail_file_path`) are reprocessed by a new Artisan
   command, `snappysnail:optimise-images`.
5. **Dimensions/quality targets:** unchanged from what the code already
   targeted — full image capped at 1920×1080 (never upscaled), thumbnail
   400×400, WebP quality 90.
6. **`AtLeastFiveImages` validation rule:** out of scope. It's a pre-existing
   no-op inherited from another project's copy-paste and doesn't interact with
   the optimisation pipeline — left untouched.

## New architecture

- **`App\Contracts\ImageOptimisationContract`** — one method,
  `generate(string $sourcePath): array{full: string, thumbnail: string}`,
  returning storage-relative paths.
- **`App\Services\ImageOptimisation`** implements it. Constructor-injects
  Intervention's `ImageManager` (already bound by `intervention/image-laravel`
  from `config/image.php`, so the app's configured driver is respected).
  `generate()` does the real work: `scaleDown(1920, 1080)` for the full image
  (never upscales), `cover(400, 400)` for the thumbnail, both re-encoded to
  WebP q90, written via `Storage::disk('public')` (not raw filesystem paths —
  keeps `Storage::fake('public')` hermetic in tests).
- **`App\Providers\ImageOptimisationServiceProvider`** (deferred) binds the
  contract to the service, registered in `bootstrap/providers.php`.
- **`HasImages::syncImages()`** resolves the contract and calls `generate()`
  directly — the *only* call site, shared by the controller
  (`SuggestionController::store()`/`update()`) and the seeder
  (`SuggestionsSeeder::attachImages()`).
- **Deleted:** `App\Jobs\OptimiseImage` (folded into the service),
  `App\Events\ImageSaved` / `App\Events\ImageDeleting` (unwired, no
  listeners — dead code independent of this refactor, but removed as part of
  it since they existed only to (not) trigger the job being removed).
- **Kept as-is:** `App\Jobs\DeletePhisicalImages` (async physical-file
  cleanup on delete) — unrelated to the mismatch, already correct.
- **`Image` model:** drops `optimised_at` from `$fillable` and the
  `$dispatchesEvents` block; URL accessors simplify to a plain
  `ImageOptimisation::getPublicUrl(...)` with no conditional suffix.
- **Migration:** new migration drops the `optimised_at` column (the original
  `create_images_table` migration is not edited further since it may already
  be applied in deployed environments).
- **`SuggestionsSeeder::attachImages()`:** drops its
  `OptimiseImage::dispatchSync()` loop — `syncImages()` now does the full
  optimisation itself, so seeded and admin-uploaded images are produced by
  identical code.
- **Backfill command** `snappysnail:optimise-images`: groups existing `Image`
  rows by shared physical `file_path`, re-runs `generate()` against each
  distinct file once, updates every row in that group, then deletes the old
  physical files. Skips a group already carrying the `_thumb.webp` filename
  suffix the pipeline produces, so reruns are cheap; `--force` bypasses the
  check. See §2 for two bugs found in this command's first version and fixed
  post-deploy — read it before relying on the description above as "this
  always worked." (The grouping this command does is now belt-and-braces
  rather than load-bearing — as of §3, a translation no longer has its own
  `Image` rows to group in the first place.)

---

## §1. Investigation (2026-09-07) — background for why this refactor exists

The admin panel (controller) and the database seeder produced **different image
files for the same source**. That is why the Bertone Design images on
production (added via the admin panel) did not match the ones a local
`db:seed` produced.

- **Controller path:** stored the uploaded file essentially untouched. No
  resize, no re-encode, no real thumbnail. `optimised_at` stayed `NULL`.
- **Seeder path:** did the same store **and then** ran `OptimiseImage`,
  which rescaled the full image, built a real 400×400 thumbnail, re-encoded
  both to WebP q90, and set `optimised_at`.

`OptimiseImage` was dispatched from **exactly one place in the codebase**:
`database/seeders/SuggestionsSeeder.php`.

### How images were attached

Both paths called `HasImages::syncImages($request)`
(`app/Traits/HasImages.php`), reading two request fields:

| field | meaning |
|---|---|
| `modelImagesIds` | comma-separated ids of existing images to **keep** |
| `tempImagesPaths` | comma-separated real paths of newly uploaded temp files |

`syncImages()` deleted images not in `modelImagesIds`, then for each
`tempImagesPaths` entry called `ImageOptimisation::generate()` and
`createMany()`'d the resulting `Image` rows.

#### `ImageOptimisation::generate()` (old version)

```php
$image_file = new File($source_image);
$full_image_name = str_replace($image_file->extension(), 'webp', time().$image_file->hashName());
$full_path      = Storage::disk('public')->putFileAs('suggestions/images', $image_file, $full_image_name, 'public');
$thumbnail_name = str_replace('.webp', '_200_200.webp', $full_image_name);
$thumbnail_path = Storage::disk('public')->putFileAs('suggestions/images', $image_file, $thumbnail_name, 'public');
```

What it actually did:

- Swapped the extension **in the filename string** to `.webp`. The bytes were
  **not** converted — a JPEG/PNG ended up with a `.webp` name.
- `putFileAs()` copied the **raw source file twice** — once as
  `<hash>.webp`, once as `<hash>_200_200.webp`. The two files were
  **byte-identical**.
- **No resize. No re-encode. No real thumbnail.**

### The two paths

#### Controller path — `SuggestionController::store()` / `update()`

1. `App\Livewire\ImagesUpload` collects uploads. Validation:
   `tempImages.* => image|mimes:png,jpg,jpeg|max:12288` (12 MB).
   Note: the `AtLeastFiveImages` rule (`app/Rules/AtLeastFiveImages.php`) has
   its body fully commented out — it is a **no-op** (out of scope, see
   Decisions §6 above).
2. Controller: `Suggestion::create($request->all())` then
   `$suggestion->syncImages($request)`.
3. `syncImages()` → `ImageOptimisation::generate()` → raw copy ×2.
4. `createMany()` wrote `Image` rows with `optimised_at = NULL`.
5. `Image` model `$dispatchesEvents['saved'] = ImageSaved::class` fired…
   **and nothing listened** (see below). `OptimiseImage` never ran.

**Result:** original file at native dimensions; `<hash>.webp` ==
`<hash>_200_200.webp`; `optimised_at` NULL; image URLs had **no**
`?optimised` suffix.

#### Seeder path — `SuggestionsSeeder::attachImages()`

Same `syncImages()` as above, **plus**:

```php
$suggestion->syncImages((object) ['tempImagesPaths' => $paths->implode(',')]);

foreach ($suggestion->images as $image) {
    OptimiseImage::dispatchSync($image);
}
```

`OptimiseImage::handle()` (`app/Jobs/OptimiseImage.php`):

- **full:** `InterventionImage::read()->scale(1920, 1080)->toWebp(90)->save()`
  `scale()` **upsizes** images smaller than the box — our ~1200 px sources
  came out ~1560 px.
- **thumbnail:**
  `read()->scaleDown(400)->scaleDown(400)->resizeCanvas(400, 400, 'transparent')->toWebp(90)->save()`
  → a real 400×400 image, letter-boxed onto a **transparent** canvas (RGBA).
- set `optimised_at = now()`, `saveQuietly()`.

**Result:** full image upscaled + recompressed; thumbnail was a genuine
400×400 padded WebP; `optimised_at` set; URLs got `?optimised`
(`Image::getURLAttribute` / `getThumbnailURLAttribute`).

### Side-by-side

| | Controller (admin panel) | Seeder |
|---|---|---|
| `ImageOptimisation::generate()` (raw copy ×2) | yes | yes |
| `OptimiseImage` job | **never** | `dispatchSync` per image |
| full image | original bytes, native size | `scale(1920,1080)` → upscaled ~1560 px, WebP q90 |
| thumbnail (`_200_200`) | identical copy of full | real 400×400, transparent canvas, WebP q90 |
| `optimised_at` | `NULL` | set |
| URL suffix | none | `?optimised` |
| input format | PNG/JPG (stored with `.webp` name, original bytes) | seed sources already WebP |

### Evidence (Bertone Design)

Repo seed sources `database/seeders/data/images/works/Bertone/` (already
production-optimised WebP):

```
01-bertone.webp  RGB 1080x1080   18440 b
02-bertone.webp  RGB 1200x830    35816 b
03-bertone.webp  RGB 1196x829    62764 b
04-bertone.webp  RGB 1201x830    41964 b
05-bertone.webp  RGB 1198x831    59972 b
06-bertone.webp  RGB 1197x831    10840 b
```

**Production** (`https://snappysnail.io/storage/...`): full and `_200_200`
were **byte-identical** to the repo sources — native dimensions, `optimised_at`
never set. (One earlier 7th image was a 179×200 JPEG mislabelled `.webp`;
it has since been removed on production. We seed 6.)

**Local after `db:seed`:** e.g. image 2 — full became `1561x1080 / 73512 b`
(upscaled), thumb became `400x400 RGBA / 11928 b` (padded). Same story for
the others.

So: Bertone was added on production **through the admin controller**, which
skipped `OptimiseImage`, so production had the untouched originals. The seeder
re-processed every image. This is exactly what "Legacy production images" in
Decisions §4 above accounts for.

### Events were not wired

`app/Models/Image.php` had:

```php
protected $dispatchesEvents = [
    'saved'    => ImageSaved::class,
    'deleting' => ImageDeleting::class,
];
```

But `App\Events\ImageSaved` / `ImageDeleting` had **no listeners** — there was
no `app/Listeners/`, no `app/Observers/`, no `EventServiceProvider`, no
`Event::listen`. Both events and the job are removed by this refactor (see
"New architecture" above) rather than wired up, since the synchronous
single-call-site design makes them unnecessary.

### Related

- `create_images_table` migration used to wipe
  `storage/app/public/suggestions/images` on every non-production migrate
  (including the test suite) via a relative path — fixed 2026-09-07 to skip
  during tests and use an absolute path. See
  `database/migrations/2025_01_07_141315_create_images_table.php`.

---

## §2. Post-deploy bugs (2026-09-08)

Bugs 1 and 2 surfaced running the backfill command against real production
data (`images` table dump); Bug 3 is the same root cause found by inspection
in a second code path. All three are fixed. Recorded here because 1 and 2 are
the kind of "worked in every test I wrote, broke on real data" bug worth
remembering, and all three share one lesson: an `Image` row's `file_path` is
not 1:1 with the row — translations share physical files by design
(`copyImagesFrom()`), so any code that mutates or deletes a file based on one
row has to account for every other row that might still point at it.

### Bug 1: dimensions-based "already done" check silently skipped everything

**First version** decided a row was already migrated by checking whether its
on-disk thumbnail was exactly 400×400 pixels (`getimagesize()`). Reasonable-
looking, wrong in practice: per §1, some production images had already been
resized to a real 400×400 **in place**, under the **old** `_200_200.webp`
filename, by a one-off script that predates this whole refactor and no longer
exists. That coincidence — right pixels, old filename — made the check treat
every one of those rows as "already optimised" and skip it.

**Symptom:** running the command reported success (`Reprocessed 0, skipped
19`) but the `images` table dump showed every row's `file_path` still on the
pre-refactor `_200_200` naming, and — the real tell — `updated_at` unchanged
for months. Nothing was ever written. The command didn't error; it just
quietly did nothing, which is why it looked like the command itself had
failed rather than "succeeded at skipping everything."

**Fix:** check the filename instead of pixel dimensions —
`str_ends_with($image->thumbnail_file_path, '_thumb.webp')`. Only the current
`ImageOptimisation::generate()` ever produces that suffix, so it can't
false-positive against old data the way "is it 400×400" can. See
`OptimiseExistingImages::alreadyMigrated()`.

### Bug 2: reprocessing one row deleted the file a sibling row still needed

**Symptom:** after fixing Bug 1 and re-running, English suggestions showed
correctly optimised images; Italian translations of the *same* suggestions
still showed old ones.

**Cause:** `Suggestion::copyImagesFrom()` — used when a translation is
created — gives the translation its own `Image` row, but that row's
`file_path`/`thumbnail_file_path` point at the **same physical file** as the
source's row (translations share files, they don't duplicate them). The
first version of the backfill command processed `Image` rows one at a time:
for each row it read the source file, generated new optimised files, updated
that one row, and **deleted the old physical file**. The English row (lower
id, inserted first) was processed first — succeeded, then deleted the shared
original. When the command reached the Italian row next, the file it needed
to read no longer existed, reprocessing threw, and that row was left
pointing at a path that had just been deleted out from under it — worse than
stale, actually broken.

**Fix:** group `Image` rows by `file_path` before processing. Each group is
reprocessed exactly once — one call to `generate()`, every row in the group
updated to the same new paths, and the old files deleted only after every
row referencing them has moved on. See
`OptimiseExistingImages::reprocessGroup()`. Covered by a regression test:
`tests/Feature/Console/OptimiseExistingImagesTest.php` — "reprocesses a file
shared by a translation and its source together, without breaking the second
row."

### Bug 3: the same "shared file" issue also existed in the live delete path

`HasImages::syncImages()` had the same root issue as Bug 2, in the *live*
admin-panel delete path rather than the backfill command: removing an image
from a suggestion deleted its physical file immediately, with no awareness
that a translation's `Image` row might still point at that same file.

**Fix (2026-09-08):** `syncImages()`'s `deletePhisicalFiles()` now queries for
any remaining `Image` row (across all suggestions) still referencing a given
`file_path`/`thumbnail_file_path` before queuing it for physical deletion —
only files nothing points to anymore actually get deleted. Also fixed, in the
same method, a pre-existing `array_merge()` bug that nested two path arrays
as two elements instead of flattening them (the "remove all images" branch of
`syncImages()`), which would have made `DeletePhisicalImages` try to delete an
array as if it were a file path. Covered by
`tests/Feature/HasImagesTest.php`.

---

## §3. Root design fix: a translation no longer owns its own images (2026-09-08)

Bugs 1–3 were all patches over the same underlying design flaw: a
translation's `Image` rows were **copies** of its source's rows
(`Suggestion::copyImagesFrom()`), independent database rows that happened to
start out pointing at the same `file_path`. Nothing kept them in sync after
that. Every one of the three bugs above was a different way for those two
copies to drift apart — reprocess one, the other goes stale; delete one, the
other's file disappears. Patching each symptom (grouping, reference-counting)
worked around the copies drifting; it didn't stop them from being copies.

**The actual fix: stop copying.** A translation now has **zero `Image` rows
of its own**. `Suggestion::images()` (and `latestImage()`/`oldestImage()`)
delegate to the translation's source when `translation_of` is set:

```php
public function images(): MorphMany
{
    return $this->translation_of !== null
        ? $this->translationSource->images()
        : $this->ownImages(); // HasImages::images(), aliased on import
}
```

One set of rows, one set of files, structurally impossible to desync because
there is nothing to desync — not "kept in sync by more careful code," just
not duplicated in the first place. `HasImages::copyImagesFrom()` is deleted;
every call site (`SuggestionController::translate()`, `::importTranslations()`,
`SuggestionsSeeder`) simply stops calling it — setting `translation_of` is now
sufficient for a translation to show its source's images, nothing else to do.

**Trade-off:** calling `$suggestion->images()->create(...)` on a translation
attaches the new image to its **source**, not the translation — this is the
intended behaviour (images are shared, not per-locale), but it means editing
images from a translation's admin page silently edits the source's image set.
Also, this relation does not support Eloquent eager loading
(`Suggestion::with('images')`) correctly across a mixed collection of sources
and translations — `addEagerConstraints()` would constrain by each model's
own id, not its delegated source's id. Nothing in the codebase currently does
this (every existing usage is a single suggestion's `->images` at a time,
checked before implementing), so it's a latent limitation, not a live bug —
worth remembering if a future feature wants to eager-load images across a
suggestion listing.

**Data cleanup:** migration
`2026_09_08_102105_delete_translation_owned_images.php` deletes every `Image`
row directly owned by a translation in the existing database — both the
(now redundant) correct copies and the ones broken by Bug 2. It's a metadata-
only delete (`DB::table('images')->delete()`), no physical files are touched:
every file those rows referenced is either still referenced by the source's
row, or was already gone (Bug 2's orphaned rows). This is what actually
resolves "all the Italian images are old" on production — after this
migration runs, a translation reads its source's (already correctly
optimised) `Image` rows live, every time.

Covered by `tests/Feature/SuggestionImageDelegationTest.php` (delegation
itself) and updated assertions in `SuggestionTranslateTest`,
`SuggestionTranslationBulkTest`, and `SuggestionsSeederTest` (now asserting
shared row identity, not just equal `file_path` values).
