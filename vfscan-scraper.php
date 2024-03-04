<?php
/*

    Plugin Name: VFScan Scraper
    Plugin URI: 
    Description: Automatic crawl Manga from fr-scan.com and autopost
    Version: 1.0.0
    Author: ZackSnyder
    Author URI: 
    License: 

    */

if (!defined('WP_VFSCAN_PATH')) {
    define('WP_VFSCAN_PATH', plugin_dir_path(__FILE__));
}
if (!defined('WP_VFSCAN_URL')) {
    define('WP_VFSCAN_URL', plugin_dir_url(__FILE__));
}
if (!defined('WP_MCL_TD')) {
    define('WP_MCL_TD', 'madara');
}
if (!defined('ERROR_GET_HTML')) {
    define('ERROR_GET_HTML', 703);
}
if (!defined('ERROR_CLOUD_FLARE')) {
    define('ERROR_CLOUD_FLARE', 704);
}
if (!function_exists('get_admin_loading_icon')) {
    function get_admin_loading_icon()
    {
        return '<img src="' . admin_url('images/spinner.gif') . '" style="vertical-align:bottom">';
    }
}



class WP_VFSCAN
{

    public function __construct()
    {
        $this->hooks();
        $this->init();
    }

    private function hooks()
    {
        add_action('admin_menu', array($this, 'vfscanscraper_admin_menu_option'));
    }
    private function init()
    {

        include WP_VFSCAN_PATH . "vendor/autoload.php";
        include WP_VFSCAN_PATH . "vfscan/vfscan_manga.php";
        include WP_VFSCAN_PATH . "crons/cronjobs.php";
        include WP_VFSCAN_PATH . "crons/crawl-all.php";
    }


    public function vfscanscraper_admin_menu_option()
    {
        add_menu_page(
            'VFScan Scraper',
            'VFScan Scraper',
            'manage_options',
            'vfscan-settings',
            array($this, 'vfscan_scraper_settings'),
            'dashicons-screenoptions',
            2
        );

        add_submenu_page(
            'vfscan-settings',
            'Settings',
            'Settings',
            'manage_options',
            'vfscan-settings',
            array($this, 'vfscan_scraper_settings'),
            1
        );

        add_submenu_page(
            'vfscan-settings',
            'Single Crawl',
            'Single Crawl',
            'manage_options',
            'vfscan-scraper',
            array($this, 'vfscan_scraper'),
            2
        );
    }
    public function vfscan_scraper_settings()
    {
        include WP_VFSCAN_PATH . "settings/settings.php";
    }
    public function vfscan_scraper()
    {

        include WP_VFSCAN_PATH . "vfscan/vfscan.php";
    }
}

$WP_VFSCANSCRAPER = new WP_VFSCAN();
