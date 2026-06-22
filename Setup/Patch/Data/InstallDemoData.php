<?php
declare(strict_types=1);

namespace Etechflow\Faq\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Optional demo-data seeder. Disabled by default.
 *
 * Enable BEFORE the first `bin/magento setup:upgrade`:
 *   bin/magento config:set etechflow_faq/install/install_demo_data 1
 *   bin/magento setup:upgrade
 *   bin/magento config:set etechflow_faq/install/install_demo_data 0
 *
 * Seeds 9 categories + 36 sample Q&A items so a fresh install has a working
 * /faqs page immediately. Idempotent: skips the seed if any category already
 * exists (so re-runs after manual editing are safe).
 */
class InstallDemoData implements DataPatchInterface
{
    private const CONFIG_FLAG = 'etechflow_faq/install/install_demo_data';

    private ModuleDataSetupInterface $setup;
    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        ModuleDataSetupInterface $setup,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->setup       = $setup;
        $this->scopeConfig = $scopeConfig;
    }

    public function apply(): self
    {
        $enabled = (string) $this->scopeConfig->getValue(
            self::CONFIG_FLAG,
            ScopeInterface::SCOPE_STORE
        );
        if ($enabled !== '1') {
            return $this;
        }

        $this->setup->startSetup();
        $conn = $this->setup->getConnection();
        $catTable  = $this->setup->getTable('etechflow_faq_category');
        $itemTable = $this->setup->getTable('etechflow_faq_item');

        if ((int) $conn->fetchOne("SELECT COUNT(*) FROM $catTable") > 0) {
            $this->setup->endSetup();
            return $this;
        }

        $categories = $this->getDemoCategories();
        $items      = $this->getDemoItems();

        $identifierToId = [];
        foreach ($categories as $cat) {
            $conn->insert($catTable, [
                'identifier' => $cat['identifier'],
                'label'      => $cat['label'],
                'icon_key'   => $cat['icon_key'],
                'sort_order' => $cat['sort_order'],
                'is_active'  => 1,
            ]);
            $identifierToId[$cat['identifier']] = (int) $conn->lastInsertId($catTable);
        }

        foreach ($items as $item) {
            $catId = $identifierToId[$item['category']] ?? null;
            if (!$catId) {
                continue;
            }
            $conn->insert($itemTable, [
                'category_id'      => $catId,
                'question'         => $item['question'],
                'url_key'          => $item['url_key'],
                'subtitle'         => $item['subtitle'] ?? null,
                'answer'           => $item['answer'],
                'meta_title'       => $item['meta_title'] ?? null,
                'meta_description' => $item['meta_description'] ?? null,
                'sort_order'       => $item['sort_order'],
                'is_active'        => 1,
            ]);
        }

        $this->setup->endSetup();
        return $this;
    }

    /** @return array<int,array{identifier:string,label:string,icon_key:string,sort_order:int}> */
    private function getDemoCategories(): array
    {
        return [
            ['identifier' => 'orders',    'label' => 'Orders',                'icon_key' => 'orders',    'sort_order' => 0],
            ['identifier' => 'returns',   'label' => 'Returns & Refunds',     'icon_key' => 'returns',   'sort_order' => 1],
            ['identifier' => 'delivery',  'label' => 'Delivery / Shipping',   'icon_key' => 'delivery',  'sort_order' => 2],
            ['identifier' => 'payment',   'label' => 'Payment',               'icon_key' => 'payment',   'sort_order' => 3],
            ['identifier' => 'collect',   'label' => 'Click & Collect',       'icon_key' => 'collect',   'sort_order' => 4],
            ['identifier' => 'account',   'label' => 'Your Account',          'icon_key' => 'account',   'sort_order' => 5],
            ['identifier' => 'support',   'label' => 'Customer Services',     'icon_key' => 'support',   'sort_order' => 6],
            ['identifier' => 'technical', 'label' => 'Products / Technical',  'icon_key' => 'technical', 'sort_order' => 7],
            ['identifier' => 'locksmith', 'label' => 'Auto Locksmith',        'icon_key' => 'locksmith', 'sort_order' => 8],
        ];
    }

    /** @return array<int,array{category:string,question:string,url_key:string,subtitle:?string,answer:string,sort_order:int}> */
    private function getDemoItems(): array
    {
        return [
            ['category' => 'orders', 'question' => 'Do you offer a price match?', 'url_key' => 'do-you-offer-a-price-match', 'subtitle' => null, 'answer' => 'Yes, we match prices on identical products from authorised retailers. Contact us with a link to the lower price.', 'sort_order' => 0],
            ['category' => 'orders', 'question' => 'The item I want is out of stock — when will it be back?', 'url_key' => 'the-item-i-want-is-out-of-stock-when-will-it-be-back-in', 'subtitle' => null, 'answer' => 'Sign up for back-in-stock notifications directly on the product page and we will email you the moment it is available.', 'sort_order' => 1],
            ['category' => 'orders', 'question' => 'Can I change or cancel my order?', 'url_key' => 'can-i-change-or-cancel-my-order', 'subtitle' => null, 'answer' => 'Orders can be modified or cancelled within 1 hour of placement if not yet dispatched. Contact us immediately.', 'sort_order' => 2],
            ['category' => 'orders', 'question' => 'Can I manage my order after placing it?', 'url_key' => 'can-i-manage-my-order-after-placing-it', 'subtitle' => null, 'answer' => 'Yes — log into My Account to view order details, track shipment, and manage delivery preferences.', 'sort_order' => 3],

            ['category' => 'returns', 'question' => 'Can I return a product if I change my mind?', 'url_key' => 'can-i-return-a-product-if-i-change-my-mind', 'subtitle' => null, 'answer' => 'Yes. Return any unused, unopened product within 90 days for a full refund. Log into My Account to initiate a return.', 'sort_order' => 0],
            ['category' => 'returns', 'question' => 'Can I return an electronic item?', 'url_key' => 'can-i-return-an-electronic-item', 'subtitle' => null, 'answer' => 'Electronic items can be returned if not yet activated or paired. Once activated, they cannot be returned.', 'sort_order' => 1],
            ['category' => 'returns', 'question' => 'Do you offer free returns?', 'url_key' => 'do-you-offer-free-returns', 'subtitle' => null, 'answer' => 'Yes — we cover return postage for items that are faulty, incorrect, or do not match what you ordered.', 'sort_order' => 2],
            ['category' => 'returns', 'question' => 'How long do I have to return?', 'url_key' => 'how-long-do-i-have-to-return', 'subtitle' => null, 'answer' => 'Our returns window is 90 days from delivery. Items must be unused, in original condition with original packaging.', 'sort_order' => 3],

            ['category' => 'delivery', 'question' => 'Can I change my shipping address after ordering?', 'url_key' => 'can-i-change-my-shipping-address-after-ordering', 'subtitle' => null, 'answer' => 'Address changes may be possible within 1 hour of ordering, before dispatch. Contact our team immediately.', 'sort_order' => 0],
            ['category' => 'delivery', 'question' => 'Can I track my order?', 'url_key' => 'can-i-track-my-order', 'subtitle' => null, 'answer' => 'Yes — once dispatched you will receive a tracking link by email. Also available in My Account at any time.', 'sort_order' => 1],
            ['category' => 'delivery', 'question' => 'Do you offer next-day delivery?', 'url_key' => 'do-you-offer-next-day-delivery', 'subtitle' => null, 'answer' => 'Yes — next-day delivery is available for in-stock items ordered before the cut-off shown at checkout.', 'sort_order' => 2],
            ['category' => 'delivery', 'question' => 'Do you ship internationally?', 'url_key' => 'do-you-ship-internationally', 'subtitle' => null, 'answer' => 'See the shipping page for the current list of supported countries and rates.', 'sort_order' => 3],

            ['category' => 'payment', 'question' => 'How do I pay for my order?', 'url_key' => 'how-do-i-pay-for-my-order', 'subtitle' => null, 'answer' => 'We accept all major credit/debit cards, PayPal, Apple Pay, and Google Pay. All payments secured with 256-bit SSL.', 'sort_order' => 0],
            ['category' => 'payment', 'question' => 'How long does it take to process a refund?', 'url_key' => 'how-long-does-it-take-to-process-a-refund', 'subtitle' => null, 'answer' => 'Once we receive and inspect your return, refunds are processed within 3–5 working days to your original payment method.', 'sort_order' => 1],
            ['category' => 'payment', 'question' => 'Do you accept card payments in-store?', 'url_key' => 'do-you-accept-card-payments-in-store', 'subtitle' => null, 'answer' => 'Yes — we accept all major credit and debit cards at our collection points and in-store locations.', 'sort_order' => 2],
            ['category' => 'payment', 'question' => 'Do you accept PayPal online?', 'url_key' => 'do-you-accept-paypal-online', 'subtitle' => null, 'answer' => 'Yes — PayPal is available as a payment option at checkout alongside all major cards.', 'sort_order' => 3],

            ['category' => 'collect', 'question' => 'How does Click & Collect work?', 'url_key' => 'how-does-click-collect-work', 'subtitle' => null, 'answer' => 'Select Click & Collect at checkout, choose your collection point, and complete payment. We will prepare your order.', 'sort_order' => 0],
            ['category' => 'collect', 'question' => 'When will I be notified that my order is ready?', 'url_key' => 'when-will-i-be-notified-that-my-order-is-ready', 'subtitle' => null, 'answer' => 'You will receive an email and/or SMS once your order is ready for collection, typically within a few hours.', 'sort_order' => 1],
            ['category' => 'collect', 'question' => 'Where do I collect my Click & Collect order?', 'url_key' => 'where-do-i-collect-my-click-collect-order', 'subtitle' => null, 'answer' => 'Collection details — address and opening hours — are confirmed in your ready-to-collect notification.', 'sort_order' => 2],
            ['category' => 'collect', 'question' => 'How long will my Click & Collect order be held?', 'url_key' => 'how-long-will-my-click-collect-order-be-held', 'subtitle' => null, 'answer' => 'We hold Click & Collect orders for up to 7 working days, after which uncollected orders are returned and refunded.', 'sort_order' => 3],

            ['category' => 'account', 'question' => 'Can I create an account for my business?', 'url_key' => 'can-i-create-an-account-for-my-business', 'subtitle' => null, 'answer' => 'Yes — we offer trade accounts for businesses. Contact us to apply.', 'sort_order' => 0],
            ['category' => 'account', 'question' => 'I have forgotten my password or email — what should I do?', 'url_key' => 'i-have-forgotten-my-password-or-email-what-should-i-do', 'subtitle' => null, 'answer' => 'Use the "Forgot Password" link on the sign-in page. For a forgotten email, contact our support team directly.', 'sort_order' => 1],
            ['category' => 'account', 'question' => 'Do I need an account to place an order?', 'url_key' => 'do-i-need-an-account-to-place-an-order', 'subtitle' => null, 'answer' => 'No — guest checkout is available. An account lets you track orders, save addresses, and view history.', 'sort_order' => 2],
            ['category' => 'account', 'question' => 'How do I delete my account?', 'url_key' => 'how-do-i-delete-my-account', 'subtitle' => null, 'answer' => 'Email our support team with your registered email address and we will process deletion within 48 hours.', 'sort_order' => 3],

            ['category' => 'support', 'question' => 'How do I contact the team?', 'url_key' => 'how-do-i-contact-the-team', 'subtitle' => null, 'answer' => 'Phone, email, or live chat — full details on the Contact page. Standard hours: Mon–Fri 9am–5pm.', 'sort_order' => 0],
            ['category' => 'support', 'question' => 'Do you offer a callback service?', 'url_key' => 'do-you-offer-a-callback-service', 'subtitle' => null, 'answer' => 'Yes — leave your number via the contact form and we will call back within 1 working day.', 'sort_order' => 1],
            ['category' => 'support', 'question' => 'Do you have a store finder?', 'url_key' => 'do-you-have-a-store-finder', 'subtitle' => null, 'answer' => 'Yes — use our Store Finder to locate your nearest collection point or partner store.', 'sort_order' => 2],
            ['category' => 'support', 'question' => 'Do you have a loyalty/rewards scheme?', 'url_key' => 'do-you-have-a-loyalty-rewards-scheme', 'subtitle' => null, 'answer' => 'Yes — earn Reward Points on every purchase, redeemable against future orders. Sign up free to start earning.', 'sort_order' => 3],

            ['category' => 'technical', 'question' => 'Do you cut keys in-store?', 'url_key' => 'do-you-cut-keys-in-store', 'subtitle' => null, 'answer' => 'Yes — key cutting is available at our collection points. You will need your key code or vehicle registration.', 'sort_order' => 0],
            ['category' => 'technical', 'question' => 'How do I program a remote to my vehicle?', 'url_key' => 'how-do-i-program-a-remote-to-my-vehicle', 'subtitle' => null, 'answer' => 'Programming instructions are included with every remote. Our technical team can also help.', 'sort_order' => 1],
            ['category' => 'technical', 'question' => 'Do all remotes come with batteries?', 'url_key' => 'do-all-remotes-come-with-batteries', 'subtitle' => null, 'answer' => 'Most remote fobs include a battery — stated clearly on each listing. Replacement batteries also available.', 'sort_order' => 2],
            ['category' => 'technical', 'question' => 'What does OEM mean?', 'url_key' => 'what-does-oem-mean', 'subtitle' => null, 'answer' => 'OEM = Original Equipment Manufacturer. The part is made to the same specification as the factory-fitted part.', 'sort_order' => 3],

            ['category' => 'locksmith', 'question' => 'Do you offer a vehicle opening service?', 'url_key' => 'do-you-offer-a-vehicle-opening-service', 'subtitle' => null, 'answer' => 'Yes — our auto locksmiths cover non-destructive vehicle opening for cars, vans, and motorcycles.', 'sort_order' => 0],
            ['category' => 'locksmith', 'question' => 'Do you program car keys?', 'url_key' => 'do-you-program-car-keys', 'subtitle' => null, 'answer' => 'Yes — we program transponder keys, smart keys, and proximity fobs, including EEPROM cloning.', 'sort_order' => 1],
            ['category' => 'locksmith', 'question' => 'How much is a replacement car key?', 'url_key' => 'how-much-is-a-replacement-car-key', 'subtitle' => null, 'answer' => 'Basic blade keys from a small fee; smart/proximity keys typically more depending on make and model.', 'sort_order' => 2],
            ['category' => 'locksmith', 'question' => 'Can you help if I have lost all my keys?', 'url_key' => 'can-you-help-if-i-have-lost-all-my-keys', 'subtitle' => null, 'answer' => 'Yes — our locksmiths can generate a new key from your vehicle\'s VIN or by reading the ECU directly.', 'sort_order' => 3],
        ];
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
