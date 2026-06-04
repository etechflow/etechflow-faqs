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

    /** Build a media URL for the hero image, or "" if not configured. */
    public function getHeroImageUrl(): string
    {
        return $this->resolveMediaUrl($this->get('hero/image_path'));
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
