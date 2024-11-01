<?php
defined('WPGMAP') or die('Direct access not allowed!');

use WpGmap\core\Helper;
use WpGmap\core\model\MapSettings;
use WpGmap\core\Settings;

$settingsId = intval($_GET['id']);

$url = Settings::getPageUrls();
$mapSettingsModel = new MapSettings();

$currentMapSettings = $mapSettingsModel->show($settingsId);
if ($currentMapSettings) {
    $currentMapSettings->data = Helper::jsonDecode($currentMapSettings->data);
}
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html(Settings::get('name')) ?> - Update Settings</h1>
    <hr class="wp-header-end">

    <?php 
        if (!$currentMapSettings) {
            Helper::errorMessage('Invalid map settings ID');
        }

        $formActionUrl = $url->settingsSubmission;
        include Settings::get('viewDir').'/partials/settings-form.php';
    ?>
</div>