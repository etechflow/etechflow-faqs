<?php
declare(strict_types=1);

namespace Etechflow\Faq\Controller;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\RouterInterface;

/**
 * Matches the /faqs/{category}/{slug} pattern for FAQ detail pages.
 * Also matches /faqs/tag/{slug} for tag pages.
 *
 * Reserved controller names (vote, search, submit, tag, index) are passed
 * through to the standard router so the AJAX/admin controllers under
 * Controller/Vote, Controller/Search, etc. still work.
 */
class Router implements RouterInterface
{
    private const RESERVED_CONTROLLERS = ['index', 'view', 'vote', 'search', 'submit', 'tag'];

    private ActionFactory $actionFactory;

    public function __construct(ActionFactory $actionFactory)
    {
        $this->actionFactory = $actionFactory;
    }

    public function match(RequestInterface $request): ?ActionInterface
    {
        if (!$request instanceof HttpRequest) {
            return null;
        }

        $path = trim((string) $request->getPathInfo(), '/');
        if ($path === '' || strpos($path, 'faqs/') !== 0) {
            return null;
        }

        $parts = explode('/', $path);
        if (count($parts) !== 3) {
            return null;
        }

        [, $second, $third] = $parts;
        if ($second === '' || $third === '') {
            return null;
        }

        // Pass through to the standard router for reserved controllers
        // (vote, search, submit, tag, view, index actions live at
        // Controller/<Name>/<Action>.php).
        if (in_array(strtolower($second), self::RESERVED_CONTROLLERS, true)) {
            return null;
        }

        // /faqs/{category}/{slug} → View/Index
        $request->setRouteName('faqs_view')
            ->setModuleName('faqs_view')
            ->setControllerName('view')
            ->setActionName('index')
            ->setParam('category', $second)
            ->setParam('slug', $third);

        return $this->actionFactory->create(\Etechflow\Faq\Controller\View\Index::class);
    }
}
