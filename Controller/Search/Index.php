<?php
declare(strict_types=1);

namespace Etechflow\Faq\Controller\Search;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Framework\UrlInterface;
use Etechflow\Faq\Api\FaqRepositoryInterface;

/**
 * Returns up to 8 matching FAQ items for typeahead suggestions.
 * GET /faqs/search/index?q=delivery → {results: [{question, url, category}, ...]}.
 */
class Index implements HttpGetActionInterface
{
    private RequestInterface $request;
    private JsonFactory $jsonFactory;
    private FaqRepositoryInterface $faqRepository;
    private UrlInterface $urlBuilder;

    public function __construct(
        RequestInterface $request,
        JsonFactory $jsonFactory,
        FaqRepositoryInterface $faqRepository,
        UrlInterface $urlBuilder
    ) {
        $this->request       = $request;
        $this->jsonFactory   = $jsonFactory;
        $this->faqRepository = $faqRepository;
        $this->urlBuilder    = $urlBuilder;
    }

    public function execute(): ResultInterface
    {
        $json  = $this->jsonFactory->create();
        $query = trim((string) $this->request->getParam('q', ''));
        if ($query === '' || strlen($query) < 2) {
            return $json->setData(['results' => []]);
        }
        // Index categories by id once
        $cats = [];
        foreach ($this->faqRepository->getCategories() as $c) {
            $cats[(int) $c->getId()] = [
                'identifier' => (string) $c->getData('identifier'),
                'label'      => (string) $c->getData('label'),
            ];
        }
        $items = $this->faqRepository->search($query, 8);
        $out = [];
        foreach ($items as $i) {
            $cat = $cats[(int) $i->getCategoryId()] ?? null;
            if (!$cat || $i->getUrlKey() === '') {
                continue;
            }
            $out[] = [
                'question' => $i->getQuestion(),
                'subtitle' => $i->getSubtitle(),
                'url'      => $this->urlBuilder->getUrl('faqs/' . $cat['identifier'] . '/' . $i->getUrlKey()),
                'category' => $cat['label'],
            ];
        }
        return $json->setData(['results' => $out]);
    }
}
