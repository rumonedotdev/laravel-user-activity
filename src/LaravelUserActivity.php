<?php

namespace Haruncpi\LaravelUserActivity;

use Illuminate\Support\HtmlString;
use RuntimeException;

class LaravelUserActivity
{
    /**
     * Vite manifest entry key for the dashboard app.
     */
    const ENTRY_KEY = 'views/ts/main.ts';

    /**
     * Resolve and decode the Vite manifest.
     *
     * Priority:
     * 1) Published assets in the consuming app public directory.
     * 2) Local package dist directory (development fallback).
     *
     * @return array<string, mixed>
     *
     * @throws RuntimeException
     */
    private static function getManifest()
    {
        $manifestCandidates = [];

        if (function_exists('public_path')) {
            $manifestCandidates[] = public_path('vendor/laravel-user-activity/.vite/manifest.json');
        }

        $manifestCandidates[] = __DIR__ . '/../dist/.vite/manifest.json';

        foreach ($manifestCandidates as $manifestPath) {
            if (!is_file($manifestPath)) {
                continue;
            }

            $manifestRaw = @file_get_contents($manifestPath);
            if ($manifestRaw === false) {
                continue;
            }

            $manifest = json_decode($manifestRaw, true);
            if (is_array($manifest)) {
                return $manifest;
            }
        }

        throw new RuntimeException('Unable to locate User Activity asset manifest.');
    }

    /**
     * Base URL used for published dashboard assets.
     *
     * @return string
     */
    private static function getAssetBaseUrl()
    {
        if (function_exists('asset')) {
            return rtrim(asset('vendor/laravel-user-activity'), '/');
        }

        return '/vendor/laravel-user-activity';
    }

    /**
     * Get stylesheet tags for the User Activity dashboard.
     *
     * @return HtmlString
     *
     * @throws RuntimeException
     */
    public static function css()
    {
        $manifest = self::getManifest();
        $entry = isset($manifest[self::ENTRY_KEY]) ? $manifest[self::ENTRY_KEY] : [];
        $cssFiles = isset($entry['css']) && is_array($entry['css']) ? $entry['css'] : [];

        if (empty($cssFiles)) {
            return new HtmlString('');
        }

        $baseUrl = self::getAssetBaseUrl();
        $tags = [];

        foreach ($cssFiles as $cssFile) {
            $href = htmlspecialchars($baseUrl . '/' . ltrim($cssFile, '/'), ENT_QUOTES, 'UTF-8');
            $tags[] = '<link rel="stylesheet" href="' . $href . '">';
        }

        return new HtmlString(implode("\n", $tags));
    }

    /**
     * Get the JavaScript tag for the User Activity dashboard.
     *
     * @return HtmlString
     *
     * @throws RuntimeException
     */
    public static function js()
    {
        $manifest = self::getManifest();
        $entry = isset($manifest[self::ENTRY_KEY]) ? $manifest[self::ENTRY_KEY] : [];
        $jsFile = isset($entry['file']) ? $entry['file'] : null;

        if (empty($jsFile)) {
            throw new RuntimeException('Unable to locate the User Activity dashboard JavaScript.');
        }

        $src = htmlspecialchars(self::getAssetBaseUrl() . '/' . ltrim($jsFile, '/'), ENT_QUOTES, 'UTF-8');

        return new HtmlString('<script type="module" src="' . $src . '"></script>');
    }
}
