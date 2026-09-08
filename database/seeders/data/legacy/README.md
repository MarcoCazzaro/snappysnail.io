# Legacy seed data (pre‑2026‑09‑07)

These files are the **old** suggestion seed data and were retired on
**2026‑09‑07**. They are kept for reference only. Nothing in the app reads
them anymore.

The live seed data is now a single file: **`../suggestions.json`**.

---

## What changed

### Before

Seeding was English‑only and split across two hand‑maintained files:

| File | Role |
|------|------|
| `suggestions.json` | 23 English suggestions (`title`, `keywords`, `description`, `locale`, `sorting`, `url`) |
| `translated-suggestions-it.json` | 23 Italian translations, each linked to its English source by a hard database id (`source_id`) |
| `untranslated-suggestions.json` | last production export of English rows lacking an Italian counterpart |

Problems:

- `SuggestionsSeeder` only loaded `suggestions.json`, so **`/it/portfolio` was
  empty after a fresh seed**. Italian rows could only be added by uploading
  `translated-suggestions-it.json` through the admin panel.
- The Italian file keyed each translation to a production `source_id`. Those
  ids don't match a freshly‑seeded local database, so an upload landed
  translations on the wrong rows or skipped them.
- `SuggestionsSeeder` used `firstOrCreate(['title' => …])`. It never updated a
  row whose content had changed in the JSON, and it would have collided EN/IT
  rows that share a title (most of them — brand names).
- Images were attached only when a row was first created.

### After

- **One file, all locales.** `../suggestions.json` holds an array of
  suggestion *groups*. Shared fields (`sorting`, `url`, `images`) sit once at
  the top; per‑locale fields nest under `translations.<locale>`:

  ```json
  {
      "key": "bertone-design",
      "sorting": 202501,
      "url": "https://bertonedesign.it/",
      "images": "Bertone",
      "translations": {
          "en": { "title": "…", "keywords": "…", "description": "…" },
          "it": { "title": "…", "keywords": "…", "description": "…" }
      }
  }
  ```

  `images` is the folder name under `../images/works/`, or `null`.
  A `description` of `"views.some.blade.view"` is rendered from that view;
  any other string is stored verbatim.

- **Stable `key` slug.** Migration
  `2026_09_07_155541_add_key_to_suggestions_table` adds a `key` column with a
  `unique(key, locale)` index and backfills existing rows from a title→slug
  map. `key` is the shared identity across an EN row and its translations —
  no more `source_id`.

- **Idempotent seeder.** `SuggestionsSeeder` now upserts each `(key, locale)`
  row with `updateOrCreate`, so `php artisan db:seed` can run any number of
  times: it creates missing rows, applies content edits from the JSON, and
  never duplicates. Images are attached to the English source once and shared
  by reference with each translation (skipped when the row already has
  images, so re‑seeding does not churn storage).

- **`SuggestionImagesSeeder` was removed.** Its job (copy a source's images
  onto its translations) is now part of the main seeder loop via
  `HasImages::copyImagesFrom()`.

---

## `TRANSLATION-WORKFLOW.md` (also in this folder)

That document describes the admin‑panel bulk translation feature
("Generate translation JSON" / "Upload translated JSON",
`SuggestionController::exportUntranslated` + `importTranslations`). **That
feature still exists in the app** and its routes, controller and tests are
unchanged. The document was moved here because it is written entirely around
the old two‑file / `source_id` model and its "Seeder data files" and
"How pairs are matched" sections are now out of date — pairs are linked by
`translation_of` (added 2026‑03‑25) and identified by `key` (added
2026‑09‑07).

---

## Rebuilding `../suggestions.json`

If you ever need to regenerate the merged file from a fresh production
export, the merge is: for each English row, look up its Italian counterpart
by `source_title`, compute `key` from the title→slug map in the migration,
derive `images` from the 3rd keyword when the keywords contain `works`, and
emit the nested `translations` object.
