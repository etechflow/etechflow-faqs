<?php
declare(strict_types=1);

namespace Etechflow\Faq\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Etechflow\Faq\Model\Source\FontFamily;

/**
 * Single point of access to module Stores → Configuration values.
 */
class Config
{
    private const PATH_PREFIX = 'etechflow_faq/';

    /**
     * Subdirectory under pub/media where the Hero image upload field writes.
     * Mirrors the <upload_dir> declared in system.xml so the storefront
     * can resolve bare filenames into full media URLs.
     */
    private const HERO_UPLOAD_DIR = 'etechflow_faq/hero';

    private ScopeConfigInterface $scopeConfig;
    private StoreManagerInterface $storeManager;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig  = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    public function get(string $path): string
    {
        $value = $this->scopeConfig->getValue(
            self::PATH_PREFIX . $path,
            ScopeInterface::SCOPE_STORE
        );
        return $value === null ? '' : (string) $value;
    }

    public function getInt(string $path, int $default = 0): int
    {
        $raw = $this->get($path);
        return $raw === '' ? $default : (int) $raw;
    }

    public function isEnabled(string $path): bool
    {
        return $this->get($path) === '1';
    }

    /**
     * Resolved CSS font-family stack. Returns "" when the admin has chosen
     * "Inherit from theme" — templates should omit the font-family declaration
     * entirely in that case so the host theme's font wins.
     */
    public function getFontFamily(): string
    {
        $choice = $this->get('branding/font_family');
        if ($choice === FontFamily::SYSTEM) {
            return '';
        }
        if ($choice === FontFamily::CUSTOM) {
            return trim($this->get('branding/font_family_custom'));
        }
        return $choice;
    }

    /**
     * Build a media URL for the hero image, or "" if not configured.
     *
     * Two storage shapes supported (depending on how the admin set it):
     *   - **Bare filename** (e.g. `hero.png`) — written by the system.xml
     *     image-upload widget, which saves the file under
     *     `pub/media/etechflow_faq/hero/`. We prepend that path so the URL
     *     resolves to the actual uploaded file.
     *   - **Relative path** (e.g. `cms/faqs/hero.png` or
     *     `etechflow_faq/hero/hero.png`) — typed manually by the admin
     *     in earlier versions or for files placed outside the upload dir.
     *     Used as-is.
     *   - **Absolute URL** (http(s)://…) — used as-is.
     */
    public function getHeroImageUrl(): string
    {
        $raw = trim($this->get('hero/image_path'));
        if ($raw === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $raw)) {
            return $raw;
        }
        // Already includes the upload-dir prefix → use as-is
        if (str_starts_with($raw, self::HERO_UPLOAD_DIR . '/')) {
            return $this->resolveMediaUrl($raw);
        }
        // Magento Image backend with scope_info="1" stores scope-prefixed
        // paths (default/foo.jpg, websites/2/foo.jpg, stores/3/foo.jpg).
        // Prepend the upload dir so the actual file under
        // pub/media/etechflow_faq/hero/<scope>/<file> is reachable.
        if (preg_match('#^(default|websites|stores)/#', $raw)) {
            return $this->resolveMediaUrl(self::HERO_UPLOAD_DIR . '/' . $raw);
        }
        // Bare filename uploaded without scope_info → prepend the upload dir
        if (!str_contains($raw, '/')) {
            return $this->resolveMediaUrl(self::HERO_UPLOAD_DIR . '/' . $raw);
        }
        // Legacy path manually typed in earlier versions (e.g. cms/faqs/hero.png)
        // → used as-is
        return $this->resolveMediaUrl($raw);
    }

    /** Build a media URL for any path stored relative to pub/media. */
    public function resolveMediaUrl(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '';
        }
        if (preg_match('#^https?://#', $path)) {
            return $path;
        }
        try {
            $base = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        } catch (\Exception $e) {
            return '';
        }
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
}
