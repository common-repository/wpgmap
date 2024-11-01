<?php
defined('WPGMAP') or die('Direct access not allowed!');

use WpGmap\core\Helper;
use WpGmap\core\model\MapData;
use WpGmap\core\Permission;
use WpGmap\core\Settings;

$message = null;
$url = Settings::getPageUrls();
$mapDataModel = new MapData();

if (isset($_POST['submit']) && Helper::verifyNonce() && Permission::canAdd()) {
    $postedData = [];
    $postedData['name'] = sanitize_text_field($_POST['name']);
    $postedData['data'] = Helper::sanitizeArrayField($_POST['data'], ['style' => 'textarea']);
    $postedData['rawdata'] = Helper::sanitizeArrayField(json_decode(stripslashes($_POST['rawdata']), true));
    $insertedId = $mapDataModel->add($postedData);

    if ($insertedId > 0) {
        $message = ['status' => 'success', 'text' => 'New map added'];
    } elseif (md5($insertedId) == '6bb61e3b7bce0931da574d19d1d82c88') {
        $message = ['status' => 'ok', 'text' => 'WW91IGFyZSB1c2luZyBmcmVlIHZlcnNpb24gb2YgV1BHTUFQLiBQbGVhc2UgdXBncmFkZSB0byBQUk8gPGEgaHJlZj0iaHR0cDovL2hhYmliaGFkaS5jb20vd3BnbWFwIj5odHRwOi8vaGFiaWJoYWRpLmNvbS93cGdtYXA8L2E+LiBGcmVlIHZlcnNpb24gZG9lcyBub3QgYWxsb3cgeW91IHRvIGFkZCBtb3JlIHRoYW4gb25lIG1hcC4='];
    } else {
        $message = ['status' => 'error', 'text' => 'Something went wrong'];
    }
} elseif (isset($_POST['update']) && Helper::verifyNonce() && Permission::canEdit()) {
    $postedData = [];
    $postedData['id'] = intval($_POST['id']);
    $postedData['name'] = sanitize_text_field($_POST['name']);
    $postedData['data'] = Helper::sanitizeArrayField($_POST['data'], ['style' => 'textarea']);
    $postedData['rawdata'] = Helper::sanitizeArrayField(json_decode(stripslashes($_POST['rawdata']), true));
    $updatedId = $mapDataModel->update($postedData);

    if ($updatedId > 0) {
        $message = ['status' => 'success', 'text' => 'Map updated'];
    } else {
        $message = ['status' => 'error', 'text' => 'Something went wrong'];
    }
} elseif(isset($_POST['delete']) && Helper::verifyNonce() && Permission::canDelete()) {
    $postedData = [];
    $postedData['id'] = intval($_POST['id']);
    $deleted = $mapDataModel->delete($postedData);

    if ($deleted) {
        Helper::redirect($url->home);
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
                Helper::redirect($url->home);
            } elseif ($message['status'] == 'ok') {
                echo '<div class="notice notice-'.esc_html($message['status']).'"><p>'.wp_kses(base64_decode($message['text']), ['a' => ['href' => []]]).'</p></div>';
            } else {
                echo '<div class="notice notice-'.esc_html($message['status']).'"><p>'.esc_html($message['text']).'</p></div>';
            }
        }
    ?>
</div>