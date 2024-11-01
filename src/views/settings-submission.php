<?php
defined('WPGMAP') or die('Direct access not allowed!');

use WpGmap\core\Helper;
use WpGmap\core\model\MapSettings;
use WpGmap\core\Permission;
use WpGmap\core\Settings;

$message = null;
$url = Settings::getPageUrls();
$mapSettingsModel = new MapSettings();

if (isset($_POST['submit']) && Helper::verifyNonce() && Permission::canAdd()) {
    $postedData = [];
    $postedData['name'] = sanitize_text_field($_POST['name']);
    $postedData['is_default'] = intval($_POST['is_default']);
    $postedData['data'] = Helper::sanitizeArrayField($_POST['data'], ['style' => 'textarea']);
    $insertedId = $mapSettingsModel->add($postedData);

    if ($insertedId > 0) {
        $message = ['status' => 'success', 'text' => 'New map settings added'];
    } elseif (md5($insertedId) == '6bb61e3b7bce0931da574d19d1d82c88') {
        $message = ['status' => 'ok', 'text' => 'WW91IGFyZSB1c2luZyBmcmVlIHZlcnNpb24gb2YgV1BHTUFQLiBQbGVhc2UgdXBncmFkZSB0byBQUk8gPGEgaHJlZj0iaHR0cDovL2hhYmliaGFkaS5jb20vd3BnbWFwIj5odHRwOi8vaGFiaWJoYWRpLmNvbS93cGdtYXA8L2E+LiBGcmVlIHZlcnNpb24gZG9lcyBub3QgYWxsb3cgeW91IGFkZCBtb3JlIHRoYW4gb25lIHNldHRpbmdzLg=='];
    } else {
        $message = ['status' => 'error', 'text' => 'Something went wrong'];
    }
} elseif (isset($_POST['update']) && Helper::verifyNonce() && Permission::canEdit()) {
    $postedData = [];
    $postedData['id'] = intval($_POST['id']);
    $postedData['name'] = sanitize_text_field($_POST['name']);
    $postedData['is_default'] = intval($_POST['is_default']);
    $postedData['data'] = Helper::sanitizeArrayField($_POST['data'], ['style' => 'textarea']);
    $updated = $mapSettingsModel->update($postedData);

    if ($updated) {
        $message = ['status' => 'success', 'text' => 'Map data has been updated!'];
    } else {
        $message = ['status' => 'error', 'text' => 'Something went wrong'];
    }
} elseif(isset($_POST['delete']) && Helper::verifyNonce() && Permission::canDelete()) {
    $postedData = [];
    $postedData['id'] = intval($_POST['id']);
    $deleted = $mapSettingsModel->delete($postedData);

    if ($deleted) {
        Helper::redirect($url->settings);
    } else {
        $message = ['status' => 'error', 'text' => 'Something went wrong'];
    }
}
?>
<div class="wrap">
    <?php 
        if ($message) {
            if ($message['status'] == 'success') {
                echo '<div class="notice notice-'.esc_html($message['status']).'"><p>'.esc_html($message['text']).'</p></div>';
                Helper::redirect($url->settings);
            } elseif ($message['status'] == 'ok') {
                echo '<div class="notice notice-'.esc_html($message['status']).'"><p>'.wp_kses(base64_decode($message['text']), ['a' => ['href' => []]]).'</p></div>';
            } else {
                echo '<div class="notice notice-'.esc_html($message['status']).'"><p>'.esc_html($message['text']).'</p></div>';
            }
        }
    ?>
</div>