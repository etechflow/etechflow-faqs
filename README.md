# Etechflow_Faq — Help-Centre / FAQ Module for Magento 2

A self-contained FAQ / knowledge-base module with public detail pages,
admin management, REST API, and full theme/branding configurability.
Works on **Magento Open Source**, **Adobe Commerce**, with **Hyvä** or
**Luma** themes — no Tailwind, no Alpine, no PageBuilder dependencies.

```
┌─────────────────────────────────────────────────────────────┐
│   Help Centre & FAQs                                        │
│                                                             │
│   [Search articles...] [Search]                             │
│                                                             │
│   ┌──────────┐ ┌──────────┐ ┌──────────┐                    │
│   │ ORDERS   │ │ RETURNS  │ │ DELIVERY │   …                │
│   │ ▸ q1     │ │ ▸ q1     │ │ ▸ q1     │                    │
│   │ ▸ q2     │ │ ▸ q2     │ │ ▸ q2     │                    │
│   └──────────┘ └──────────┘ └──────────┘                    │
└─────────────────────────────────────────────────────────────┘

/faqs                            → listing of all categories
/faqs/orders/can-i-return-x      → detail page with related articles
/rest/V1/etechflow/faq          → REST: all categories with items
```

## Features

- **Categories** with display label, URL identifier, icon, sort order, active toggle.
- **Q&A items** with auto-generated slugs (unique per category), subtitle, rich answer body, SEO meta title + description.
- **Slug-based detail pages** at `/faqs/{category}/{slug}` — breadcrumb, left-rail category navigation, main content card, right-rail (search/helpful/contact), related articles grid.
- **Listing page** at `/faqs` — categories grid (desktop) + mobile accordion, in-page search filter, per-category modal showing all questions.
- **REST API** — three read-only public endpoints returning JSON, no auth required.
- **System Configuration** for branding (colours, font), hero (image + copy), contact details, and display toggles — no template hacking needed.
- **Theme-agnostic** — vanilla CSS scoped under `.kfaq-*`, vanilla JS (no framework). Renders identically on Hyvä and Luma.
- **Demo data** (opt-in) seeds 9 categories + 36 sample questions for a working out-of-the-box page.

## Requirements

| Requirement | Version |
|---|---|
| Magento | 2.4.4 → 2.4.8 (Open Source or Adobe Commerce) |
| PHP | 8.1, 8.2, or 8.3 |
| Frontend theme | Any (Hyvä, Luma, blank, custom) |

## Quick start

### Manual install (no Composer)

```bash
cd <magento-root>
mkdir -p app/code/Etechflow/Faq
# unzip the package into the directory above (so registration.php sits at app/code/Etechflow/Faq/registration.php)
unzip Etechflow_Faq-v1.0.0.zip -d app/code/Etechflow/Faq

bin/magento module:enable Etechflow_Faq
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:flush
```

### Composer install (path repository)

```bash
# In <magento-root>/composer.json add:
#   "repositories": [{ "type": "path", "url": "../Etechflow_Faq" }]
composer require etechflow/module-faq:^1.0
bin/magento setup:upgrade && bin/magento setup:di:compile && bin/magento cache:flush
```

### Verify

- Admin → **Content → FAQs → Categories** — lists 0 rows (or 9 if demo data enabled).
- Admin → **Stores → Configuration → EtechFlow → FAQ** — defaults are populated.
- Storefront → `/faqs` — renders the listing page.

## Documentation

| File | What's inside |
|---|---|
| [INSTALL.md](INSTALL.md) | Manual + Composer install, troubleshooting, post-install cleanup, demo data toggle |
| [USAGE.md](USAGE.md) | Admin walkthrough, REST API, system-config reference, customising the look, layout extension points |
| [CHANGELOG.md](CHANGELOG.md) | Version history |
| [LICENSE](LICENSE) | MIT |

## Module structure

```
app/code/Etechflow/Faq/
├── Api/                                 service contracts
│   ├── Data/{Category,Item}Interface.php
│   └── FaqRepositoryInterface.php
├── Block/                               view blocks
│   ├── Config.php                       — reads Stores → Configuration values
│   ├── Listing.php                      — /faqs landing
│   └── View.php                         — /faqs/{cat}/{slug} detail
├── Controller/
│   ├── Adminhtml/{Category,Item}/*.php  — admin CRUD
│   ├── Index/Index.php                  — /faqs route
│   ├── Router.php                       — matches /faqs/{cat}/{slug}
│   └── View/Index.php                   — detail-page controller
├── Model/                               models, resource models, source models, repository
├── Setup/Patch/Data/
│   ├── BackfillUrlKeys.php              — populates slugs for legacy rows
│   └── InstallDemoData.php              — opt-in seed (9 cats + 36 items)
├── Ui/                                  admin form/listing data providers + action columns
├── etc/                                 module config (acl, di, db_schema, system.xml, etc.)
├── view/
│   ├── adminhtml/                       admin layouts + UI components
│   └── frontend/                        layouts + templates (listing.phtml, view.phtml)
├── composer.json
├── registration.php
└── *.md, LICENSE
```

## Support

Open an issue or reach the maintainer at the email in `composer.json`.
