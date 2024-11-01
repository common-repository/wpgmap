<?php
namespace WpGmap\core;

use WpGmap\core\model\MapSettings;

class Asset
{
    private $pluginSlug;
    private $pluginUrl;

    public function __construct()
    {
        $this->pluginSlug = Settings::get('slug');
        $this->pluginUrl = Settings::get('url');
    }

    public function load()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
        add_action('wp_enqueue_scripts', [$this, 'themeEnqueue']);
    }

    public function enqueue()
    {
        wp_enqueue_media();

        wp_enqueue_style( $this->pluginSlug, $this->pluginUrl . 'src/css-js/'.$this->pluginSlug.'.css');
        wp_enqueue_script( $this->pluginSlug, $this->pluginUrl . 'src/css-js/'.$this->pluginSlug.'.js', [], '1.0.0', true );

        $this->enqueueGoogleMap();
    }

    public function enqueueGoogleMap()
    {
        $view = isset($_GET['view']) ? sanitize_key($_GET['view']) : null;
        if ($view == 'update-map' || $view == 'add-map') {
            $mapSettingsModel = new MapSettings();
            $defaultMapSettings = $mapSettingsModel->getDefault();

            if ($defaultMapSettings) {
                $defaultMapSettings->data = Helper::jsonDecode($defaultMapSettings->data);
                $googleMapSdkJs = 'https://maps.googleapis.com/maps/api/js?key='.esc_html($defaultMapSettings->data->apikey).'&callback=initDrawMap&libraries=drawing';
                wp_enqueue_script($this->pluginSlug.'-google-map', $googleMapSdkJs, [], '1.0.0', true);
            }
        }
    }

    public function themeEnqueue()
    {
        wp_enqueue_style( $this->pluginSlug.'-theme', $this->pluginUrl . 'src/css-js/theme-'.$this->pluginSlug.'.css');
        wp_enqueue_script( $this->pluginSlug.'-theme', $this->pluginUrl . 'src/css-js/theme-'.$this->pluginSlug.'.js', [], '1.0.0', true ); 
    }
}