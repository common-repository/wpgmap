<?php
defined('WPGMAP') or die('Direct access not allowed!');

use WpGmap\core\Settings;

$url = Settings::getPageUrls();
$currentMap = null;
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html(Settings::get('name')) ?> - Add New Map</h1>
    <hr class="wp-header-end">

    <?php
        $formActionUrl = $url->mapSubmission;
        include Settings::get('viewDir').'/partials/map-form.php';
    ?>
</div>