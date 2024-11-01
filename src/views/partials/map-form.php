<?php
defined('WPGMAP') or die('Direct access not allowed!');

use WpGmap\core\Helper;
use WpGmap\core\model\MapData;
use WpGmap\core\model\MapSettings;
use WpGmap\core\Settings;

$mapDataModel = new MapData();
$mapSettingsModel = new MapSettings();
$mapSettingsRecords = $mapSettingsModel->all();
$mapSettingsId = isset($_GET['settings']) ? intval($_GET['settings']) : false;
$posts = $mapSettingsModel->getPosts();

if ($mapSettingsId) {
    $defaultMapSettings = $mapSettingsModel->show($mapSettingsId);
} else {
    $defaultMapSettings = $mapSettingsModel->getDefault();
}

if ($defaultMapSettings) {
    $defaultMapSettings->data = Helper::jsonDecode($defaultMapSettings->data);
} else {
    echo '<br><div class="notice notice-warning"><p>No default settings found. <a href="'.esc_url($url->settings).'">Go to settings page</a></p></div>';
    exit();
}

$isDefaultSettingsApplied = false;
if (!$currentMap) {
    $currentMap = new stdClass();
    $currentMap->name = '';
    $currentMap->data = $defaultMapSettings->data;

    $isDefaultSettingsApplied = true;
}

add_thickbox();
?>
<form action="<?php echo esc_url_raw($formActionUrl) ?>" method="post">
    <?php if (!$isDefaultSettingsApplied) : ?>
        <input type="hidden" name="id" value="<?php echo intval($currentMap->id) ?>">
    <?php endif; ?>
    
    <div class="wp-gmap-form-columns">
        <div class="wp-gmap-form-column has-padding-right">
            <!-- form data -->
            <table class="form-table">
                <tbody>
                <tr>
                    <th><label for="wpgmap-settings">Apply settings</label></th>
                    <td>
                        <select id="wpgmap-settings">
                            <option value="">Select to apply</option>
                            <?php foreach ($mapSettingsRecords as $mapSettingsRecord) : ?>
                                <option value="<?php echo $mapSettingsRecord->id ?>"<?php echo $isDefaultSettingsApplied && $defaultMapSettings && $defaultMapSettings->id == $mapSettingsRecord->id ? ' selected' : '' ?>><?php echo esc_html($mapSettingsRecord->name) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <!-- style -->
                        <div id="wpgmap-style-modal" style="display:none;">
                            <br>
                            <textarea name="data[style]" rows="18" style="width: 100%"><?php echo $currentMap ? stripslashes(esc_textarea($currentMap->data->style)) : '' ?></textarea>
                        </div>

                        <a href="#TB_inline?&width=600&inlineId=wpgmap-style-modal" class="thickbox" style="margin-left: 10px;"><?php echo $currentMap && $currentMap->data->style ? 'Update' : 'Add' ?> Map Style</a>
                        <!-- style -->
                    </td>
                </tr>
                <tr>
                    <th><label for="wpgmap-name">Name</label></th>
                    <td><input type="text" name="name" id="wpgmap-name" value="<?php echo $currentMap ? esc_html($currentMap->name) : '' ?>" required style="width: 100%;"></td>
                </tr>
                <tr>
                    <th><label for="wpgmap-api-key">Map API key</label></th>
                    <td><input type="text" name="data[apikey]" id="wpgmap-api-key" value="<?php echo $currentMap ? esc_html($currentMap->data->apikey) : '' ?>" required style="width: 100%;"></td>
                </tr>
                <tr>
                    <th><label for="wpgmap-zoom-level">Map zoom level</label></th>
                    <td><input type="number" step="any" name="data[zoom]" id="wpgmap-zoom-level" value="<?php echo $currentMap ? intval($currentMap->data->zoom) : '15' ?>" required></td>
                </tr>
                <tr>
                    <th><label for="wpgmap-center-lat">Map center latitude</label></th>
                    <td><input type="text" name="data[center_lat]" id="wpgmap-center-lat" value="<?php echo $currentMap ? esc_html($currentMap->data->center_lat) : '' ?>" required></td>
                </tr>
                <tr>
                    <th><label for="wpgmap-center-lon">Map center longitude</label></th>
                    <td><input type="text" name="data[center_lon]" id="wpgmap-center-lon" value="<?php echo $currentMap ? esc_html($currentMap->data->center_lon) : '' ?>" required></td>
                </tr>
                <tr>
                    <th><label for="wpgmap-map-type">Map type</label></th>
                    <td>
                        <select name="data[mapTypeId]" id="wpgmap-map-type" required>
                            <option value="roadmap" <?php echo $currentMap && $currentMap->data->mapTypeId == 'roadmap' ? 'selected' : '' ?>>roadmap</option>
                            <option value="satellite" <?php echo $currentMap && $currentMap->data->mapTypeId == 'satellite' ? 'selected' : '' ?>>satellite</option>
                            <option value="hybrid" <?php echo $currentMap && $currentMap->data->mapTypeId == 'hybrid' ? 'selected' : '' ?>>hybrid</option>
                            <option value="terrain" <?php echo $currentMap && $currentMap->data->mapTypeId == 'terrain' ? 'selected' : '' ?>>terrain</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="wpgmap-disable-ui">Disable UI</label></th>
                    <td>
                        <?php if ($currentMap) : ?>
                            <label><input type="radio" name="data[disable_ui]" value="1" <?php echo $currentMap->data->disable_ui == 1 ? 'checked' : '' ?>> True</label> &nbsp;
                            <label><input type="radio" name="data[disable_ui]" value="0" <?php echo $currentMap->data->disable_ui == 0 ? 'checked' : '' ?>> False</label>
                        <?php else : ?>
                            <label><input type="radio" name="data[disable_ui]" value="1"> True</label> &nbsp;
                            <label><input type="radio" name="data[disable_ui]" value="0" checked> False</label>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th><label for="wpgmap-highlight-color">Map highlight color</label></th>
                    <td><input type="color" name="data[highlight_color]" id="wpgmap-center-lon" value="<?php echo $currentMap && isset($currentMap->data->highlight_color) ? esc_html($currentMap->data->highlight_color) : '#000000' ?>" required></td>
                </tr>
                <tr>
                    <th><label for="wpgmap-disable-legends">Legends</label></th>
                    <td>
                        <ul class="wpgmap-legend-list">
                            <?php if (isset($currentMap->data->legends)) : for ($i = 0; $i < count($currentMap->data->legends['name']); $i++) : if ($currentMap->data->legends['name'][$i]) : ?>
                                <li>
                                    <input list="wpgmap-categories" type="text" name="data[legends][name][]" value="<?php echo esc_html($currentMap->data->legends['name'][$i]) ?>" style="width: 80%">
                                    <input type="color" name="data[legends][color][]" value="<?php echo esc_html($currentMap->data->legends['color'][$i]) ?>">
                                    <span class="dashicons dashicons-remove remove-legend"></span>
                                </li>
                            <?php endif; endfor; endif; ?>
                            <li>
                                <input list="wpgmap-categories" type="text" name="data[legends][name][]" placeholder="Name" style="width: 80%">
                                <input type="color" name="data[legends][color][]" placeholder="color">
                                <span class="dashicons dashicons-remove remove-legend"></span>
                            </li>
                        </ul>
                        <a href="" class="button-link add-more-legend">Add</a>
                        
                        <datalist id="wpgmap-categories">
                        <?php
                            $categories = $mapDataModel->getCategories();

                            foreach ($categories as $category) {
                                echo '<option value="'.esc_attr($category->name).'">';
                            }
                        ?>
                        </datalist>
                    </td>
                </tr>
                <tr>
                    <th></th>
                    <td>
                        <?php wp_nonce_field(Settings::get('prefix').'_action', Settings::get('prefix').'_field'); ?>

                        <div style="float: left">
                            <?php submit_button(null, 'primary', !$isDefaultSettingsApplied ? 'update' : 'submit'); ?>
                        </div>

                        <?php if(!$isDefaultSettingsApplied) : ?>
                        <div style="float: right">
                            <?php submit_button('Delete', 'secondary', 'delete'); ?>
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
                </tbody>
            </table>

            <input type="hidden" name="rawdata" id="wpgmap-rawdata">
            <!-- form data -->
        </div>
        <div class="wp-gmap-form-column">
            <!-- map -->
            <div id="wp-gmap-draw"></div>
            <script>
                window.wpGmapOverlays = {};
                window.wpGmapMarkings = [];

                <?php if (!$isDefaultSettingsApplied && $currentMap->rawdata) : ?>
                    wpGmapMarkings = JSON.parse('<?php echo Helper::escapeJson($currentMap->rawdata) ?>');
                <?php endif; ?>
                
                <?php if ($isDefaultSettingsApplied) : ?>
                var defaultMapOptions = {
                    strokeColor: "<?php echo $currentMap && isset($currentMap->data->strokeColor) ? esc_js($currentMap->data->strokeColor) : '#ff0000' ?>",
                    strokeOpacity: <?php echo $currentMap && isset($currentMap->data->strokeOpacity) ? esc_js($currentMap->data->strokeOpacity) : 0.8 ?>,
                    strokeWeight: <?php echo $currentMap && isset($currentMap->data->strokeWeight) ? intval($currentMap->data->strokeWeight) : 2 ?>,
                    fillColor: "<?php echo $currentMap && isset($currentMap->data->fillColor) ? esc_js($currentMap->data->fillColor) : '#ff0000' ?>",
                    fillOpacity: <?php echo $currentMap && isset($currentMap->data->fillOpacity) ? esc_js($currentMap->data->fillOpacity) : 0.35 ?>,
                    dashed: "<?php echo $currentMap && isset($currentMap->data->dashed_polyline) ? esc_js($currentMap->data->dashed_polyline) : '0' ?>",
                };
                <?php else : ?>
                var defaultMapOptions = {
                    strokeColor: "<?php echo $defaultMapSettings && isset($defaultMapSettings->data->strokeColor) ? esc_js($defaultMapSettings->data->strokeColor) : '#ff0000' ?>",
                    strokeOpacity: <?php echo $defaultMapSettings && isset($defaultMapSettings->data->strokeOpacity) ? esc_js($defaultMapSettings->data->strokeOpacity) : 0.8 ?>,
                    strokeWeight: <?php echo $defaultMapSettings && isset($defaultMapSettings->data->strokeWeight) ? intval($defaultMapSettings->data->strokeWeight) : 2 ?>,
                    fillColor: "<?php echo $defaultMapSettings && isset($defaultMapSettings->data->fillColor) ? esc_js($defaultMapSettings->data->fillColor) : '#ff0000' ?>",
                    fillOpacity: <?php echo $defaultMapSettings && isset($defaultMapSettings->data->fillOpacity) ? esc_js($defaultMapSettings->data->fillOpacity) : 0.35 ?>,
                    dashed: "<?php echo $defaultMapSettings && isset($defaultMapSettings->data->dashed_polyline) ? esc_js($defaultMapSettings->data->dashed_polyline) : '0' ?>",
                };
                <?php endif; ?>
                
                function generateUniqueId() {
                    var min = 1;
                    var max = 10000;

                    return Math.floor(Math.random() * (max - min + 1) ) + min + (+ new Date());
                }

                function arrayToString(array) {
                    var str = "";
                    for (var a = 0; a < array.length; a++) {
                        str += "(";
                        str += array[a].lat + ",";
                        str += array[a].lon + ")";
                        if (a != array.length - 1) {
                            str += ",";
                        }
                    }
                    return str;
                }

                var debounceTime = 250;
                var debouceTimeout = null;
                function onMarkersChange() {
                    if (debouceTimeout) {
                        clearTimeout(debouceTimeout);
                    }

                    debouceTimeout = setTimeout(function() {
                        wpGmap();
                    }, debounceTime);
                }

                function onSelectMarking(id) {
                    wpGmapOnSelectMarking(id);
                }

                function addMarking(marker) {
                    var id = generateUniqueId();
                    marker.id = id;
                    marker.options = {
                        icon: '<?php echo $defaultMapSettings && isset($defaultMapSettings->data->marker_url) ? esc_url_raw($defaultMapSettings->data->marker_url) : null ?>',
                        postid: null,
                        openas: 'modal',
                        strokeColor: defaultMapOptions.strokeColor,
                        strokeOpacity: defaultMapOptions.strokeOpacity,
                        strokeWeight: defaultMapOptions.strokeWeight,
                        fillColor: defaultMapOptions.fillColor,
                        fillOpacity: defaultMapOptions.fillOpacity,
                        dashed: defaultMapOptions.dashed,
                    };
                    marker.name = '';
                    wpGmapMarkings.push(marker);

                    onMarkersChange();

                    return id;
                }

                function updateMarking(marker, id) {
                    for (var i = 0; i < wpGmapMarkings.length; i++) {
                        if (wpGmapMarkings[i].id == id) {
                            wpGmapMarkings[i].value = marker.value;

                            onMarkersChange();
                            break;
                        }
                    }
                }

                var getMarkerCoordinates = function (marker) {
                    return {
                        lat: marker.getPosition().lat(),
                        lon: marker.getPosition().lng()
                    }
                }

                var getPolygonCoordinates = function (polygon) {
                    var arr = [];

                    for (var i = 0; i < polygon.getPath().getLength(); i++) {
                        var value = polygon.getPath().getAt(i).toUrlValue(6);
                        var valueArray = value.split(',');
                        arr.push({ lat: valueArray[0], lon: valueArray[1] });
                    }

                    return arrayToString(arr);
                }

                var getCircleCoordinates = function (circle) {
                    return  {
                        center: {
                            lat: circle.getCenter().lat(),
                            lon: circle.getCenter().lng()
                        },
                        radius: circle.getRadius(),
                    }
                }

                var getPolylineCoordinates = function (polyline) {
                    var arr = [];

                    for (var i = 0; i < polyline.getPath().getLength(); i++) {
                        var value = polyline.getPath().getAt(i).toUrlValue(6);
                        var valueArray = value.split(',');
                        arr.push({ lat: valueArray[0], lon: valueArray[1] });
                    }

                    return arrayToString(arr);
                }

                var getRectangleCoordinates = function (rectangle) {
                    var bounds = rectangle.getBounds();
                    return {
                        north: bounds.getNorthEast().lat(),
                        south: bounds.getSouthWest().lat(),
                        east: bounds.getNorthEast().lng(),
                        west: bounds.getSouthWest().lng()
                    }
                }

                var mapHelper = {
                    marker: function (marker, id) {
                        var m = {
                            type: 'marker',
                            value: getMarkerCoordinates(marker)
                        }

                        if (!id) {
                            var id = addMarking(m);

                            google.maps.event.addListener(marker, 'position_changed', function() {
                                m.value = getMarkerCoordinates(this);

                                updateMarking(m, id);
                            });

                            google.maps.event.addListener(marker, 'click', function() {
                                onSelectMarking(id);
                            });

                            return id;
                        }
                    },
                    polygon: function (polygon) {
                        var marker = {
                            type: 'polygon',
                            value: getPolygonCoordinates(polygon),
                        };

                        if (!id) {
                            var id = addMarking(marker);

                            polygon.getPaths().forEach(function(path, index){
                                google.maps.event.addListener(path, 'insert_at', function() {
                                    marker.value = getPolygonCoordinates(polygon);
                                    updateMarking(marker, id);
                                });

                                google.maps.event.addListener(path, 'remove_at', function() {
                                    marker.value = getPolygonCoordinates(polygon);
                                    updateMarking(marker, id);
                                });

                                google.maps.event.addListener(path, 'set_at', function() {
                                    marker.value = getPolygonCoordinates(polygon);
                                    updateMarking(marker, id);
                                });
                            });

                            google.maps.event.addListener(polygon, 'dragend', function() {
                                marker.value = getPolygonCoordinates(polygon);
                                updateMarking(marker, id);
                            });

                            google.maps.event.addListener(polygon, 'click', function() {
                                onSelectMarking(id);
                            });

                            return id;
                        }
                    },
                    circle: function (circle) {
                        var marker = {
                            type: 'circle',
                            value: getCircleCoordinates(circle)
                        };

                        if (!id) {
                            var id = addMarking(marker);

                            google.maps.event.addListener(circle, 'center_changed', function() {
                                marker.value = getCircleCoordinates(this);
                                updateMarking(marker, id);
                            });

                            google.maps.event.addListener(circle, 'radius_changed', function() {
                                marker.value = getCircleCoordinates(this);
                                updateMarking(marker, id);
                            });

                            google.maps.event.addListener(circle, 'click', function() {
                                onSelectMarking(id);
                            });

                            return id;
                        }
                    },
                    polyline: function (polyline) {
                        var marker = {
                            type: 'polyline',
                            value: getPolylineCoordinates(polyline)
                        };

                        if (!id) {
                            var id = addMarking(marker);

                            google.maps.event.addListener(polyline.getPath(), 'insert_at', function () {
                                marker.value = getPolylineCoordinates(polyline);
                                updateMarking(marker, id);
                            });

                            google.maps.event.addListener(polyline.getPath(), 'remove_at', function () {
                                marker.value = getPolylineCoordinates(polyline);
                                updateMarking(marker, id);
                            });

                            google.maps.event.addListener(polyline.getPath(), 'set_at', function () {
                                marker.value = getPolylineCoordinates(polyline);
                                updateMarking(marker, id);
                            });

                            google.maps.event.addListener(polyline, 'dragend', function() {
                                marker.value = getPolylineCoordinates(this);
                                updateMarking(marker, id);
                            });

                            google.maps.event.addListener(polyline, 'click', function() {
                                onSelectMarking(id);
                            });

                            return id;
                        }
                    },
                    rectangle: function (rectangle) {
                        var marker = {
                            type: 'rectangle',
                            value: getRectangleCoordinates(rectangle)
                        };

                        if (!id) {
                            var id = addMarking(marker);

                            google.maps.event.addListener(rectangle, 'bounds_changed', function() {
                                marker.value = getRectangleCoordinates(this);
                                updateMarking(marker, id);
                            });

                            google.maps.event.addListener(rectangle, 'click', function() {
                                onSelectMarking(id);
                            });

                            return id;
                        }
                    },
                };

                function initDrawMap() {
                    var map = new google.maps.Map(document.getElementById("wp-gmap-draw"), {
                        center: {
                            lat: <?php echo esc_html($currentMap->data->center_lat) ?>,
                            lng: <?php echo esc_html($currentMap->data->center_lon) ?>
                        },
                        zoom: <?php echo intval($currentMap->data->zoom) ?>,
                        mapTypeId: '<?php echo esc_html($currentMap->data->mapTypeId) ?>',
                        <?php if ($currentMap->data->style) : ?>
                        styles: <?php echo stripslashes($currentMap->data->style) ?>
                        <?php endif; ?>
                    });

                    var polylineOptions = {
                        draggable: true,
                        editable: true,
                        strokeColor: defaultMapOptions.strokeColor,
                        strokeOpacity: defaultMapOptions.strokeOpacity,
                        strokeWeight: defaultMapOptions.strokeWeight,
                    };

                    if (defaultMapOptions.dashed == '1') {
                        var lineSymbol = {
                            path: "M 0,-1 0,1",
                            scale: 4,
                            strokeColor: defaultMapOptions.strokeColor,
                            strokeOpacity: defaultMapOptions.strokeOpacity,
                            strokeWeight: defaultMapOptions.strokeWeight,
                        };

                        polylineOptions.strokeOpacity = 0;
                        polylineOptions.icons = [
                            {
                                icon: lineSymbol,
                                offset: "0",
                                repeat: "20px",
                            },
                        ];
                    } 

                    var drawingManager = new google.maps.drawing.DrawingManager({
                        drawingMode: google.maps.drawing.OverlayType.MARKER,
                        drawingControl: true,
                        drawingControlOptions: {
                            position: google.maps.ControlPosition.TOP_CENTER,
                            drawingModes: [
                                google.maps.drawing.OverlayType.MARKER,
                                google.maps.drawing.OverlayType.CIRCLE,
                                google.maps.drawing.OverlayType.POLYGON,
                                google.maps.drawing.OverlayType.POLYLINE,
                                google.maps.drawing.OverlayType.RECTANGLE,
                            ],
                        },
                        markerOptions: {
                            draggable: true,
                            icon: '<?php echo $defaultMapSettings && isset($defaultMapSettings->data->marker_url) ? esc_url($defaultMapSettings->data->marker_url) : null ?>',
                        },
                        circleOptions: {
                            draggable: true,
                            editable: true,
                            strokeColor: defaultMapOptions.strokeColor,
                            strokeOpacity: defaultMapOptions.strokeOpacity,
                            strokeWeight: defaultMapOptions.strokeWeight,
                            fillColor: defaultMapOptions.fillColor,
                            fillOpacity: defaultMapOptions.fillOpacity,
                        },
                        polygonOptions: {
                            draggable: true,
                            editable: true,
                            strokeColor: defaultMapOptions.strokeColor,
                            strokeOpacity: defaultMapOptions.strokeOpacity,
                            strokeWeight: defaultMapOptions.strokeWeight,
                            fillColor: defaultMapOptions.fillColor,
                            fillOpacity: defaultMapOptions.fillOpacity,
                        },
                        polylineOptions: polylineOptions,
                        rectangleOptions: {
                            draggable: true,
                            editable: true,
                            strokeColor: defaultMapOptions.strokeColor,
                            strokeOpacity: defaultMapOptions.strokeOpacity,
                            strokeWeight: defaultMapOptions.strokeWeight,
                            fillColor: defaultMapOptions.fillColor,
                            fillOpacity: defaultMapOptions.fillOpacity,
                        },
                    });

                    drawingManager.setMap(map);

                    drawingManager.addListener('overlaycomplete', function(event) {
                        var shapeId = null;

                        if (Object.keys(wpGmapOverlays).length >= 6) {
                            alert(atob('RlJFRSB2ZXJzaW9uIGRvZXMgbm90IGFsbG93IG1vcmUgdGhhbiA2IG1hcmtlcnM='));

                            event.overlay.setMap(null);
                            return false;
                        }

                        switch (event.type) {
                            case 'marker':
                                shapeId = mapHelper.marker(event.overlay);
                            break;

                            case 'circle':
                                shapeId = mapHelper.circle(event.overlay);
                            break;

                            case 'polygon':
                                shapeId = mapHelper.polygon(event.overlay);
                            break;

                            case 'polyline':
                                shapeId = mapHelper.polyline(event.overlay);
                            break;

                            case 'rectangle':
                                shapeId = mapHelper.rectangle(event.overlay);
                            break;
                        }

                        if (shapeId) {
                            wpGmapOverlays[shapeId] = event;
                        }
                    });

                    <?php 
                        if ($currentMap && isset($currentMap->data->markings) && count($currentMap->data->markings) > 0) {
                            foreach($currentMap->data->markings as $markingId => $marking) {
                                if ($marking['type'] == 'marker') : ?>
                                    var marker<?php echo esc_attr($markingId) ?> = new google.maps.Marker({
                                        position: {
                                            lat: <?php echo esc_attr($marking['lat']) ?>,
                                            lng: <?php echo esc_attr($marking['lon']) ?>
                                        },
                                        draggable: true,
                                        map: map,
                                        title: "<?php echo esc_html($marking['name']) ?>",
                                        icon: "<?php echo esc_url($marking['icon']) ?>"
                                    });

                                    var id<?php echo esc_attr($markingId) ?> = '<?php echo esc_attr($markingId) ?>';
                                    var m<?php echo esc_attr($markingId) ?> = {
                                        type: 'marker',
                                        value: getMarkerCoordinates(marker<?php echo esc_attr($markingId) ?>)
                                    }

                                    google.maps.event.addListener(marker<?php echo esc_attr($markingId) ?>, 'position_changed', function() {
                                        m<?php echo esc_attr($markingId) ?>.value = getMarkerCoordinates(this);

                                        updateMarking(m<?php echo esc_attr($markingId) ?>, id<?php echo esc_attr($markingId) ?>);
                                    });

                                    google.maps.event.addListener(marker<?php echo esc_attr($markingId) ?>, 'click', function() {
                                        onSelectMarking(id<?php echo esc_attr($markingId) ?>);
                                    });

                                    wpGmapOverlays[<?php echo esc_attr($markingId) ?>] = marker<?php echo esc_attr($markingId) ?>;
                                <?php elseif ($marking['type'] == 'circle') : ?>
                                    var circle<?php echo esc_attr($markingId) ?> = new google.maps.Circle({
                                        strokeColor: "<?php echo esc_attr($marking['strokeColor']) ?>",
                                        strokeOpacity: <?php echo esc_attr($marking['strokeOpacity']) ?>,
                                        strokeWeight: <?php echo esc_attr($marking['strokeWeight']) ?>,
                                        fillColor: "<?php echo esc_attr($marking['fillColor']) ?>",
                                        fillOpacity: <?php echo esc_attr($marking['fillOpacity']) ?>,
                                        map,
                                        center: { lat: <?php echo esc_attr($marking['centerLat']) ?>, lng: <?php echo esc_attr($marking['centerLon']) ?> },
                                        radius: <?php echo esc_attr($marking['radius']) ?>,
                                        draggable: true,
                                        editable: true,
                                    });

                                    var id<?php echo esc_attr($markingId) ?> = '<?php echo esc_attr($markingId) ?>';
                                    var marker<?php echo esc_attr($markingId) ?> = {
                                        type: 'circle',
                                        value: getCircleCoordinates(circle<?php echo esc_attr($markingId) ?>)
                                    };

                                    google.maps.event.addListener(circle<?php echo esc_attr($markingId) ?>, 'center_changed', function() {
                                        marker<?php echo esc_attr($markingId) ?>.value = getCircleCoordinates(this);
                                        updateMarking(marker<?php echo esc_attr($markingId) ?>, id<?php echo esc_attr($markingId) ?>);
                                    });

                                    google.maps.event.addListener(circle<?php echo esc_attr($markingId) ?>, 'radius_changed', function() {
                                        marker<?php echo esc_attr($markingId) ?>.value = getCircleCoordinates(this);
                                        updateMarking(marker<?php echo esc_attr($markingId) ?>, id<?php echo esc_attr($markingId) ?>);
                                    });

                                    google.maps.event.addListener(circle<?php echo esc_attr($markingId) ?>, 'click', function() {
                                        onSelectMarking(id<?php echo esc_attr($markingId) ?>);
                                    });

                                    wpGmapOverlays[<?php echo esc_attr($markingId) ?>] = circle<?php echo esc_attr($markingId) ?>;
                                <?php elseif ($marking['type'] == 'polygon') : $coordinates = Helper::coordinatesToArray($marking['coordinates']); ?>
                                    var polygon<?php echo esc_attr($markingId) ?> = new google.maps.Polygon({
                                        paths: [
                                            <?php 
                                                foreach ($coordinates as $coord) {
                                                    echo '{ lat: '.esc_attr($coord['lat']).', lng: '.esc_attr($coord['lon']).' },';
                                                }
                                            ?>
                                        ],
                                        strokeColor: "<?php echo esc_attr($marking['strokeColor']) ?>",
                                        strokeOpacity: <?php echo esc_attr($marking['strokeOpacity']) ?>,
                                        strokeWeight: <?php echo esc_attr($marking['strokeWeight']) ?>,
                                        fillColor: "<?php echo esc_attr($marking['fillColor']) ?>",
                                        fillOpacity: <?php echo esc_attr($marking['fillOpacity']) ?>,
                                        draggable: true,
                                        editable: true,
                                    });

                                    polygon<?php echo esc_attr($markingId) ?>.setMap(map);

                                    var id<?php echo esc_attr($markingId) ?> = '<?php echo esc_attr($markingId) ?>';
                                    var marker<?php echo esc_attr($markingId) ?> = {
                                        type: 'polygon',
                                        value: getPolygonCoordinates(polygon<?php echo esc_attr($markingId) ?>),
                                    };

                                    polygon<?php echo esc_attr($markingId) ?>.getPaths().forEach(function(path, index){
                                        google.maps.event.addListener(path, 'insert_at', function() {
                                            marker<?php echo esc_attr($markingId) ?>.value = getPolygonCoordinates(polygon<?php echo esc_attr($markingId) ?>);
                                            updateMarking(marker<?php echo esc_attr($markingId) ?>, id<?php echo esc_attr($markingId) ?>);
                                        });

                                        google.maps.event.addListener(path, 'remove_at', function() {
                                            marker<?php echo esc_attr($markingId) ?>.value = getPolygonCoordinates(polygon<?php echo esc_attr($markingId) ?>);
                                            updateMarking(marker<?php echo esc_attr($markingId) ?>, id<?php echo esc_attr($markingId) ?>);
                                        });

                                        google.maps.event.addListener(path, 'set_at', function() {
                                            marker<?php echo esc_attr($markingId) ?>.value = getPolygonCoordinates(polygon<?php echo esc_attr($markingId) ?>);
                                            updateMarking(marker<?php echo esc_attr($markingId) ?>, id<?php echo esc_attr($markingId) ?>);
                                        });
                                    });

                                    google.maps.event.addListener(polygon<?php echo esc_attr($markingId) ?>, 'dragend', function() {
                                        marker<?php echo esc_attr($markingId) ?>.value = getPolygonCoordinates(polygon<?php echo esc_attr($markingId) ?>);
                                        updateMarking(marker<?php echo esc_attr($markingId) ?>, id<?php echo esc_attr($markingId) ?>);
                                    });

                                    google.maps.event.addListener(polygon<?php echo esc_attr($markingId) ?>, 'click', function() {
                                        onSelectMarking(id<?php echo esc_attr($markingId) ?>);
                                    });

                                    wpGmapOverlays[<?php echo esc_attr($markingId) ?>] = polygon<?php echo esc_attr($markingId) ?>;
                                <?php elseif ($marking['type'] == 'polyline') : $coordinates = Helper::coordinatesToArray($marking['coordinates']); ?> 

                                    var polylineOptions = {
                                        path: [
                                            <?php 
                                                foreach ($coordinates as $coord) {
                                                    echo '{ lat: '.esc_attr($coord['lat']).', lng: '.esc_attr($coord['lon']).' },';
                                                }
                                            ?>
                                        ],
                                        strokeColor: "<?php echo esc_attr($marking['strokeColor']) ?>",
                                        strokeOpacity: <?php echo esc_attr($marking['strokeOpacity']) ?>,
                                        strokeWeight: <?php echo esc_attr($marking['strokeWeight']) ?>,
                                        draggable: true,
                                        editable: true,
                                    }

                                    <?php if ($marking['dashed'] == 1) : ?>
                                        var lineSymbol = {
                                            path: "M 0,-1 0,1",
                                            scale: 4,
                                            strokeColor: "<?php echo esc_attr($marking['strokeColor']) ?>",
                                            strokeOpacity: <?php echo esc_attr($marking['strokeOpacity']) ?>,
                                            strokeWeight: <?php echo esc_attr($marking['strokeWeight']) ?>,
                                        };

                                        polylineOptions.strokeOpacity = 0;
                                        polylineOptions.icons = [
                                            {
                                                icon: lineSymbol,
                                                offset: "0",
                                                repeat: "20px",
                                            },
                                        ];
                                    <?php endif; ?>

                                    var polyline<?php echo esc_attr($markingId) ?> = new google.maps.Polyline(polylineOptions);
                                    polyline<?php echo esc_attr($markingId) ?>.setMap(map);

                                    var id<?php echo esc_attr($markingId) ?> = '<?php echo esc_attr($markingId) ?>';
                                    var marker<?php echo esc_attr($markingId) ?> = {
                                        type: 'polyline',
                                        value: getPolylineCoordinates(polyline<?php echo esc_attr($markingId) ?>),
                                    };

                                    google.maps.event.addListener(polyline<?php echo esc_attr($markingId) ?>.getPath(), 'insert_at', function () {
                                        marker<?php echo esc_attr($markingId) ?>.value = getPolylineCoordinates(polyline<?php echo esc_attr($markingId) ?>);
                                        updateMarking(marker<?php echo esc_attr($markingId) ?>, id<?php echo esc_attr($markingId) ?>);
                                    });

                                    google.maps.event.addListener(polyline<?php echo esc_attr($markingId) ?>.getPath(), 'remove_at', function () {
                                        marker<?php echo esc_attr($markingId) ?>.value = getPolylineCoordinates(polyline<?php echo esc_attr($markingId) ?>);
                                        updateMarking(marker<?php echo esc_attr($markingId) ?>, id<?php echo esc_attr($markingId) ?>);
                                    });

                                    google.maps.event.addListener(polyline<?php echo esc_attr($markingId) ?>.getPath(), 'set_at', function () {
                                        marker<?php echo esc_attr($markingId) ?>.value = getPolylineCoordinates(polyline<?php echo esc_attr($markingId) ?>);
                                        updateMarking(marker<?php echo esc_attr($markingId) ?>, id<?php echo esc_attr($markingId) ?>);
                                    });

                                    google.maps.event.addListener(polyline<?php echo esc_attr($markingId) ?>, 'dragend', function() {
                                        marker<?php echo esc_attr($markingId) ?>.value = getPolylineCoordinates(this);
                                        updateMarking(marker<?php echo esc_attr($markingId) ?>, id<?php echo esc_attr($markingId) ?>);
                                    });

                                    google.maps.event.addListener(polyline<?php echo esc_attr($markingId) ?>, 'click', function() {
                                        onSelectMarking(id<?php echo esc_attr($markingId) ?>);
                                    });

                                    wpGmapOverlays[<?php echo esc_attr($markingId) ?>] = polyline<?php echo esc_attr($markingId) ?>;
                                <?php elseif ($marking['type'] == 'rectangle') : ?> 
                                    var rectangle<?php echo esc_attr($markingId) ?> = new google.maps.Rectangle({
                                        strokeColor: "<?php echo esc_attr($marking['strokeColor']) ?>",
                                        strokeOpacity: <?php echo esc_attr($marking['strokeOpacity']) ?>,
                                        strokeWeight: <?php echo esc_attr($marking['strokeWeight']) ?>,
                                        fillColor: "<?php echo esc_attr($marking['fillColor']) ?>",
                                        fillOpacity: <?php echo esc_attr($marking['fillOpacity']) ?>,
                                        map,
                                        bounds: {
                                            north: <?php echo esc_attr($marking['north']) ?>,
                                            south: <?php echo esc_attr($marking['south']) ?>,
                                            east: <?php echo esc_attr($marking['east']) ?>,
                                            west: <?php echo esc_attr($marking['west']) ?>,
                                        },
                                        draggable: true,
                                        editable: true,
                                    });

                                    var id<?php echo esc_attr($markingId) ?> = '<?php echo esc_attr($markingId) ?>';
                                    var marker<?php echo esc_attr($markingId) ?> = {
                                        type: 'rectangle',
                                        value: getRectangleCoordinates(rectangle<?php echo esc_attr($markingId) ?>)
                                    };

                                    google.maps.event.addListener(rectangle<?php echo esc_attr($markingId) ?>, 'bounds_changed', function() {
                                        marker<?php echo esc_attr($markingId) ?>.value = getRectangleCoordinates(this);
                                        updateMarking(marker<?php echo esc_attr($markingId) ?>, id<?php echo esc_attr($markingId) ?>);
                                    });

                                    google.maps.event.addListener(rectangle<?php echo esc_attr($markingId) ?>, 'click', function() {
                                        onSelectMarking(id<?php echo esc_attr($markingId) ?>);
                                    });

                                    wpGmapOverlays[<?php echo esc_attr($markingId) ?>] = rectangle<?php echo esc_attr($markingId) ?>;
                                <?php endif;

                            }
                        }
                    ?>
                    

                    var prependedCustomSettings = false;
                    function setSettingsToCustom() {
                        if (!prependedCustomSettings) {
                            jQuery('#wpgmap-settings option:selected').removeAttr('selected');
                            jQuery('#wpgmap-settings').prepend("<option value=''>Custom settings</option>").val('');
                            prependedCustomSettings = true;
                        }
                    }

                    map.addListener('zoom_changed', function () {
                        jQuery('#wpgmap-zoom-level').val(this.getZoom());
                        setSettingsToCustom();
                    });

                    map.addListener('center_changed', function () {
                        jQuery('#wpgmap-center-lat').val(this.getCenter().lat());
                        jQuery('#wpgmap-center-lon').val(this.getCenter().lng());
                        setSettingsToCustom();
                    });

                    jQuery('#wpgmap-zoom-level').change(function () {
                        map.setZoom(parseInt(jQuery(this).val()));
                    });

                    jQuery('#wpgmap-map-type').change(function () {
                        map.setMapTypeId(jQuery(this).val());
                    });
                }
            </script>
            <!-- map -->
        </div>
    </div>

    <div id="wp-gmap-markings"></div>
    
    <div class="wp-gmap-button-submit">
        <?php submit_button(null, 'primary', !$isDefaultSettingsApplied ? 'update' : 'submit'); ?>
    </div>

</form>

<script type="text/template" id="wp-gmap-template-marker">
    <div class="wp-gmap-marker" id="wp-gmap-marker-{id}">
        <div class="wp-gmap-marker-label">Marker <a href="" class="button-link wp-gmap-marker-remove">Remove</a> <a href="" class="button-link wp-gmap-marker-preview">Preview</a></div>
        <div class="wp-gmap-marker-body">
            <input type="hidden" name="data[markings][{id}][type]" value="marker">
            <ul class="wp-gmap-marker-body-list">
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-name">Name</label>
                    <input type="text" name="data[markings][{id}][name]" id="wpgmap-markings-{id}-name" value="{name}">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-lat">Lat</label>
                    <input type="text" name="data[markings][{id}][lat]" id="wpgmap-markings-{id}-lat" value="{value.lat}" class="wpgmap-disabled">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-lon">Lon</label>
                    <input type="text" name="data[markings][{id}][lon]" id="wpgmap-markings-{id}-lon" value="{value.lon}" class="wpgmap-disabled">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-icon">Icon URL</label>
                    <input type="text" name="data[markings][{id}][icon]" id="wpgmap-markings-{id}-icon" value="{options.icon}">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-postid">Link to post</label>
                    <select name="data[markings][{id}][postid]" class="wpgmap-post-list" id="wpgmap-markings-{id}-postid" data-val="{options.postid}">
                        <option value=""></option>
                        <?php foreach($posts as $post) : ?>
                            <option value="<?php echo intval($post->ID) ?>"><?php echo esc_html($post->post_title) ?></option>
                        <?php endforeach; ?>
                    </select>
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-openas">Open as</label>
                    <select name="data[markings][{id}][openas]" id="wpgmap-markings-{id}-openas" data-val="{options.openas}">
                        <option value="modal"<?php echo $currentMap && isset($currentMap->data->openas) && $currentMap->data->openas == 'modal' ? ' selected' : '' ?>>Modal</option>
                        <option value="window"<?php echo $currentMap && isset($currentMap->data->openas) && $currentMap->data->openas == 'window' ? ' selected' : '' ?>>Info window</option>
                    </select>
                </li>
            </ul>
        </div>
    </div>
</script>

<script type="text/template" id="wp-gmap-template-circle">
    <div class="wp-gmap-marker" id="wp-gmap-marker-{id}">
        <div class="wp-gmap-marker-label">Circle <a href="" class="button-link wp-gmap-marker-remove">Remove</a> <a href="" class="button-link wp-gmap-marker-preview">Preview</a></div>
        <div class="wp-gmap-marker-body">
            <input type="hidden" name="data[markings][{id}][type]" value="circle">
            <ul class="wp-gmap-marker-body-list">
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-name">Name</label>
                    <input type="text" name="data[markings][{id}][name]" id="wpgmap-markings-{id}-name" value="{name}">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-centerLat">Center lat</label>
                    <input type="text" name="data[markings][{id}][centerLat]" id="wpgmap-markings-{id}-centerLat" value="{value.center.lat}" class="wpgmap-disabled">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-centerLon">Center lon</label>
                    <input type="text" name="data[markings][{id}][centerLon]" id="wpgmap-markings-{id}-centerLon" value="{value.center.lon}" class="wpgmap-disabled">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-radius">Radius</label>
                    <input type="text" name="data[markings][{id}][radius]" id="wpgmap-markings-{id}-radius" value="{value.radius}" class="wpgmap-disabled">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-strokeColor">strokeColor</label>
                    <input type="color" name="data[markings][{id}][strokeColor]" id="wpgmap-markings-{id}-strokeColor" value="{options.strokeColor}">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-strokeOpacity">strokeOpacity</label>
                    <input type="number" step="any" name="data[markings][{id}][strokeOpacity]" id="wpgmap-markings-{id}-strokeOpacity" value="{options.strokeOpacity}">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-strokeWeight">strokeWeight</label>
                    <input type="number" step="any" name="data[markings][{id}][strokeWeight]" id="wpgmap-markings-{id}-strokeWeight" value="{options.strokeWeight}">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-fillColor">fillColor</label>
                    <input type="color" name="data[markings][{id}][fillColor]" id="wpgmap-markings-{id}-fillColor" value="{options.fillColor}">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-fillOpacity">fillOpacity</label>
                    <input type="number" step="any" name="data[markings][{id}][fillOpacity]" id="wpgmap-markings-{id}-fillOpacity" value="{options.fillOpacity}">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-postid">Link to post</label>
                    <select name="data[markings][{id}][postid]" class="wpgmap-post-list" id="wpgmap-markings-{id}-postid" data-val="{options.postid}">
                        <option value=""></option>
                        <?php foreach($posts as $post) : ?>
                            <option value="<?php echo intval($post->ID) ?>"><?php echo esc_html($post->post_title) ?></option>
                        <?php endforeach; ?>
                    </select>
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-openas">Open as</label>
                    <select name="data[markings][{id}][openas]" id="wpgmap-markings-{id}-openas" data-val="{options.openas}">
                        <option value="modal"<?php echo $currentMap && isset($currentMap->data->openas) && $currentMap->data->openas == 'modal' ? ' selected' : '' ?>>Modal</option>
                        <option value="window"<?php echo $currentMap && isset($currentMap->data->openas) && $currentMap->data->openas == 'window' ? ' selected' : '' ?>>Info window</option>
                    </select>
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-legend">Apply legend</label>
                    <select id="wpgmap-markings-{id}-legend" class="wpgmap-markings-legend-select"></select>
                </li>
            </ul>
        </div>
    </div>
</script>

<script type="text/template" id="wp-gmap-template-polygon">
    <div class="wp-gmap-marker" id="wp-gmap-marker-{id}">
        <div class="wp-gmap-marker-label">Polygon <a href="" class="button-link wp-gmap-marker-remove">Remove</a> <a href="" class="button-link wp-gmap-marker-preview">Preview</a></div>
        <div class="wp-gmap-marker-body">
            <input type="hidden" name="data[markings][{id}][type]" value="polygon">
            <ul class="wp-gmap-marker-body-list">
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-name">Name</label>
                    <input type="text" name="data[markings][{id}][name]" id="wpgmap-markings-{id}-name" value="{name}">
                </li>
                <li class="wp-gmap-marker-body-list-item is-full">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-coordinates">Coordinates</label>
                    <input type="text" name="data[markings][{id}][coordinates]" value="{value}" id="wpgmap-markings-{id}-coordinates" class="wpgmap-disabled">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-strokeColor">strokeColor</label>
                    <input type="color" name="data[markings][{id}][strokeColor]" id="wpgmap-markings-{id}-strokeColor" value="{options.strokeColor}">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-strokeOpacity">strokeOpacity</label>
                    <input type="number" step="any" name="data[markings][{id}][strokeOpacity]" id="wpgmap-markings-{id}-strokeOpacity" value="{options.strokeOpacity}">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-strokeWeight">strokeWeight</label>
                    <input type="number" step="any" name="data[markings][{id}][strokeWeight]" id="wpgmap-markings-{id}-strokeWeight" value="{options.strokeWeight}">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-fillColor">fillColor</label>
                    <input type="color" name="data[markings][{id}][fillColor]" id="wpgmap-markings-{id}-fillColor" value="{options.fillColor}">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-fillOpacity">fillOpacity</label>
                    <input type="number" step="any" name="data[markings][{id}][fillOpacity]" id="wpgmap-markings-{id}-fillOpacity" value="{options.fillOpacity}">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-postid">Link to post</label>
                    <select name="data[markings][{id}][postid]" class="wpgmap-post-list" id="wpgmap-markings-{id}-postid" data-val="{options.postid}">
                        <option value=""></option>
                        <?php foreach($posts as $post) : ?>
                            <option value="<?php echo intval($post->ID) ?>"><?php echo esc_html($post->post_title) ?></option>
                        <?php endforeach; ?>
                    </select>
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-openas">Open as</label>
                    <select name="data[markings][{id}][openas]" id="wpgmap-markings-{id}-openas" data-val="{options.openas}">
                        <option value="modal"<?php echo $currentMap && isset($currentMap->data->openas) && $currentMap->data->openas == 'modal' ? ' selected' : '' ?>>Modal</option>
                        <option value="window"<?php echo $currentMap && isset($currentMap->data->openas) && $currentMap->data->openas == 'window' ? ' selected' : '' ?>>Info window</option>
                    </select>
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-legend">Apply legend</label>
                    <select id="wpgmap-markings-{id}-legend" class="wpgmap-markings-legend-select"></select>
                </li>
            </ul>
        </div>
    </div>
</script>

<script type="text/template" id="wp-gmap-template-polyline">
    <div class="wp-gmap-marker" id="wp-gmap-marker-{id}">
        <div class="wp-gmap-marker-label">Polyline <a href="" class="button-link wp-gmap-marker-remove">Remove</a> <a href="" class="button-link wp-gmap-marker-preview">Preview</a></div>
        <div class="wp-gmap-marker-body">
            <input type="hidden" name="data[markings][{id}][type]" value="polyline">
            <ul class="wp-gmap-marker-body-list">
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-name">Name</label>
                    <input type="text" name="data[markings][{id}][name]" id="wpgmap-markings-{id}-name" value="{name}">
                </li>
                <li class="wp-gmap-marker-body-list-item is-full">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-coordinates">Coordinates</label>
                    <input type="text" name="data[markings][{id}][coordinates]" value="{value}" id="wpgmap-markings-{id}-coordinates" class="wpgmap-disabled">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-strokeColor">strokeColor</label>
                    <input type="color" name="data[markings][{id}][strokeColor]" id="wpgmap-markings-{id}-strokeColor" value="{options.strokeColor}">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-strokeOpacity">strokeOpacity</label>
                    <input type="number" step="any" name="data[markings][{id}][strokeOpacity]" id="wpgmap-markings-{id}-strokeOpacity" value="{options.strokeOpacity}">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-strokeWeight">strokeWeight</label>
                    <input type="number" step="any" name="data[markings][{id}][strokeWeight]" id="wpgmap-markings-{id}-strokeWeight" value="{options.strokeWeight}">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-dashed">Dashed</label>
                    <select name="data[markings][{id}][dashed]" id="wpgmap-markings-{id}-dashed" data-val="{options.dashed}">
                        <option value="1">True</option>
                        <option value="0" selected>False</option>
                    </select>
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-postid">Link to post</label>
                    <select name="data[markings][{id}][postid]" class="wpgmap-post-list" id="wpgmap-markings-{id}-postid" data-val="{options.postid}">
                        <option value=""></option>
                        <?php foreach($posts as $post) : ?>
                            <option value="<?php echo intval($post->ID) ?>"><?php echo esc_html($post->post_title) ?></option>
                        <?php endforeach; ?>
                    </select>
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-openas">Open as</label>
                    <select name="data[markings][{id}][openas]" id="wpgmap-markings-{id}-openas" data-val="{options.openas}">
                        <option value="modal"<?php echo $currentMap && isset($currentMap->data->openas) && $currentMap->data->openas == 'modal' ? ' selected' : '' ?>>Modal</option>
                        <option value="window"<?php echo $currentMap && isset($currentMap->data->openas) && $currentMap->data->openas == 'window' ? ' selected' : '' ?>>Info window</option>
                    </select>
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-legend">Apply legend</label>
                    <select id="wpgmap-markings-{id}-legend" class="wpgmap-markings-legend-select"></select>
                </li>
            </ul>
        </div>
    </div>
</script>

<script type="text/template" id="wp-gmap-template-rectangle">
    <div class="wp-gmap-marker" id="wp-gmap-marker-{id}">
        <div class="wp-gmap-marker-label">Rectangle <a href="" class="button-link wp-gmap-marker-remove">Remove</a> <a href="" class="button-link wp-gmap-marker-preview">Preview</a></div>
        <div class="wp-gmap-marker-body">
            <input type="hidden" name="data[markings][{id}][type]" value="rectangle">
            <ul class="wp-gmap-marker-body-list">
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-name">Name</label>
                    <input type="text" name="data[markings][{id}][name]" id="wpgmap-markings-{id}-name" value="{name}">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-north">North</label>
                    <input type="text" name="data[markings][{id}][north]" value="{value.north}" id="wpgmap-markings-{id}-north" class="wpgmap-disabled">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-south">South</label>
                    <input type="text" name="data[markings][{id}][south]" value="{value.south}" id="wpgmap-markings-{id}-south" class="wpgmap-disabled">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-east">East</label>
                    <input type="text" name="data[markings][{id}][east]" value="{value.east}" id="wpgmap-markings-{id}-east" class="wpgmap-disabled">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-west">West</label>
                    <input type="text" name="data[markings][{id}][west]" value="{value.west}" id="wpgmap-markings-{id}-west" class="wpgmap-disabled">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-strokeColor">strokeColor</label>
                    <input type="color" name="data[markings][{id}][strokeColor]" id="wpgmap-markings-{id}-strokeColor" value="{options.strokeColor}">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-strokeOpacity">strokeOpacity</label>
                    <input type="number" step="any" name="data[markings][{id}][strokeOpacity]" id="wpgmap-markings-{id}-strokeOpacity" value="{options.strokeOpacity}">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-strokeWeight">strokeWeight</label>
                    <input type="number" step="any" name="data[markings][{id}][strokeWeight]" id="wpgmap-markings-{id}-strokeWeight" value="{options.strokeWeight}">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-fillColor">fillColor</label>
                    <input type="color" name="data[markings][{id}][fillColor]" id="wpgmap-markings-{id}-fillColor" value="{options.fillColor}">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-fillOpacity">fillOpacity</label>
                    <input type="number" step="any" name="data[markings][{id}][fillOpacity]" id="wpgmap-markings-{id}-fillOpacity" value="{options.fillOpacity}">
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-postid">Link to post</label>
                    <select name="data[markings][{id}][postid]" class="wpgmap-post-list" id="wpgmap-markings-{id}-postid" data-val="{options.postid}">
                        <option value=""></option>
                        <?php foreach($posts as $post) : ?>
                            <option value="<?php echo intval($post->ID) ?>"><?php echo esc_html($post->post_title) ?></option>
                        <?php endforeach; ?>
                    </select>
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-openas">Open as</label>
                    <select name="data[markings][{id}][openas]" id="wpgmap-markings-{id}-openas" data-val="{options.openas}">
                        <option value="modal"<?php echo $currentMap && isset($currentMap->data->openas) && $currentMap->data->openas == 'modal' ? ' selected' : '' ?>>Modal</option>
                        <option value="window"<?php echo $currentMap && isset($currentMap->data->openas) && $currentMap->data->openas == 'window' ? ' selected' : '' ?>>Info window</option>
                    </select>
                </li>
                <li class="wp-gmap-marker-body-list-item">
                    <label class="wp-gmap-marker-body-list-item-name" for="wpgmap-markings-{id}-legend">Apply legend</label>
                    <select id="wpgmap-markings-{id}-legend" class="wpgmap-markings-legend-select"></select>
                </li>
            </ul>
        </div>
    </div>
</script>