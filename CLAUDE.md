# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Stack

Laravel 11 (PHP 8.2+) with Livewire 3, Jetstream, Tailwind CSS 3, Vite, and Alpine.js. MySQL database. Pest for testing.

## Commands

```bash
# Development (runs server, queue, logs, and vite concurrently)
composer dev

# Individual services
php artisan serve
php artisan queue:listen
php artisan pail          # real-time log viewer
npm run dev               # Vite dev server

# Build
npm run build

# Tests
./vendor/bin/pest
./vendor/bin/pest tests/Unit/MyTest.php   # single test file

# Code style
php artisan pint          # auto-fix
vendor/bin/phpcs          # check only
```

## Architecture

### Core Domain: Suggestion Management

The app is primarily a **suggestion CRUD system** with polymorphic image attachments.

**Models:**
- `Suggestion` — has a global scope `SuggestionsSortingScope` (always ordered by `sorting` DESC) and uses the `HasImages` trait
- `Image` — polymorphic attachment model; fires `ImageSaved` / `ImageDeleting` events that trigger queued jobs

**Traits:**
- `HasImages` (`app/Traits/HasImages.php`) — provides `images()` morphMany, `syncImages()`, and convenience accessors; add to any model needing image attachments

**Image Pipeline:**
1. `ImagesUpload` Livewire component handles upload validation (min 5 images rule via `AtLeastFiveImages`)
2. `ImageSaved` event dispatches `OptimiseImage` job → converts to WebP, resizes, writes thumbnail
3. `ImageDeleting` event dispatches `DeletePhysicalImages` job for async cleanup
4. `ImageOptimisation` service (`app/Services/`) handles file path resolution and public URL generation

### Authentication & Admin

Jetstream + Fortify handle auth. All admin routes live under `/admin/*` and require the `auth` middleware. The dashboard is at `/admin/dashboard`.

### Livewire Components

- `SsnailSearch` — real-time keyword search against `Suggestion` records
- `ImagesUpload` — file upload with validation; wired to suggestion forms

### Routing

- `/` — public home (cached 30 days)
- `/dear-googlebot` — SEO-targeted page
- `/{whatever}` — catch-all for SPA-like routing
- `/admin/suggestions` — resourceful CRUD (auth-protected)
- `/api/user` — Sanctum-protected user endpoint

### View Composers

`SEOComposer` is registered in `AppServiceProvider` and runs on every request using the `app` and `guest` layouts — edit it to change global SEO meta.

### Brand / Theming

Tailwind custom colors: `brand: #fcbe03`, `brand-hover: #fc8003`. Dark mode uses the `class` strategy. Custom font is Nunito. Logo/brand assets are in `public/`.

## Notes

- WYSIWYG editor reference: https://quilljs.com/docs/quickstart
- Queue connection is `database`; run `php artisan queue:listen` during development or jobs will not process
- Uploaded images are stored under `storage/app/public/suggestions/images`; run `php artisan storage:link` if symlink is missing
