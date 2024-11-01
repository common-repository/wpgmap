<?php
defined('WPGMAP') or die('Direct access not allowed!');

use WpGmap\core\Helper;
use WpGmap\core\model\MapData;
use WpGmap\core\Settings;

$url = Settings::getPageUrls();
$mapId = intval($_GET['id']);
$mapDataModel = new MapData();

$currentMap = $mapDataModel->show($mapId);
if ($currentMap) {
    $currentMap->data = Helper::jsonDecode($currentMap->data);
}
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html(Settings::get('name')) ?> - Update Map</h1>
    <hr class="wp-header-end">

    <?php
        $formActionUrl = $url->mapSubmission;
        include Settings::get('viewDir').'/partials/map-form.php';
    ?>
</div>