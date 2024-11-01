<?php
defined('WPGMAP') or die('Direct access not allowed!');

use WpGmap\core\Settings;

$url = Settings::getPageUrls();
$currentMapSettings = null;
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html(Settings::get('name')) ?> - Add New Settings</h1>
    <hr class="wp-header-end">

    <?php
        $formActionUrl = $url->settingsSubmission;
        include Settings::get('viewDir').'/partials/settings-form.php';
    ?>
</div>