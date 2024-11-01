<?php
namespace WpGmap\core;

class Settings
{
    public static function getAll()
    {
        return (object) [
            'name' => WPGMAP_NAME,
            'version' => WPGMAP_VERSION,
            'dir' => WPGMAP_DIR,
            'url' => WPGMAP_URL,
            'slug' => WPGMAP_SLUG,
            'prefix' => WPGMAP_PREFIX,
            'viewDir' => WPGMAP_DIR . '/src/views',
        ];
    }

    public static function get($name)
    {
        $settings = Settings::getAll();

        if (property_exists($settings, $name)) {
            return $settings->{$name};
        }
    }

    public static function db()
    {
        global $wpdb;

        return $wpdb;
    }

    public static function getTableNames()
    {
        $db = Settings::db();

        return (object) [
            'settings' => $db->prefix . 'gmap_settings',
            'data' => $db->prefix . 'gmap_data',
        ];
    }

    public static function getPageUrls($onlySlug = null)
    {
        if ($onlySlug) {
            return (object) [
                'home' => WPGMAP_SLUG,
                'addMap' => WPGMAP_SLUG.'&view=add-map',
                'settings' => WPGMAP_SLUG.'&view=settings',
                'addSettings' => WPGMAP_SLUG.'&view=add-settings',
                'pro' => WPGMAP_SLUG.'&view=upgrade',
            ];
        }

        return (object) [
            'home' => admin_url('admin.php?page='.WPGMAP_SLUG),
            'addMap' => admin_url('admin.php?page='.WPGMAP_SLUG.'&view=add-map'),
            'updateMap' => admin_url('admin.php?page='.WPGMAP_SLUG.'&view=update-map&id={id}'),
            'mapSubmission' => admin_url('admin.php?page='.WPGMAP_SLUG.'&view=map-submission'),
            'settings' => admin_url('admin.php?page='.WPGMAP_SLUG.'&view=settings'),
            'addSettings' => admin_url('admin.php?page='.WPGMAP_SLUG.'&view=add-settings'),
            'updateSettings' => admin_url('admin.php?page='.WPGMAP_SLUG.'&view=update-settings&id={id}'),
            'settingsSubmission' => admin_url('admin.php?page='.WPGMAP_SLUG.'&view=settings-submission'),
            'pro' => admin_url('admin.php?page='.WPGMAP_SLUG.'&view=upgrade'),
        ];
    }
}