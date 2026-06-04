<?php
declare(strict_types=1);

namespace Etechflow\Faq\Model\Sitemap;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use Magento\Store\Model\ScopeInterface;
use Etechflow\Faq\Api\FaqRepositoryInterface;

/**
 * Contributes FAQ listing + detail URLs to Magento's sitemap.xml generator.
 * Honors the etechflow_faq/seo/enable_sitemap toggle (default ON).
 */
class ItemProvider implements ItemProviderInterface
{
    private FaqRepositoryInterface $faqRepository;
    private SitemapItemInterfaceFactory $itemFactory;
    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        FaqRepositoryInterface $faqRepository,
        SitemapItemInterfaceFactory $itemFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->faqRepository = $faqRepository;
        $this->itemFactory   = $itemFactory;
        $this->scopeConfig   = $scopeConfig;
    }

    public function getItems($storeId): array
    {
        $enabled = (string) $this->scopeConfig->getValue(
            'etechflow_faq/seo/enable_sitemap',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($enabled === '0') {
            return [];
        }

        $items = [];
        // Listing
        $items[] = $this->itemFactory->create([
            'url'        => 'faqs',
            'priority'   => '0.7',
            'changeFrequency' => 'weekly',
        ]);

        // Categories with items
        foreach ($this->faqRepository->getAll() as $cat) {
            $catIdent = (string) $cat->getData('identifier');
            $catItems = method_exists($cat, 'getItems') ? ($cat->getItems() ?? []) : [];
            foreach ($catItems as $i) {
                $slug = (string) ($i->getData('url_key') ?? '');
                if ($slug === '') { continue; }
                $items[] = $this->itemFactory->create([
                    'url'             => 'faqs/' . $catIdent . '/' . $slug,
                    'priority'        => '0.6',
                    'changeFrequency' => 'monthly',
                ]);
            }
        }
        return $items;
    }
}
