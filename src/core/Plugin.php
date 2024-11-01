<?php
namespace WpGmap\core;

class Plugin
{
    public function __construct()
    {
        
    }

    public function run()
    {
        $this->loadAssets()
        ->registerMenu()
        ->registerShortCode();
    }

    public function loadAssets()
    {
        $asset = new Asset();
        $asset->load();

        return $this;
    }

    public function registerMenu()
    {
        $menu = new Menu();
        $menu->register();

        return $this;
    }

    public function registerShortCode()
    {
        $shortcode = new Shortcode();
        $shortcode->register();

        return $this;
    }
}