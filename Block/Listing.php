<?php
declare(strict_types=1);

namespace Etechflow\Faq\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Etechflow\Faq\Api\FaqRepositoryInterface;

/**
 * Backing block for the public FAQ listing at /faqs.
 * Reads via the repository — no raw SQL — so it works on any store
 * regardless of how the DB connection is configured.
 */
class Listing extends Template
{
    private FaqRepositoryInterface $faqRepository;
    private Config $configHelper;

    public function __construct(
        Context $context,
        FaqRepositoryInterface $faqRepository,
        Config $configHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->faqRepository = $faqRepository;
        $this->configHelper  = $configHelper;
    }

    public function getConfig(): Config
    {
        return $this->configHelper;
    }

    /** @return \Etechflow\Faq\Api\Data\CategoryInterface[] */
    public function getCategoriesWithItems(): array
    {
        return $this->faqRepository->getAll();
    }

    /** @return \Etechflow\Faq\Api\Data\ItemInterface[] */
    public function getFeaturedItems(int $limit = 6): array
    {
        return $this->faqRepository->getFeaturedItems($limit);
    }

    /** @return \Etechflow\Faq\Model\Tag[] */
    public function getTags(): array
    {
        return $this->faqRepository->getTags();
    }

    public function getDetailUrl(string $categoryIdentifier, string $slug): string
    {
        return $this->getUrl('faqs/' . $categoryIdentifier . '/' . $slug);
    }

    public function getSearchEndpoint(): string
    {
        return $this->getUrl('faqs/search/index');
    }

    public function getSubmitEndpoint(): string
    {
        return $this->getUrl('faqs/submit/index');
    }

    public function getTagUrl(string $slug): string
    {
        return $this->getUrl('faqs/tag/' . $slug);
    }
}
