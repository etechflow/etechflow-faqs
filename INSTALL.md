# Installation Guide

## Prerequisites

- Magento 2.4.4 – 2.4.8 (Open Source or Adobe Commerce) up and running.
- Shell access with the user that owns the Magento filesystem (typically `www-data`, `app`, or your project user).
- PHP 8.1+, the same PHP binary that runs your store.

## Option A — Manual install (recommended for a single site)

### 1. Extract into `app/code`

```bash
cd <magento-root>                       # e.g. /var/www/html
mkdir -p app/code/Etechflow
unzip /path/to/Etechflow_Faq-v1.0.0.zip -d app/code/Etechflow/
# Result: app/code/Etechflow/Faq/registration.php exists
```

If the zip extracted as `Etechflow_Faq-v1.0.0/` with a version-suffix folder, rename it:

```bash
mv app/code/Etechflow/Etechflow_Faq-v1.0.0 app/code/Etechflow/Faq
```

### 2. Fix ownership (if installed as root)

```bash
chown -R <magento-user>:<magento-group> app/code/Etechflow/Faq
```

### 3. Enable and install

```bash
bin/magento module:status Etechflow_Faq          # should show "disabled"
bin/magento module:enable Etechflow_Faq
bin/magento setup:upgrade
bin/magento setup:di:compile                      # required in production mode
bin/magento setup:static-content:deploy -f        # add -l <locales> e.g. en_US en_GB
bin/magento cache:flush
```

### 4. Verify

- Admin login → top menu **Content → FAQs → Categories** (the menu entry exists).
- **Stores → Configuration → EtechFlow → FAQ** shows the new section with all defaults populated.
- Storefront `/faqs` returns a 200. (If empty, see "Demo data" below.)
- `curl http://<your-domain>/rest/V1/etechflow/faq/categories` returns JSON.

---

## Option B — Composer install (path repository, multi-site)

Useful if you keep the module folder outside Magento and want every site to consume the same code.

### 1. Place the module outside Magento

```bash
unzip Etechflow_Faq-v1.0.0.zip -d /opt/modules/etechflow-faq
# /opt/modules/etechflow-faq/composer.json now exists
```

### 2. Register as a path repo in each Magento site

In `<magento-root>/composer.json`:

```jsonc
{
    "repositories": [
        { "type": "path", "url": "/opt/modules/etechflow-faq" }
    ]
}
```

### 3. Require and install

```bash
composer require etechflow/module-faq:^1.0
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:flush
```

---

## Option C — Composer install (private Packagist / Satis / Git)

If you publish the module to a private Composer repository or a Git tag:

```bash
# composer.json
"repositories": [
    { "type": "git", "url": "git@github.com:your-org/module-faq.git" }
],
"require": {
    "etechflow/module-faq": "^1.0"
}
```

Then the same `setup:upgrade → di:compile → cache:flush` triplet.

---

## Demo data (optional)

The module ships with an opt-in seed of 9 categories and 36 sample Q&A items
(orders, returns, delivery, payment, click-and-collect, account, support,
technical, auto-locksmith).

**Enable BEFORE the first `setup:upgrade`:**

```bash
bin/magento config:set etechflow_faq/install/install_demo_data 1
bin/magento setup:upgrade        # patch runs and seeds the data
bin/magento config:set etechflow_faq/install/install_demo_data 0
bin/magento cache:flush
```

Re-running `setup:upgrade` after the patch is recorded will NOT re-seed the
data (data patches run once per module, recorded in `patch_list`). To re-seed
on demand, see USAGE.md → "Re-running data patches".

---

## Branding setup

After install, visit **Stores → Configuration → EtechFlow → FAQ** to customise:

| Group | Field | Default | Notes |
|---|---|---|---|
| Branding | Primary colour | `#C41818` | Used for active states, accents |
| Branding | Secondary colour | `#0535F5` | Used for links, search buttons |
| Branding | Text colour | `#0E1025` | Headings, body |
| Branding | Font family | `Inter, system-ui, sans-serif` | Any web-safe / loaded font |
| Hero | Image path | *(empty)* | e.g. `cms/faqs/hero.png` — leave blank for gradient fallback |
| Hero | Title | "Help Centre & FAQs" | |
| Hero | Subtitle | "Find answers to common questions…" | |
| Contact | Phone | *(empty)* | hides the card if blank |
| Contact | Phone hours | *(empty)* | small print under the number |
| Contact | Email | *(empty)* | hides the card if blank |
| Contact | Email response time | *(empty)* | small print |
| Contact | Contact form URL | `contact` | relative route or full URL |
| Contact | Store finder URL | *(empty)* | hides the card if blank |
| Display | Related articles count | `6` | Cap on the related-articles grid |
| Display | Show helpful buttons | `Yes` | Yes/No buttons on detail page |
| Display | Enable search widget | `Yes` | Search box in listing hero |

Changes apply on cache flush (`bin/magento cache:flush layout block_html full_page`).

---

## Migrating from a CMS-page-based FAQ

If your site previously rendered `/faqs` via a CMS page that used a custom
template (e.g. `Magento_Cms/templates/faq/index.phtml`), do this after install:

1. **Decide which `/faqs` wins.** Magento's URL rewrites take priority over
   module routes — so as long as the CMS page exists, it serves `/faqs` and
   the module's `Controller/Listing/Index` is dormant.
2. **To switch to the module-served listing**, delete the CMS page:
   Admin → **Content → Elements → Pages** → find the page with URL key `faqs`
   → Actions → Delete.
3. Optionally remove the old theme template:
   `rm <magento-root>/app/design/frontend/<Vendor>/<theme>/Magento_Cms/templates/faq/index.phtml`
4. `bin/magento cache:flush layout block_html full_page` and reload `/faqs`.

The module's listing then takes over with the configured branding.

---

## Troubleshooting

### "Class Magento\\Ui\\Component\\Listing\\Columns\\EditDeleteActionsColumn does not exist"

You have a different version of Magento. The module ships its own action
columns at `Etechflow\Faq\Ui\Component\Listing\Column\{Category,Item}Actions`.
Verify those files are present and run:

```bash
bin/magento setup:di:compile
bin/magento cache:flush
```

### Admin form spins forever ("loading…") on the Item edit page

You may have enabled PageBuilder for the WYSIWYG. This module's Answer field
is intentionally a plain `<textarea>` to avoid PageBuilder's heavy asset
graph. If you customised the field to use `<wysiwyg>`, switch back:

```xml
<!-- app/code/Etechflow/Faq/view/adminhtml/ui_component/etechflow_faq_item_form.xml -->
<field name="answer" sortOrder="30" formElement="textarea">
```

### Listing renders but no questions appear

Either no items exist yet, or none have `is_active = 1`. Check
Admin → FAQs → Questions & Answers.

### Detail page returns 404

Check:
1. The item's `is_active` flag is `1`.
2. The item's `url_key` is populated (run the `BackfillUrlKeys` patch by re-installing the module).
3. The category's `is_active` flag is `1` and `identifier` matches the URL segment.

### Dynamic property deprecation on PHP 8.2+

Already handled in v1.0.0 — the listing data providers declare
`private ?array $loadedData = null;`. If you've forked the module and removed
that declaration, restore it.

### Browser console: "preload not used within a few seconds"

Unrelated to this module — that's typically from a third-party search widget.
Add the `crossorigin` attribute to its `<script>` tag to match its `<link
rel="preload" crossorigin>`.

---

## Uninstall

```bash
bin/magento module:disable Etechflow_Faq
rm -rf app/code/Etechflow/Faq
bin/magento setup:upgrade
# Optional — drop the data tables:
bin/magento setup:db-schema:upgrade    # will report removal of unused tables in dry-run mode
```

To clean the DB tables manually:

```sql
DROP TABLE IF EXISTS etechflow_faq_item;
DROP TABLE IF EXISTS etechflow_faq_category;
DELETE FROM setup_module WHERE module = 'Etechflow_Faq';
DELETE FROM patch_list WHERE patch_name LIKE 'Etechflow\\\\Faq\\\\%';
```
