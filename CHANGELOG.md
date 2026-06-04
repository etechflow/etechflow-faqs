# Changelog

All notable changes to **Etechflow_Faq** will be documented in this file. The
format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and
this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] — 2026-05-16

### Added
- Initial public release.
- Admin: Categories CRUD (label, identifier/slug, icon, sort order, active flag).
- Admin: Q&A items CRUD (question, url_key, subtitle, answer, SEO meta, sort, active).
- Slug auto-generation on save with per-category uniqueness (`-2`, `-3`, … suffix).
- Schema-version backfill patch (`BackfillUrlKeys`) populates slugs for legacy rows.
- Storefront listing at `/faqs` — categories grid, mobile accordion, search filter,
  modal "all questions in category", contact cards, trust bar.
- Storefront detail at `/faqs/{category}/{slug}` — breadcrumb, left categories
  sidebar with active state, main card (eyebrow + H1 + subtitle + rich answer),
  right rail (search, helpful Y/N, contact card), related articles grid.
- Custom `Controller/Router.php` handles the `/faqs/{cat}/{slug}` URL pattern
  without colliding with the `/faqs` listing.
- REST API endpoints: `GET /V1/etechflow/faq`, `GET /V1/etechflow/faq/categories`,
  `GET /V1/etechflow/faq/categories/:id/items`.
- System Configuration section **Stores → Configuration → EtechFlow → FAQ** for
  branding (primary/secondary/text colors, font), hero (image + title + subtitle),
  contact (phone, email, contact-form URL, store-finder URL), and display toggles.
- CSS custom properties wired to config so any host theme can re-skin without
  forking templates.
- Optional demo-data patch (`InstallDemoData`, off by default) seeds 9 categories
  + 36 Q&A items for a new install.
- Composer manifest (`magento2-module`, MIT).
- INSTALL.md, USAGE.md, README.md, CHANGELOG.md.

### Compatible with
- Magento Open Source 2.4.4 → 2.4.8
- Adobe Commerce 2.4.4 → 2.4.8
- PHP 8.1, 8.2, 8.3
- Hyvä Theme 1.3+ (no Hyvä-only code — works on Luma too)
- Luma / blank / any custom frontend theme
