<?php
namespace WpGmap\core;

class Menu 
{
    private $settings;
    private $urls;

    public function __construct()
    {
        $this->settings = Settings::getAll();
        $this->urls = Settings::getPageUrls(true);
    }

    public function register()
    {
        add_action('admin_menu', [$this, 'bootstrap']);
    }

    public function bootstrap()
    {
        add_menu_page($this->settings->name, $this->settings->name, 'manage_options', $this->settings->slug, [$this, 'menuPage'], 'dashicons-location-alt', 21);
        add_submenu_page( $this->settings->slug, 'Add New Map', 'Add New Map', 'manage_options', $this->urls->addMap, [$this, 'menuPage']);
        add_submenu_page( $this->settings->slug, 'Settings', 'Settings', 'manage_options', $this->urls->settings, [$this, 'menuPage']);
        add_submenu_page( $this->settings->slug, 'Add New Settings', 'Add New Settings', 'manage_options', $this->urls->addSettings, [$this, 'menuPage']);
        add_submenu_page( $this->settings->slug, 'Upgrade to PRO', 'Upgrade to PRO', 'manage_options', $this->urls->pro, [$this, 'menuPage']);
    }

    public function menuPage()
    {
        $view = $this->getViewName();
        if (!$view) {
            $view = 'home';   
        }

        $path = $this->settings->viewDir. '/' . $view . '.php';

        if (file_exists($path)) {
            include $path;
        } else {
            Helper::errorMessage('Requested view file is missing '. $path);
        }
    }

    private function getViewName()
    {
        return isset($_GET['view']) ? sanitize_key($_GET['view']) : NULL;
    }
}