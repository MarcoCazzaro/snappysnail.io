# Upgrade Plan

Full stack upgrade from Laravel 11 / Livewire 3 / Tailwind 3 to latest versions.

**Starting point:** Laravel 11.50.0, Livewire 3.7.12, Tailwind 3.4.19, Vite 5.4.21

---

## Phase 1 — Laravel 11 → 12 ✅

- [x] Check Laravel 12 upgrade guide for breaking changes
- [x] Update `composer.json`: `laravel/framework` → `^12.0`
- [x] Run `composer update laravel/framework --with-all-dependencies`
- [x] Apply any breaking changes (none applied — no breaking changes affected this project)
- [x] Run `./vendor/bin/pest` — 1 pre-existing failure unrelated to upgrade, rest green

## Phase 2 — Laravel 12 → 13 ✅

- [x] Check Laravel 13 upgrade guide for breaking changes
- [x] Update `composer.json`: `laravel/framework` → `^13.0`
- [x] Run `composer update` (also bumped `laravel/tinker` ^3.0, `laravel/boost` ^2.4, `pestphp/pest` ^4.0 simultaneously — required by dependency resolution)
- [x] No breaking changes applied — none affected this project
- [x] Run tests — 58 passed, 7 skipped ✅

## Phase 3 — Livewire 3 → 4 ✅

- [x] Check Livewire 4 upgrade guide for breaking changes
- [x] Update `composer.json`: `livewire/livewire` → `^4.0`
- [x] Run `composer update livewire/livewire --with-all-dependencies`
- [x] `livewire:upgrade` does not exist in v4 (was a v2→v3 tool only)
- [x] Reviewed `SsnailSearch` and `ImagesUpload` — no changes needed (wire:model.live still valid, tags already self-closing)
- [x] Jetstream works fine with Livewire 4
- [x] Run tests — 58 passed, 7 skipped ✅

## Phase 4 — Pest 3 → 4 ✅

- [x] Updated alongside Laravel 13 (required by dependency resolution)
- [x] `pestphp/pest` → v4.4.3, `pestphp/pest-plugin-laravel` → v4.1.0
- [x] Tests pass

## Phase 5 — Vite 5 → 8 + laravel-vite-plugin 1 → 3 ✅

- [x] Update `package.json`: `vite` → `^8.0`, `laravel-vite-plugin` → `^3.0`
- [x] Run `npm install`
- [x] `vite.config.js` needs no changes (no rollupOptions, no import.meta.glob)
- [x] Run `npm run build` — clean build ✅

## Phase 6 — Tailwind CSS 3 → 4 ✅

- [x] Ran `npx @tailwindcss/upgrade@latest --force` (official automated tool)
- [x] `tailwind.config.js` deleted — all config migrated to CSS `@theme` in `app.css`
- [x] `@tailwind base/components/utilities` → `@import 'tailwindcss'`
- [x] Dark mode `@custom-variant dark (&:is(.dark *))`
- [x] Brand colors, font, gradient-radial all migrated to CSS variables
- [x] Border color compatibility shim added automatically
- [x] `postcss.config.js` updated: `tailwindcss` → `@tailwindcss/postcss`, autoprefixer removed
- [x] Views scanned and migrated for renamed utilities automatically
- [x] `npm run build` — clean build ✅
- [x] Tests — 58 passed ✅
- [ ] Visual review in browser (manual step for the user)

## Phase 7 — Minor cleanup ✅

- [x] `laravel/tinker` → v3.0.0 (done in Phase 2)
- [x] `laravel/boost` → v2.4.1 (done in Phase 2)
- [x] All packages updated ✅

---

## Notes

- Jetstream 5.5.2 supports Laravel 11/12/13 (`illuminate/support: ^11.0|^12.0|^13.0`) — no upgrade needed
- Alpine.js 3.15.8 — already current
- Sanctum 4.3.1 — already current
- Nightwatch 1.24.4 — already current
- After each phase: run `vendor/bin/pint --dirty` to fix code style
