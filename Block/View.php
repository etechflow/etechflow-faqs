<?php
declare(strict_types=1);

namespace Etechflow\Faq\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Etechflow\Faq\Api\FaqRepositoryInterface;

class View extends Template
{
    private Registry $registry;
    private FaqRepositoryInterface $faqRepository;
    private Config $configHelper;

    public function __construct(
        Context $context,
        Registry $registry,
        FaqRepositoryInterface $faqRepository,
        Config $configHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry      = $registry;
        $this->faqRepository = $faqRepository;
        $this->configHelper  = $configHelper;
    }

    public function getConfig(): Config
    {
        return $this->configHelper;
    }

    public function getItem(): ?\Etechflow\Faq\Model\Item
    {
        $item = $this->registry->registry('current_faq_item');
        return $item instanceof \Etechflow\Faq\Model\Item ? $item : null;
    }

    public function getCategory(): ?\Etechflow\Faq\Model\Category
    {
        $cat = $this->registry->registry('current_faq_category');
        return $cat instanceof \Etechflow\Faq\Model\Category ? $cat : null;
    }

    public function getAllCategories(): array
    {
        return $this->faqRepository->getCategories();
    }

    /** @return \Etechflow\Faq\Model\Item[] */
    public function getRelatedItems(?int $limit = null): array
    {
        $item = $this->getItem();
        if (!$item) {
            return [];
        }
        $cap = $limit ?? $this->configHelper->getInt('display/related_articles_count', 6);
        if ($cap < 1) {
            return [];
        }
        return $this->faqRepository->getRelatedItems(
            (int) $item->getCategoryId(),
            (int) $item->getItemId(),
            $cap
        );
    }

    public function getListingUrl(): string
    {
        return $this->getUrl('faqs');
    }

    public function getDetailUrl(string $categoryIdentifier, string $slug): string
    {
        return $this->getUrl('faqs/' . $categoryIdentifier . '/' . $slug);
    }

    public function getVoteEndpoint(): string
    {
        return $this->getUrl('faqs/vote/index');
    }

    public function getCanonicalUrl(): string
    {
        $item = $this->getItem();
        $cat  = $this->getCategory();
        if (!$item || !$cat) {
            return '';
        }
        return $this->getDetailUrl((string) $cat->getData('identifier'), (string) $item->getUrlKey());
    }
}
