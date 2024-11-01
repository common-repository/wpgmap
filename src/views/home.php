<?php
defined('WPGMAP') or die('Direct access not allowed!');

use WpGmap\core\Helper;
use WpGmap\core\model\MapData;
use WpGmap\core\Settings;

$url = Settings::getPageUrls();
$mapDataModel = new MapData();
$records = $mapDataModel->all('id, name');
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html(Settings::get('name')) ?></h1>
    <a href="<?php echo esc_url($url->addMap) ?>" class="page-title-action">Add New Map</a>
    <hr class="wp-header-end">
    <br>

    <?php if (count($records) > 0) : ?>
    <table class="widefat striped">
        <thead>
        <tr>
            <th width="5%">ID</th>
            <th width="45%">Map name</th>
            <th width="50%">Shortcode</th>
        </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $record) : ?>
            <tr>
                <td><?php echo intval($record->id) ?></td>
                <td>
                    <a href="<?php echo esc_url(Helper::parseTemplate($url->updateMap, ['id' => $record->id])) ?>">
                        <?php echo esc_html($record->name) ?>
                    </a>
                </td>
                <td>[wpgmap id="<?php echo intval($record->id) ?>" name="<?php echo esc_html($record->name) ?>" height="600px" list="false" legends="false"]</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else : ?>
        <p>No records found!</p>
    <?php endif; ?>
</div>