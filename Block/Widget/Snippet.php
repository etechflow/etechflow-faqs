<?php
declare(strict_types=1);

namespace Etechflow\Faq\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;
use Etechflow\Faq\Api\FaqRepositoryInterface;

/**
 * CMS Widget — embed a list of FAQ items into a CMS page / block / product
 * description. Configurable by category identifier, tag slug, featured-only,
 * and item cap.
 */
class Snippet extends Template implements BlockInterface
{
    protected $_template = 'Etechflow_Faq::widget/snippet.phtml';

    private FaqRepositoryInterface $faqRepository;

    public function __construct(
        Context $context,
        FaqRepositoryInterface $faqRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->faqRepository = $faqRepository;
    }

    /** @return \Etechflow\Faq\Api\Data\ItemInterface[] */
    public function getResolvedItems(): array
    {
        $limit        = max(1, (int) ($this->getData('limit') ?: 5));
        $featuredOnly = (int) $this->getData('featured_only') === 1;
        $catIdent     = trim((string) $this->getData('category_identifier'));
        $tagSlug      = trim((string) $this->getData('tag_slug'));

        if ($tagSlug !== '') {
            $items = $this->faqRepository->getItemsByTagSlug($tagSlug);
        } elseif ($catIdent !== '') {
            $cat = $this->faqRepository->getCategoryByIdentifier($catIdent);
            $items = $cat ? $this->faqRepository->getItemsByCategoryId((int) $cat->getId()) : [];
        } elseif ($featuredOnly) {
            $items = $this->faqRepository->getFeaturedItems($limit);
        } else {
            // All — flatten getAll() output
            $items = [];
            foreach ($this->faqRepository->getAll() as $c) {
                foreach (($c->getItems() ?? []) as $i) {
                    $items[] = $i;
                }
            }
        }

        if ($featuredOnly) {
            $items = array_values(array_filter($items, static fn($i) => (int) $i->getData('is_featured') === 1));
        }
        return array_slice($items, 0, $limit);
    }

    public function getDetailUrl(string $categoryIdentifier, string $slug): string
    {
        return $this->getUrl('faqs/' . $categoryIdentifier . '/' . $slug);
    }

    public function getListingUrl(): string
    {
        return $this->getUrl('faqs');
    }

    /**
     * Resolve a category by item — handy to build detail URLs from a flat item list.
     */
    public function getCategoryIdentifierForItem(\Etechflow\Faq\Api\Data\ItemInterface $item): string
    {
        static $cache = null;
        if ($cache === null) {
            $cache = [];
            foreach ($this->faqRepository->getCategories() as $c) {
                $cache[(int) $c->getId()] = (string) $c->getData('identifier');
            }
        }
        return $cache[(int) $item->getCategoryId()] ?? '';
    }
}
