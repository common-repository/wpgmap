<?php
namespace WpGmap\core;

class Shortcode 
{
    private $settings;

    public function __construct()
    {
        $this->settings = Settings::getAll();
    }

    public function register()
    {
        add_shortcode('wpgmap', [$this, 'tag']); 
    }

    public function tag($atts)
    {
        ob_start();
        require_once $this->settings->viewDir . '/' . 'gmap.php';
        return ob_get_clean();
    }
}