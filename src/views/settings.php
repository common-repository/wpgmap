<?php
defined('WPGMAP') or die('Direct access not allowed!');

use WpGmap\core\Helper;
use WpGmap\core\Settings;
use WpGmap\core\model\MapSettings;

$url = Settings::getPageUrls();
$mapSettingsModel = new MapSettings();
$records = $mapSettingsModel->all();
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html(Settings::get('name')) ?> - Settings</h1>
    <a href="<?php echo esc_url($url->addSettings) ?>" class="page-title-action">Add New Settings</a>
    <hr class="wp-header-end">
    <br>

    <?php if (count($records) > 0) : ?>
    <table class="widefat striped">
        <thead>
        <tr>
            <th width="60%">Name</th>
            <th width="40%">Default?</th>
        </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $record) : ?>
            <tr>
                <td>
                    <a href="<?php echo esc_url(Helper::parseTemplate($url->updateSettings, ['id' => $record->id])) ?>">
                        <?php echo esc_html($record->name) ?>
                    </a>
                </td>
                <td><?php echo $record->is_default ? 'Yes' : 'No' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else : ?>
        <p>No records found!</p>
    <?php endif; ?>
</div>