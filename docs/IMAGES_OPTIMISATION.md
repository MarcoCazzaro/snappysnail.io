# Image optimisation: controller path vs seeder path

**Date:** 2026-09-07
**Status:** investigation notes — decision pending (pick up tomorrow)

## TL;DR

The admin panel (controller) and the database seeder produce **different image
files for the same source**. That is why the Bertone Design images on
production (added via the admin panel) do not match the ones a local
`db:seed` produces.

- **Controller path:** stores the uploaded file essentially untouched. No
  resize, no re-encode, no real thumbnail. `optimised_at` stays `NULL`.
- **Seeder path:** does the same store **and then** runs `OptimiseImage`,
  which rescales the full image, builds a real 400×400 thumbnail, re-encodes
  both to WebP q90, and sets `optimised_at`.

`OptimiseImage` is dispatched from **exactly one place in the codebase**:
`database/seeders/SuggestionsSeeder.php`.

## How images are attached

Both paths call `HasImages::syncImages($request)`
(`app/Traits/HasImages.php`), reading two request fields:

| field | meaning |
|---|---|
| `modelImagesIds` | comma-separated ids of existing images to **keep** |
| `tempImagesPaths` | comma-separated real paths of newly uploaded temp files |

`syncImages()` deletes images not in `modelImagesIds`, then for each
`tempImagesPaths` entry calls `ImageOptimisation::generate()` and
`createMany()`s the resulting `Image` rows.

### `ImageOptimisation::generate()` (`app/Services/ImageOptimisation.php`)

```php
$image_file = new File($source_image);
$full_image_name = str_replace($image_file->extension(), 'webp', time().$image_file->hashName());
$full_path      = Storage::disk('public')->putFileAs('suggestions/images', $image_file, $full_image_name, 'public');
$thumbnail_name = str_replace('.webp', '_200_200.webp', $full_image_name);
$thumbnail_path = Storage::disk('public')->putFileAs('suggestions/images', $image_file, $thumbnail_name, 'public');
```

What it actually does:

- Swaps the extension **in the filename string** to `.webp`. The bytes are
  **not** converted — a JPEG/PNG ends up with a `.webp` name.
- `putFileAs()` copies the **raw source file twice** — once as
  `<hash>.webp`, once as `<hash>_200_200.webp`. The two files are
  **byte-identical**.
- **No resize. No re-encode. No real thumbnail.**

## The two paths

### Controller path — `SuggestionController::store()` / `update()`

1. `App\Livewire\ImagesUpload` collects uploads. Validation:
   `tempImages.* => image|mimes:png,jpg,jpeg|max:12288` (12 MB).
   Note: the `AtLeastFiveImages` rule (`app/Rules/AtLeastFiveImages.php`) has
   its body fully commented out — it is currently a **no-op**.
2. Controller: `Suggestion::create($request->all())` then
   `$suggestion->syncImages($request)`.
3. `syncImages()` → `ImageOptimisation::generate()` → raw copy ×2.
4. `createMany()` writes `Image` rows with `optimised_at = NULL`.
5. `Image` model `$dispatchesEvents['saved'] = ImageSaved::class` fires…
   **and nothing listens** (see below). `OptimiseImage` is **never** run.

**Result:** original file at native dimensions; `<hash>.webp` ==
`<hash>_200_200.webp`; `optimised_at` NULL; image URLs have **no**
`?optimised` suffix.

### Seeder path — `SuggestionsSeeder::attachImages()`

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
  come out ~1560 px.
- **thumbnail:**
  `read()->scaleDown(400)->scaleDown(400)->resizeCanvas(400, 400, 'transparent')->toWebp(90)->save()`
  → a real 400×400 image, letter-boxed onto a **transparent** canvas (RGBA).
- sets `optimised_at = now()`, `saveQuietly()`.

**Result:** full image upscaled + recompressed; thumbnail is a genuine
400×400 padded WebP; `optimised_at` set; URLs get `?optimised`
(`Image::getURLAttribute` / `getThumbnailURLAttribute`).

## Side-by-side

| | Controller (admin panel) | Seeder |
|---|---|---|
| `ImageOptimisation::generate()` (raw copy ×2) | yes | yes |
| `OptimiseImage` job | **never** | `dispatchSync` per image |
| full image | original bytes, native size | `scale(1920,1080)` → upscaled ~1560 px, WebP q90 |
| thumbnail (`_200_200`) | identical copy of full | real 400×400, transparent canvas, WebP q90 |
| `optimised_at` | `NULL` | set |
| URL suffix | none | `?optimised` |
| input format | PNG/JPG (stored with `.webp` name, original bytes) | seed sources are already WebP |

## Evidence (Bertone Design)

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
are **byte-identical** to the repo sources — native dimensions, `optimised_at`
never set. (One earlier 7th image was a 179×200 JPEG mislabelled `.webp`;
it has since been removed on production. We seed 6.)

**Local after `db:seed`:** e.g. image 2 — full becomes `1561x1080 / 73512 b`
(upscaled), thumb becomes `400x400 RGBA / 11928 b` (padded). Same story for
the others.

So: Bertone was added on production **through the admin controller**, which
skips `OptimiseImage`, so production has the untouched originals. Our seeder
re-processes every image.

## Events are not wired

`app/Models/Image.php`:

```php
protected $dispatchesEvents = [
    'saved'    => ImageSaved::class,
    'deleting' => ImageDeleting::class,
];
```

But `App\Events\ImageSaved` / `ImageDeleting` have **no listeners** — there is
no `app/Listeners/`, no `app/Observers/`, no `EventServiceProvider`, no
`Event::listen`. `CLAUDE.md` still claims "ImageSaved event dispatches
OptimiseImage job … ImageDeleting event dispatches DeletePhysicalImages job" —
that wiring **does not exist in the code**. `OptimiseImage` runs only from the
seeder; `DeletePhisicalImages` runs only from inside `syncImages()`.

## Open questions / things to decide tomorrow

1. **Align the seeder with the controller.** Simplest fix: drop the
   `OptimiseImage::dispatchSync()` loop from `SuggestionsSeeder`. The seed
   sources are already the production-optimised WebP files, so
   `generate()`'s raw copy is all the controller does anyway. Removes the
   upscaling and most of the ~12 s seed time.

2. **`scale(1920, 1080)` upscales.** In `OptimiseImage::handle()` this should
   almost certainly be `scaleDown(1920, 1080)` so images are never enlarged.
   Right now it makes local Bertone fulls blurrier than the originals.

3. **Nothing optimises admin-panel uploads.** New images added through the
   admin panel today are stored raw and never optimised (full == thumbnail,
   no real 400×400). The production images that *do* carry `?optimised` were
   optimised by some earlier one-off (tinker / queue / a listener that has
   since been removed). Decide whether the controller should:
   - re-wire `ImageSaved` → `OptimiseImage` (queued), or
   - call `OptimiseImage` explicitly in `syncImages()` like the seeder does, or
   - leave it and rely on a batch command.

4. **`ImageOptimisation::generate()` is misnamed** — it optimises nothing, it
   just copies the upload twice and renames the extension. If real
   optimisation moves into the controller path, `generate()` and
   `OptimiseImage` should probably merge.

5. **`AtLeastFiveImages` rule is a no-op** (body commented out). The
   `min 5 images` requirement described in `CLAUDE.md` is not enforced.

## Related

- `create_images_table` migration used to wipe
  `storage/app/public/suggestions/images` on every non-production migrate
  (including the test suite) via a relative path — fixed 2026-09-07 to skip
  during tests and use an absolute path. See
  `database/migrations/2025_01_07_141315_create_images_table.php`.
