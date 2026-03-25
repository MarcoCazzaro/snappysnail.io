# Bulk Translation Workflow

This document explains how to export, translate, and import Suggestion records in bulk using the admin panel.

---

## Overview

Each `Suggestion` record has a `locale` field (`en` or `it`). Translation pairs are identified by matching `title` — there is no foreign key linking them. The bulk workflow lets you export all unpaired suggestions, translate the file, and import it back in one go.

---

## Step 1 — Generate translation JSON

In the admin panel, go to **Suggestions** and click **Generate translation JSON**.

The system finds every suggestion that has no counterpart with the same title in the other locale, and downloads a file called `untranslated-suggestions.json`.

Each entry looks like this:

```json
{
    "source_id": 23,
    "source_locale": "en",
    "source_title": "Bertone Design",
    "target_locale": "it",
    "title": "Bertone Design",
    "keywords": "works,portfolio,Bertone Design,WordPress",
    "description": "<p>Bertone Design is a company...</p>",
    "url": "https://bertonedesign.it/",
    "sorting": 202501
}
```

**Fields to translate:** `title`, `keywords`, `description`.

**Fields to leave unchanged:** `source_id`, `source_locale`, `source_title`, `target_locale`, `url`, `sorting`.

---

## Step 2 — Translate the file

Open the JSON file in any editor and translate the `title`, `keywords`, and `description` values for each entry.

### Rules

- **`title`** — translate if it has a meaning (e.g. "Contact" → "Contatti", "This website" → "Questo sito"). Leave unchanged for proper names, domain names, and brand names (e.g. "Bertone Design", "Forbes (2023-2025)", "Bluerating.com").
- **`keywords`** — translate descriptive words (e.g. `works` → `lavori`, `contact` → `contatti`); leave technical terms and brand names in English (e.g. `WordPress`, `Laravel`, `Bootstrap`).
- **`description`** — translate all visible text. Preserve all HTML tags, attributes, URLs, and inline code (Alpine.js `x-data`, SVG paths, etc.) exactly as they are. Only replace human-readable text nodes.

### Special cases

- **Contact** — the description contains an SVG QR code. Translate only the introductory text; leave the `<svg>` block untouched.
- **Services** — the description contains an Alpine.js component. Translate the `<h2>` headings, the paragraph text, and the `labels` array values inside `x-data`. The values are HTML-entity encoded (`&quot;`), so be careful to preserve that encoding.
- **Curriculum** — the description is a large HTML block marked with `<!--SSNAIL RAW-->`. Translate all human-readable text (headings, paragraphs, label cells) while keeping the HTML structure intact.

---

## Step 3 — Upload translated JSON

Back in the admin panel, click **Upload translated JSON** and pick the translated file. The system will auto-submit on file selection.

For each entry the system:

1. Looks for an existing suggestion with `locale = target_locale` AND `title = source_title`.
   - **Found** → updates it with the translated fields.
   - **Not found** → creates a new suggestion with the translated fields.
2. Copies `url` and `sorting` from the entry (falling back to the source suggestion if missing).

On completion, a green banner shows how many records were created and updated.

---

## Seeder data files

| File | Description |
|------|-------------|
| `untranslated-suggestions.json` | Last export from production (English suggestions without an Italian counterpart) |
| `translated-suggestions-it.json` | Italian translations ready to upload |
| `suggestions.json` | Full suggestions seed data |

To apply the translations to a database, upload `translated-suggestions-it.json` via the admin panel.

---

## How pairs are matched

Two suggestions are considered a translation pair when they share the **same `title`** and have **different `locale` values**. This is the current linking mechanism — there is no dedicated foreign key column.

A consequence: if you later rename a suggestion's title in one locale but not the other, the pair breaks and the suggestion will appear in the next export.
