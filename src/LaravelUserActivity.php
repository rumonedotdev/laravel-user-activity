<?php

namespace Haruncpi\LaravelUserActivity;

use Illuminate\Support\HtmlString;
use RuntimeException;

class LaravelUserActivity
{
    /**
     * Published public directory for package assets.
     */
    private const ASSET_DIRECTORY = 'vendor/laravel-user-activity';

    /**
     * User Activity dashboard CSS file name.
     */
    private const CSS_FILE = 'style.css';

    /**
     * User Activity dashboard JavaScript file name.
     */
    private const JS_FILE = 'main.js';

    /**
     * Resolve full path for a published asset in the public directory.
     */
    private static function resolvePublicAssetPath(string $asset)
    {
        if (!function_exists('public_path')) {
            throw new RuntimeException('Unable to resolve public path for User Activity assets.');
        }

        return public_path(self::ASSET_DIRECTORY.'/'.ltrim($asset, '/'));
    }

    /**
     * Load a published public asset.
     */
    private static function loadPublicAsset(string $asset, string $description)
    {
        $assetPath = self::resolvePublicAssetPath($asset);

        if (($contents = @file_get_contents($assetPath)) === false) {
            throw new RuntimeException("Unable to load the User Activity dashboard {$description} from [{$assetPath}].");
        }

        return $contents;
    }

    /**
     * Get inline stylesheet for the User Activity dashboard.
     *
     * @return HtmlString
     *
     * @throws RuntimeException
     */
    public static function css()
    {
        $css = self::loadPublicAsset(self::CSS_FILE, 'CSS');

        return new HtmlString("<style>{$css}</style>");
    }

    /**
     * Get inline JavaScript for the User Activity dashboard.
     *
     * @return HtmlString
     *
     * @throws RuntimeException
     */
    public static function js()
    {
        $js = self::loadPublicAsset(self::JS_FILE, 'JavaScript');

        return new HtmlString(<<<HTML
            <script type="module">
                {$js}
            </script>
            HTML);
    }
}
