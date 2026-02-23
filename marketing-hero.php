<?php
/**
 * Plugin Name: Marketing Hero
 * Description: Track marketing activities and results with a clean, fast admin experience.
 * Version: 1.0.0
 * Author: Marketing Hero
 * Requires PHP: 8.1
 * Requires at least: 6.0
 * Text Domain: marketing-hero
 */

declare(strict_types=1);

use MarketingHero\Admin\AdminAssets;
use MarketingHero\Admin\AdminMenu;
use MarketingHero\Admin\PostHandlers;
use MarketingHero\Support\Container;
use MarketingHero\Support\Installer;

if (!defined('ABSPATH')) {
    exit;
}

define('MARKETING_HERO_VERSION', '1.0.0');
define('MARKETING_HERO_FILE', __FILE__);
define('MARKETING_HERO_PATH', plugin_dir_path(__FILE__));
define('MARKETING_HERO_URL', plugin_dir_url(__FILE__));

$autoload = MARKETING_HERO_PATH . 'vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
} else {
    require_once MARKETING_HERO_PATH . 'src/Support/Autoloader.php';
    MarketingHero\Support\Autoloader::register();
}

register_activation_hook(__FILE__, static function (): void {
    Installer::activate();
});

add_action('plugins_loaded', static function (): void {
    if (!is_admin()) {
        return;
    }

    $container = new Container();

    (new AdminMenu($container))->register();
    (new AdminAssets())->register();
    (new PostHandlers($container))->register();
});
