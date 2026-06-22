# Changelog

All notable changes to **Etechflow_Faq** will be documented in this file. The
format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and
this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] — 2026-06-05 — Stripe portal licensing + admin gate + storefront/REST gating + Hero image upload

### Added

- **Stripe portal subscription licensing.** Adds the SP-XXXX subscription-key flow — same pattern shipped on `ETechFlow_BackorderEtaDisplay` v1.3.0, `ETechFlow_NextDayEligibility` v1.8.0, and `ETechFlow_ShippingTableRates` v1.2.0. Three plan tiers (Starter $9/mo, Professional $19/mo, Enterprise $49/mo) with in-admin Stripe Checkout, automatic key activation, portal-validated server-IP enforcement, IP-block auto-restore, and 48-hour offline grace when the portal is unreachable. HMAC per-module + bundle keys (`LICENSING_PROTOCOL.md`) also accepted for offline / bundle activation.
- **`Model/LicenseValidator.php`** (greenfield) — 5-arg constructor with tri-state `validateViaPortal(): ?bool` per the enforcement contract. `MODULE_ID = 'faq'`, unique `SECRET_FRAGMENTS`, shared `BUNDLE_SECRET_FRAGMENTS`.
- **License gate page** under **Content → FAQs → License & Plans** with dark plan-cards UI and a Stripe Checkout button.
- **Module Status banner** at the top of Stores → Configuration → ETECHFLOW → FAQ / Help Centre. Always-expanded, 5-state (info / warning / success) — tells the merchant exactly why the module is locked (or that it's active).
- **Admin gating plugin** (`Plugin/Adminhtml/LicenseGatePlugin.php`) — every admin Item / Category / Pending / Categoryimage controller redirects to the license gate when not licensed.
- **Storefront gating plugin** (`Plugin/Controller/StorefrontGatePlugin.php`) — every `/faqs/*` request forwards to Magento's noroute (clean 404) when not licensed.
- **REST API gating plugin** (`Plugin/Api/FaqRepositoryGatePlugin.php`) — all 12 read methods on `FaqRepositoryInterface` return empty arrays / null when not licensed. No content leakage via API.
- **`<payment>` config group** for Stripe `sk_test`/`sk_live`/currency (Encrypted backend model on the secret key).
- **`<license>` config group** with `production_environment`, `license_key`, `bundle_license_key` (obscure + Encrypted), `portal_url`, and auto-managed `issued_key` + `issued_at` + `ip_blocked` fields.

### Changed

- **Hero image path → image upload widget.** Replaced the plain text input with Magento's `type="image"` field + `backend_model="Image"`. Files now upload to `pub/media/etechflow_faq/hero/` directly through the admin UI. `Block\Config::getHeroImageUrl()` updated to resolve bare filenames, scope-prefixed paths (`default/foo.jpg`), and legacy plain paths — fully backward compatible.

### Migration

```
composer update etechflow/module-faq
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```

After upgrade, on a production host the module is **locked by default**. Go to **Stores → Configuration → ETECHFLOW → FAQ / Help Centre → License & Plans** and either paste an existing SP-XXXX / HMAC / bundle key, or click "Select Plan & Pay" to buy a subscription via Stripe.

Dev hosts (localhost, `*.test`, `*.local`, `staging.*`, `*.ngrok-free.dev`, etc.) auto-bypass licensing. Production hosts that aren't auto-detected can opt out with **Production Environment = No**.

### Notes

- `License Portal URL` defaults to `https://license-service.etechflow.com/license/validate` (the eTechFlow portal). For production, change this when eTechFlow publishes the final portal URL.
- Portal IP-revoke + suspend lock the module within ~60 seconds (`CACHE_TTL_REJECT` = 60). Re-activating in the portal restores the module within the same window via `issued_key` auto-restore.

---

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
