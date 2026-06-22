<?php
declare(strict_types=1);

namespace Etechflow\Faq\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

/**
 * Renders a short "how to use this page" help panel at the top of each admin
 * page. The text is keyed by the layout's "page" argument (categories,
 * category_edit, items, item_edit, pending, tags, settings).
 */
class Help extends Template
{
    protected $_template = 'Etechflow_Faq::help.phtml';

    public function getHelpText(): string
    {
        $page = (string) $this->getData('page');
        $texts = [
            'categories'    => '<strong>FAQ Categories</strong> group related questions (e.g. Orders, Returns, Delivery). Click <em>Add New Category</em> to create one — give it a Label, pick an Icon, set Sort Order and Active. The URL identifier is auto-generated from the label and used in detail-page URLs as <code>/faqs/{identifier}/{slug}</code>.',
            'category_edit' => '<strong>Category editor</strong>. <em>Label</em> is shown to visitors; <em>URL Identifier</em> appears in the URL of detail pages — keep it short and lowercase. <em>Icon Style</em> picks a built-in SVG; <em>Icon Image</em> overrides it with a media path you uploaded via Content → Media Gallery (e.g. <code>etechflow_faq/icons/orders.png</code>). <em>Sort Order</em> controls position in lists.',
            'items'         => '<strong>Questions & Answers</strong>. One row per Q&A. Pick a Category, type the Question — the URL slug auto-generates from it. Add a Subtitle for the small line under the H1 on the detail page, and write the Answer in plain text or basic HTML (<code>&lt;p&gt;</code>, <code>&lt;strong&gt;</code>, <code>&lt;a&gt;</code>, <code>&lt;ul&gt;</code>). Toggle <em>Featured</em> to pin to the top of the listing.',
            'item_edit'     => '<strong>FAQ editor</strong>. <em>URL Key</em>: leave blank to auto-generate from the question. Must be unique within the category — duplicates auto-suffix <code>-2</code>, <code>-3</code>. <em>Subtitle</em>: one-line summary under the H1 on the detail page. <em>Answer</em>: plain text or basic HTML — full HTML (links, lists, headings, tables) is supported. <em>Featured</em>: pins this item to the top of its category and to the "Popular questions" strip on /faqs. Open the <em>SEO</em> fieldset to set custom meta title / description for the detail page.',
            'pending'       => '<strong>Visitor-submitted questions</strong>. When visitor submissions are enabled in Stores → Configuration → EtechFlow → FAQ, questions submitted via the public form land here. Click a row to review, then Approve (creates a normal FAQ item — you still need to write the answer) or Reject.',
            'tags'          => '<strong>FAQ Tags</strong> let you link questions across categories. e.g. an item "How do I track my order?" might be tagged both <em>orders</em> and <em>delivery</em>. Visitors browse tag pages at <code>/faqs/tag/{slug}</code>. To attach tags to an item, edit the item and use the Tags multiselect.',
            'settings'      => '<strong>EtechFlow FAQ Settings</strong>. Branding controls colours and font; Hero controls the listing-page hero image and copy; Contact controls the right-rail contact card; SEO toggles Schema.org JSON-LD, OpenGraph and sitemap entries; Features toggles featured strip, AJAX search, tags and visitor submissions. Defaults are sensible — leave most blank to inherit your theme.',
        ];
        return $texts[$page] ?? '';
    }

    public function getDocsLink(): string
    {
        // Static link to the bundled USAGE.md inside the module folder
        return 'app/code/Etechflow/Faq/USAGE.md';
    }
}
