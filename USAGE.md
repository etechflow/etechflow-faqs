# Usage Guide

## Admin walkthrough

### Categories

**Content → FAQs → Categories**

| Field | Description |
|---|---|
| Label | Display name shown on the storefront ("Orders", "Returns & Refunds") |
| Identifier | URL slug used as `{category}` in `/faqs/{category}/{slug}` URLs — lowercase, hyphens only. Auto-suggested from the label if left blank |
| Icon | Pick from 9 built-in SVGs: orders, returns, delivery, payment, collect, account, support, technical, locksmith |
| Sort Order | Ascending — smaller numbers first |
| Active | Hide the category from storefront without deleting it |

### Questions & Answers

**Content → FAQs → Questions & Answers**

| Field | Description |
|---|---|
| Category | Drop-down of all active categories |
| Question | The Q text — shows as H1 on the detail page |
| URL Key | Slug for `/faqs/{category}/{slug}`. Leave blank to auto-generate from the question. Must be unique per category — duplicates auto-suffix with `-2`, `-3` |
| Subtitle | One-line summary shown under the H1 on the detail page (optional) |
| Answer | The answer body. Plain text or basic HTML. Supported tags: `<p>`, `<br>`, `<strong>`, `<em>`, `<a>`, `<ul>`/`<li>`, `<h2>`/`<h3>`, `<blockquote>`, `<table>` |
| Sort Order | Ascending — smaller numbers first within a category |
| Active | Hide from storefront and 404 the detail page without deleting |
| Meta Title | SEO `<title>` tag — falls back to Question if blank |
| Meta Description | SEO `<meta name="description">` — optional |

---

## Storefront URLs

| URL | Page |
|---|---|
| `/faqs` | Listing of all active categories with their first 4 questions each + per-category "view all" modal |
| `/faqs?q=delivery` | Same listing, filtered by the search term |
| `/faqs/{category}/{slug}` | Detail page for one question |
| `/faqs/{category}/{slug}/` | Same — trailing slash works |

### URL routing

- **Listing** is served by `Etechflow\Faq\Controller\Index\Index` (the module's standard route at frontName `faqs`, default controller/action).
- **Detail** is served by `Etechflow\Faq\Controller\View\Index`, dispatched through `Etechflow\Faq\Controller\Router` (a custom `RouterInterface`) which matches the 3-segment `/faqs/{cat}/{slug}` pattern and forwards.

If your store has a CMS page or URL rewrite for `/faqs`, that takes priority
over the module's Listing controller. To use the module's listing instead,
remove the CMS page / URL rewrite (see INSTALL.md → "Migrating from a
CMS-page-based FAQ").

---

## REST API

All endpoints are GET, public (no auth header required), and return JSON.

### `GET /rest/V1/etechflow/faq`

Returns every active category with its nested active items.

```bash
curl 'https://example.com/rest/V1/etechflow/faq' | jq .
```

```jsonc
[
    {
        "category_id": 1,
        "identifier": "orders",
        "label": "Orders",
        "icon_key": "orders",
        "items": [
            {
                "item_id": 1,
                "category_id": 1,
                "url_key": "does-the-store-offer-a-price-match",
                "question": "Does the store offer a price match?",
                "subtitle": null,
                "answer": "Yes, we match …",
                "meta_title": null,
                "meta_description": null,
                "sort_order": 0,
                "is_active": 1
            }
        ]
    }
]
```

### `GET /rest/V1/etechflow/faq/categories`

Just the categories, no items.

### `GET /rest/V1/etechflow/faq/categories/:categoryId/items`

Items for one category by numeric id.

```bash
curl 'https://example.com/rest/V1/etechflow/faq/categories/1/items'
```

---

## System configuration reference

**Stores → Configuration → EtechFlow → FAQ**

All paths are `etechflow_faq/<group>/<field>`.

### Branding (`etechflow_faq/branding`)

| Field | Default | Used for |
|---|---|---|
| `primary_color` | `#C41818` | Active sidebar item, helpful "No" button border, blockquote border, breadcrumb category, contact card highlight |
| `secondary_color` | `#0535F5` | Links, search button bg, eyebrow text, "Back to FAQs" hover, related-card top accent |
| `text_color` | `#0E1025` | Headings, body text |
| `font_family` | `Inter, system-ui, sans-serif` | Wraps the whole `.kfaq-view` block |

### Hero (`etechflow_faq/hero`)

| Field | Default | Notes |
|---|---|---|
| `image_path` | *(empty)* | Path relative to `pub/media`. e.g. `cms/faqs/hero.png`. Blank → CSS gradient fallback |
| `title` | `Help Centre & FAQs` | Big heading at the top of the listing |
| `subtitle` | `Find answers to common questions…` | One-line lead text |

### Contact (`etechflow_faq/contact`)

| Field | Default | Notes |
|---|---|---|
| `phone` | *(empty)* | E.g. `08000 250 260`. Blank → row hidden |
| `phone_hours` | *(empty)* | e.g. `Mon – Fri 8:30am – 5:30pm` |
| `email` | *(empty)* | Blank → row hidden |
| `email_response_time` | *(empty)* | e.g. `We aim to reply within 1 hour` |
| `contact_form_url` | `contact` | Relative route or full URL |
| `store_finder_url` | *(empty)* | Blank → store-finder card hidden |

### Display (`etechflow_faq/display`)

| Field | Default | Notes |
|---|---|---|
| `related_articles_count` | `6` | Cap on related-articles grid on detail page |
| `show_helpful_buttons` | `1` | Yes/No buttons on detail page right rail |
| `enable_search_widget` | `1` | Search box in the listing hero |

### Install (`etechflow_faq/install`)

| Field | Default | Notes |
|---|---|---|
| `install_demo_data` | `0` | When `1`, the `InstallDemoData` patch seeds 9 cats + 36 items on next `setup:upgrade`. Toggled via CLI, not the admin UI |

CLI: `bin/magento config:set etechflow_faq/<group>/<field> <value>`

---

## Customising the look

### Re-skin via CSS variables (zero code)

Every colour and font in the storefront templates is wired to a CSS custom
property. Change them in Stores → Configuration and `cache:flush layout
block_html full_page`. No template fork required.

### Override individual styles in your theme

Add a tiny stylesheet to your theme that re-declares the variables OR overrides
specific selectors:

```css
/* In <your-theme>/web/css/source/_etechflow-faq.less */
.kfaq-view {
    --kfaq-primary: #d23;
    --kfaq-secondary: #1b75d0;
}
.kfaq-related-card {
    border-radius: 4px;     /* override radius without touching the module */
}
```

### Fork a template

If you need structural changes, copy the template into your theme:

```bash
mkdir -p <your-theme>/Etechflow_Faq/templates/
cp app/code/Etechflow/Faq/view/frontend/templates/view.phtml \
   <your-theme>/Etechflow_Faq/templates/view.phtml
```

Magento will resolve to your theme copy first. Run `setup:static-content:deploy -f`.

### Adding a new icon

1. Pick or design an inline SVG (24×24, `stroke="currentColor"`).
2. Open `view/frontend/templates/listing.phtml` and `view.phtml`.
3. Add a new key to the `$icons` array:

```php
$icons['my-new-icon'] = '<svg ...> ... </svg>';
```

4. Open `Model/Source/IconKey.php` and add `['value' => 'my-new-icon', 'label' => __('My Label')]` to the array.
5. `bin/magento cache:flush config`. The new icon is now selectable in the category admin.

---

## Layout extension points

Both storefront pages expose named blocks you can target from your theme's
layout XML:

| Block name | Layout handle | Purpose |
|---|---|---|
| `etechflow.faq.listing` | `faqs_view_index_index` | Listing page main block |
| `etechflow.faq.view` | `faqs_view_view_index` | Detail page main block |

Example — add a custom banner under the FAQ listing:

```xml
<!-- <your-theme>/Magento_Theme/layout/faqs_view_index_index.xml -->
<page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    <body>
        <referenceContainer name="content">
            <block class="Magento\Framework\View\Element\Template"
                   name="my.banner"
                   template="Magento_Theme::custom-banner.phtml"
                   after="etechflow.faq.listing"/>
        </referenceContainer>
    </body>
</page>
```

---

## Re-running data patches

Magento records every data patch by class name in the `patch_list` table after
first run. To force `BackfillUrlKeys` (or `InstallDemoData`) to re-run:

```sql
DELETE FROM patch_list WHERE patch_name = 'Etechflow\\Faq\\Setup\\Patch\\Data\\BackfillUrlKeys';
```

Then `bin/magento setup:upgrade` will execute it again.

---

## Renaming the module (advanced)

If you need a different vendor name (e.g. `MyVendor_Faq` instead of `Etechflow_Faq`):

1. Stop traffic / put store in maintenance mode.
2. `mv app/code/Etechflow app/code/MyVendor`
3. Find/replace the namespace across every file:
   ```bash
   grep -rl 'Etechflow\\Faq' app/code/MyVendor/Faq | xargs sed -i 's/Etechflow\\Faq/MyVendor\\Faq/g'
   grep -rl 'Etechflow_Faq' app/code/MyVendor/Faq | xargs sed -i 's/Etechflow_Faq/MyVendor_Faq/g'
   ```
5. Rename DB tables — use a custom one-shot SQL or upgrade patch:
   ```sql
   RENAME TABLE etechflow_faq_category TO etechflow_faq_category,
                etechflow_faq_item     TO etechflow_faq_item;
   ```
6. Update `etc/db_schema.xml` and `etc/db_schema_whitelist.json` with the new table names.
7. `composer.json` → change `name` and the psr-4 prefix.
8. `bin/magento setup:upgrade && bin/magento setup:di:compile && bin/magento cache:flush`.

Allow ~30 minutes and test admin + storefront before unmaintenance.
