<?php
use WpGmap\core\Settings;

defined('WPGMAP') or die('Direct access not allowed!');
?>
<form action="<?php echo esc_url_raw($formActionUrl) ?>" method="post">
    <?php if ($currentMapSettings) : ?>
        <input type="hidden" name="id" value="<?php echo intval($currentMapSettings->id) ?>">
    <?php endif; ?>

    <table class="form-table">
        <tbody>
        <tr>
            <th><label for="wpgmap-name">Name</label></th>
            <td><input type="text" name="name" id="wpgmap-name" value="<?php echo $currentMapSettings ? esc_html($currentMapSettings->name) : '' ?>" required style="width: 50%;"></td>
        </tr>
        <tr>
            <th><label>Mark as default</label></th>
            <td>
                <?php if ($currentMapSettings) : ?>
                    <label><input type="radio" name="is_default" value="1" <?php echo $currentMapSettings->is_default == 1 ? 'checked' : '' ?>> Yes</label> &nbsp;
                    <label><input type="radio" name="is_default" value="0" <?php echo $currentMapSettings->is_default == 0 ? 'checked' : '' ?>> No</label>
                <?php else : ?>
                    <label><input type="radio" name="is_default" value="1"> Yes</label> &nbsp;
                    <label><input type="radio" name="is_default" value="0" checked> No</label>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th><label for="wpgmap-api-key">Map API key</label></th>
            <td><input type="text" name="data[apikey]" id="wpgmap-api-key" value="<?php echo $currentMapSettings ? esc_html($currentMapSettings->data->apikey) : '' ?>" required style="width: 50%;"></td>
        </tr>
        <tr>
            <th><label for="wpgmap-zoom-level">Map zoom level</label></th>
            <td><input type="number" name="data[zoom]" id="wpgmap-zoom-level" value="<?php echo $currentMapSettings ? esc_html($currentMapSettings->data->zoom) : '15' ?>" required></td>
        </tr>
        <tr>
            <th><label for="wpgmap-center-lat">Map center latitude</label></th>
            <td><input type="text" name="data[center_lat]" id="wpgmap-center-lat" value="<?php echo $currentMapSettings ? esc_html($currentMapSettings->data->center_lat) : '' ?>" required></td>
        </tr>
        <tr>
            <th><label for="wpgmap-center-lon">Map center longitude</label></th>
            <td><input type="text" name="data[center_lon]" id="wpgmap-center-lon" value="<?php echo $currentMapSettings ? esc_html($currentMapSettings->data->center_lon) : '' ?>" required></td>
        </tr>
        <tr>
            <th><label for="wpgmap-map-type">Map type</label></th>
            <td>
                <select name="data[mapTypeId]" id="wpgmap-map-type" required>
                    <option value="roadmap" <?php echo $currentMapSettings && $currentMapSettings->data->mapTypeId == 'roadmap' ? 'selected' : '' ?>>roadmap</option>
                    <option value="satellite" <?php echo $currentMapSettings && $currentMapSettings->data->mapTypeId == 'satellite' ? 'selected' : '' ?>>satellite</option>
                    <option value="hybrid" <?php echo $currentMapSettings && $currentMapSettings->data->mapTypeId == 'hybrid' ? 'selected' : '' ?>>hybrid</option>
                    <option value="terrain" <?php echo $currentMapSettings && $currentMapSettings->data->mapTypeId == 'terrain' ? 'selected' : '' ?>>terrain</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="wpgmap-disable-ui">Disable UI</label></th>
            <td>
                <?php if ($currentMapSettings) : ?>
                    <label><input type="radio" name="data[disable_ui]" value="1" <?php echo $currentMapSettings->data->disable_ui == 1 ? 'checked' : '' ?>> True</label> &nbsp;
                    <label><input type="radio" name="data[disable_ui]" value="0" <?php echo $currentMapSettings->data->disable_ui == 0 ? 'checked' : '' ?>> False</label>
                <?php else : ?>
                    <label><input type="radio" name="data[disable_ui]" value="1"> True</label> &nbsp;
                    <label><input type="radio" name="data[disable_ui]" value="0" checked> False</label>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th><label for="wpgmap-marker-icon-url">Map marker icon url</label></th>
            <td><input type="text" name="data[marker_url]" id="wpgmap-marker-icon-url" value="<?php echo $currentMapSettings && $currentMapSettings->data->marker_url ? esc_url_raw($currentMapSettings->data->marker_url) : '' ?>" style="width: 50%;"></td>
        </tr>

        <tr>
            <th><label for="wpgmap-marker-strokeColor">Map shape strokeColor</label></th>
            <td><input type="color" name="data[strokeColor]" id="wpgmap-marker-strokeColor" value="<?php echo $currentMapSettings && isset($currentMapSettings->data->strokeColor) ? esc_html($currentMapSettings->data->strokeColor) : '#FF0000' ?>"></td>
        </tr>
        <tr>
            <th><label for="wpgmap-marker-strokeOpacity">Map shape strokeOpacity</label></th>
            <td><input type="number" name="data[strokeOpacity]" id="wpgmap-marker-strokeOpacity" value="<?php echo $currentMapSettings && isset($currentMapSettings->data->strokeOpacity) ? esc_html($currentMapSettings->data->strokeOpacity) : 0.8 ?>"></td>
        </tr>
        <tr>
            <th><label for="wpgmap-marker-strokeWeight">Map shape strokeWeight</label></th>
            <td><input type="number" name="data[strokeWeight]" id="wpgmap-marker-strokeWeight" value="<?php echo $currentMapSettings && isset($currentMapSettings->data->strokeWeight) ? esc_html($currentMapSettings->data->strokeWeight) : 2 ?>"></td>
        </tr>
        <tr>
            <th><label for="wpgmap-marker-fillColor">Map shape fillColor</label></th>
            <td><input type="color" name="data[fillColor]" id="wpgmap-marker-fillColor" value="<?php echo $currentMapSettings && isset($currentMapSettings->data->fillColor) ? esc_html($currentMapSettings->data->fillColor) : '#FF0000' ?>"></td>
        </tr>
        <tr>
            <th><label for="wpgmap-marker-fillOpacity">Map shape fillOpacity</label></th>
            <td><input type="number" name="data[fillOpacity]" id="wpgmap-marker-fillOpacity" value="<?php echo $currentMapSettings && isset($currentMapSettings->data->fillOpacity) ? esc_html($currentMapSettings->data->fillOpacity) : 0.35 ?>"></td>
        </tr>
        <tr>
            <th><label for="wpgmap-marker-dashed">Map polyline dashed</label></th>
            <td>
                <?php if ($currentMapSettings) : ?>
                    <label><input type="radio" name="data[dashed_polyline]" value="1" <?php echo isset($currentMapSettings->data->dashed_polyline) && $currentMapSettings->data->dashed_polyline == 1 ? 'checked' : '' ?>> True</label> &nbsp;
                    <label><input type="radio" name="data[dashed_polyline]" value="0" <?php echo isset($currentMapSettings->data->dashed_polyline) && $currentMapSettings->data->dashed_polyline == 0 ? 'checked' : '' ?>> False</label>
                <?php else : ?>
                    <label><input type="radio" name="data[dashed_polyline]" value="1"> True</label> &nbsp;
                    <label><input type="radio" name="data[dashed_polyline]" value="0" checked> False</label>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th><label for="wpgmap-marker-openas">Map marker open as</label></th>
            <td>
                <select name="data[openas]" id="wpgmap-marker-openas">
                    <?php if ($currentMapSettings) : ?>
                        <option value="modal" <?php echo $currentMapSettings && isset($currentMapSettings->data->openas) && $currentMapSettings->data->openas == 'modal' ? 'selected' : '' ?>>Modal</option>
                        <option value="window" <?php echo $currentMapSettings && isset($currentMapSettings->data->openas) && $currentMapSettings->data->openas == 'window' ? 'selected' : '' ?>>Info window</option>
                    <?php else: ?>
                        <option value="modal" selected>Modal</option>
                        <option value="window">Info window</option>
                    <?php endif; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="wpgmap-style">Map style array</label></th>
            <td>
                <textarea name="data[style]" id="wpgmap-style" rows="15" style="width: 100%"><?php echo $currentMapSettings && isset($currentMapSettings->data->style) ? stripslashes(esc_textarea($currentMapSettings->data->style)) : '' ?></textarea>
            </td>
        </tr>
        <tr>
            <th><label for="wpgmap-highlight-color">Map highlight color</label></th>
            <td><input type="color" name="data[highlightColor]" id="wpgmap-highlight-color" value="<?php echo $currentMapSettings && isset($currentMapSettings->data->highlightColor) ? esc_html($currentMapSettings->data->highlightColor) : '#000000' ?>"></td>
        </tr>

        <tr>
            <th></th>
            <td>
                <?php wp_nonce_field(Settings::get('prefix').'_action', Settings::get('prefix').'_field'); ?>

                <div style="float: left">
                    <?php submit_button(null, 'primary', isset($currentMapSettings) ? 'update' : 'submit'); ?>
                </div>

                <?php if(isset($currentMapSettings)) : ?>
                <div style="float: right">
                    <?php submit_button('Delete', 'secondary', 'delete'); ?>
                </div>
                <?php endif; ?>
            </td>
        </tr>
        </tbody>
    </table>
</form>
