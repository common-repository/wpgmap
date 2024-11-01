<?php
use WpGmap\core\Helper;
use WpGmap\core\model\MapData;

defined('WPGMAP') or die('Direct access not allowed!');

if (is_singular()) :

$mapId = $atts['id'];
$mapName = $atts['name'];
$mapHeight = $atts['height'];

$isShowLegends = isset($atts['legends']) && $atts['legends'] == 'true' ? true : false;
$isShowLists = isset($atts['list']) && $atts['list'] == 'true' ? true : false; 

if (!$mapId) {
    Helper::errorMessage('Map ID is undefined');
}

$mapDataModel = new MapData();
$currentMap = $mapDataModel->show($mapId);
if ($currentMap) {
    $currentMap->data = Helper::jsonDecode($currentMap->data);
}

if (!$currentMap) {
    Helper::errorMessage('Map not found');
}

$mapLegends = Helper::getLegends($currentMap->data->legends);
$mapPostIds = Helper::getPostIds($currentMap->data->markings);
$wpgmapPosts = $mapDataModel->getPostsByIds($mapPostIds);
$lists = Helper::getPostAndCategory($wpgmapPosts, $mapLegends);
?>
<script>
    if (typeof google !== 'object' || (typeof google === 'object' && typeof google.maps !== 'object')) {
        var script = document.createElement('script');
        script.onload = function () {
            google.maps.event.addDomListener(window, 'load', function () {
                initMapId<?php echo intval($currentMap->id) ?>();
            });
        };
        script.src = "https://maps.googleapis.com/maps/api/js?key=<?php echo esc_html($currentMap->data->apikey) ?>";
        document.head.appendChild(script);
    }

    window.wpGmapOverlays = {};
    window.wpGmapPostMapping = {};
    window.wpGmapHighlightColor = '<?php echo esc_html($currentMap->data->highlight_color) ?>';

    function openModal(id) {
        var modalId = 'wpgmap-modal-id-' + id;
        var modalDom = document.getElementById(modalId);

        if (modalDom) {
            modalDom.classList.add('active');
        }
    }

    function highlightList(postId) {
        var items = document.querySelectorAll('.wpgmap-ui-list-item-body[data-post-id="'+postId+'"]');

        for (var i = 0; i < items.length; i++) {
            items[i].classList.add('active');
        }
    }

    function unhighlightList(postId) {
        var items = document.querySelectorAll('.wpgmap-ui-list-item-body[data-post-id="'+postId+'"]');
        for (var i = 0; i < items.length; i++) {
            items[i].classList.remove('active');
        }
    }

    function initMapId<?php echo intval($currentMap->id) ?>() {
        var map = new google.maps.Map(document.getElementById("wpgmap-id-<?php echo intval($currentMap->id) ?>"), {
            center: {
                lat: <?php echo esc_attr($currentMap->data->center_lat) ?>,
                lng: <?php echo esc_attr($currentMap->data->center_lon) ?>
            },
            zoom: <?php echo intval($currentMap->data->zoom) ?>,
            mapTypeId: '<?php echo esc_html($currentMap->data->mapTypeId) ?>',
            disableDefaultUI: <?php echo $currentMap->data->disable_ui == 0 ? 'false' : 'true' ?>,
            <?php if ($currentMap->data->style) : ?>
            styles: <?php echo stripslashes($currentMap->data->style) ?>
            <?php endif; ?>
        });

        <?php 
            if ($currentMap && isset($currentMap->data->markings) && count($currentMap->data->markings) > 0) {
                foreach($currentMap->data->markings as $markingId => $marking) {
                    $post = Helper::getPostById($marking['postid'], $wpgmapPosts);

                    if ($marking['openas'] == 'window') : ?>
                        var infowindow<?php echo esc_attr($markingId) ?> = new google.maps.InfoWindow({
                            content: `<div class="wpgmap-google-window"><h6 class="wpgmap-google-window-title"><?php echo $post ? wp_kses_post($post->post_title): '' ?></h6><?php echo $post ? wp_kses_post($post->post_content) : '' ?></div>`
                        });
                    <?php endif; ?>
                    
                    <?php if ($marking['postid'] > 0) : ?>
                    if (!wpGmapPostMapping.hasOwnProperty(<?php echo esc_attr($marking['postid']) ?>)) {
                        wpGmapPostMapping[<?php echo esc_attr($marking['postid']) ?>] = [];
                    }
                    wpGmapPostMapping[<?php echo esc_attr($marking['postid']) ?>].push(<?php echo esc_attr($markingId) ?>);
                    <?php endif; ?>

                    <?php
                    if ($marking['type'] == 'marker') : ?>
                        var marker<?php echo esc_attr($markingId) ?> = new google.maps.Marker({
                            position: {
                                lat: <?php echo esc_attr($marking['lat']) ?>,
                                lng: <?php echo esc_attr($marking['lon']) ?>
                            },
                            map: map,
                            title: "<?php echo esc_attr($marking['name']) ?>",
                            icon: "<?php echo esc_url($marking['icon']) ?>"
                        });

                        google.maps.event.addListener(marker<?php echo esc_attr($markingId) ?>, 'click', function() {
                            <?php if ($marking['openas'] == 'modal') : ?>
                                openModal(<?php echo esc_attr($marking['postid']) ?>);
                            <?php elseif ($marking['openas'] == 'window') : ?>
                                infowindow<?php echo esc_attr($markingId) ?>.open(map, marker<?php echo esc_attr($markingId) ?>);
                            <?php endif; ?>
                        });

                        google.maps.event.addListener(marker<?php echo esc_attr($markingId) ?>, 'mouseover', function() {
                            highlightList(<?php echo esc_attr($marking['postid']) ?>);
                        });

                        google.maps.event.addListener(marker<?php echo esc_attr($markingId) ?>, 'mouseout', function() {
                            unhighlightList(<?php echo esc_attr($marking['postid']) ?>);
                        });

                        wpGmapOverlays[<?php echo esc_attr($markingId) ?>] = {
                            instance: marker<?php echo esc_attr($markingId) ?>,
                            options: { icon: "<?php echo esc_url($marking['icon']) ?>" },
                            type: '<?php echo esc_attr($marking['type']) ?>',
                        };
                    <?php elseif ($marking['type'] == 'circle') : ?>
                        var circle<?php echo esc_attr($markingId) ?> = new google.maps.Circle({
                            strokeColor: "<?php echo esc_attr($marking['strokeColor']) ?>",
                            strokeOpacity: <?php echo esc_attr($marking['strokeOpacity']) ?>,
                            strokeWeight: <?php echo esc_attr($marking['strokeWeight']) ?>,
                            fillColor: "<?php echo esc_attr($marking['fillColor']) ?>",
                            fillOpacity: <?php echo esc_attr($marking['fillOpacity']) ?>,
                            map,
                            center: { lat: <?php echo $marking['centerLat'] ?>, lng: <?php echo $marking['centerLon'] ?> },
                            radius: <?php echo $marking['radius'] ?>,
                        });

                        google.maps.event.addListener(circle<?php echo esc_attr($markingId) ?>, 'click', function() {
                            <?php if ($marking['openas'] == 'modal') : ?>
                                openModal(<?php echo esc_attr($marking['postid']) ?>);
                            <?php elseif ($marking['openas'] == 'window') : ?>
                                infowindow<?php echo esc_attr($markingId) ?>.setContent(infowindow<?php echo esc_attr($markingId) ?>.content);
                                infowindow<?php echo esc_attr($markingId) ?>.setPosition(this.getCenter());
                                infowindow<?php echo esc_attr($markingId) ?>.open(map);
                            <?php endif; ?>
                        });

                        google.maps.event.addListener(circle<?php echo esc_attr($markingId) ?>, 'mouseover', function() {
                            highlightList(<?php echo esc_attr($marking['postid']) ?>);
                        });

                        google.maps.event.addListener(circle<?php echo esc_attr($markingId) ?>, 'mouseout', function() {
                            unhighlightList(<?php echo esc_attr($marking['postid']) ?>);
                        });

                        wpGmapOverlays[<?php echo esc_attr($markingId) ?>] = {
                            instance: circle<?php echo esc_attr($markingId) ?>, 
                            options: {
                                strokeColor: "<?php echo esc_attr($marking['strokeColor']) ?>",
                                strokeOpacity: <?php echo esc_attr($marking['strokeOpacity']) ?>,
                                strokeWeight: <?php echo esc_attr($marking['strokeWeight']) ?>,
                                fillColor: "<?php echo esc_attr($marking['fillColor']) ?>",
                                fillOpacity: <?php echo esc_attr($marking['fillOpacity']) ?>,
                            },
                            type: '<?php echo esc_attr($marking['type']) ?>',
                        };
                    <?php elseif ($marking['type'] == 'polygon') : $coordinates = Helper::coordinatesToArray($marking['coordinates']); ?>
                        var bounds<?php echo esc_attr($markingId) ?> = new google.maps.LatLngBounds();
                        var polygonCoords<?php echo esc_attr($markingId) ?> = [];
                        <?php foreach ($coordinates as $coord) : ?>
                            polygonCoords<?php echo esc_attr($markingId) ?>.push(new google.maps.LatLng(<?php echo esc_attr($coord['lat']) ?>, <?php echo esc_attr($coord['lon']) ?>));
                        <?php endforeach; ?>
                        for (i = 0; i < polygonCoords<?php echo esc_attr($markingId) ?>.length; i++) {
                            bounds<?php echo esc_attr($markingId) ?>.extend(polygonCoords<?php echo esc_attr($markingId) ?>[i]);
                        }

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
                        });

                        polygon<?php echo esc_attr($markingId) ?>.setMap(map);

                        google.maps.event.addListener(polygon<?php echo esc_attr($markingId) ?>, 'click', function() {
                            <?php if ($marking['openas'] == 'modal') : ?>
                                openModal(<?php echo esc_attr($marking['postid']) ?>);
                            <?php elseif ($marking['openas'] == 'window') : ?>
                                infowindow<?php echo esc_attr($markingId) ?>.setContent(infowindow<?php echo esc_attr($markingId) ?>.content);
                                infowindow<?php echo esc_attr($markingId) ?>.setPosition(bounds<?php echo esc_attr($markingId) ?>.getCenter());
                                infowindow<?php echo esc_attr($markingId) ?>.open(map);
                            <?php endif; ?>
                        });

                        google.maps.event.addListener(polygon<?php echo esc_attr($markingId) ?>, 'mouseover', function() {
                            highlightList(<?php echo esc_attr($marking['postid']) ?>);
                        });

                        google.maps.event.addListener(polygon<?php echo esc_attr($markingId) ?>, 'mouseout', function() {
                            unhighlightList(<?php echo esc_attr($marking['postid']) ?>);
                        });

                        wpGmapOverlays[<?php echo esc_attr($markingId) ?>] = {
                            instance: polygon<?php echo esc_attr($markingId) ?>,
                            options: {
                                strokeColor: "<?php echo esc_attr($marking['strokeColor']) ?>",
                                strokeOpacity: <?php echo esc_attr($marking['strokeOpacity']) ?>,
                                strokeWeight: <?php echo esc_attr($marking['strokeWeight']) ?>,
                                fillColor: "<?php echo esc_attr($marking['fillColor']) ?>",
                                fillOpacity: <?php echo esc_attr($marking['fillOpacity']) ?>,
                            },
                            type: '<?php echo esc_attr($marking['type']) ?>',
                        };
                    <?php elseif ($marking['type'] == 'polyline') : $coordinates = Helper::coordinatesToArray($marking['coordinates']); ?> 
                        var bounds<?php echo esc_attr($markingId) ?> = new google.maps.LatLngBounds();
                        var polylineCoords<?php echo esc_attr($markingId) ?> = [];
                        <?php
                            $selectedCoord = $coordinates[0];
                            if (count($coordinates) > 2) {
                                $selectedCoord = $coordinates[round(count($coordinates) / 2) - 1];
                            }
                        ?>
                        polylineCoords<?php echo esc_attr($markingId) ?>.push(new google.maps.LatLng(<?php echo esc_attr($selectedCoord['lat']) ?>, <?php echo esc_attr($selectedCoord['lon']) ?>));
                        for (i = 0; i < polylineCoords<?php echo esc_attr($markingId) ?>.length; i++) {
                            bounds<?php echo esc_attr($markingId) ?>.extend(polylineCoords<?php echo esc_attr($markingId) ?>[i]);
                        }

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

                        google.maps.event.addListener(polyline<?php echo esc_attr($markingId) ?>, 'click', function() {
                            <?php if ($marking['openas'] == 'modal') : ?>
                                openModal(<?php echo esc_attr($marking['postid']) ?>);
                            <?php elseif ($marking['openas'] == 'window') : ?>
                                infowindow<?php echo esc_attr($markingId) ?>.setContent(infowindow<?php echo esc_attr($markingId) ?>.content);
                                infowindow<?php echo esc_attr($markingId) ?>.setPosition(bounds<?php echo esc_attr($markingId) ?>.getCenter());
                                infowindow<?php echo esc_attr($markingId) ?>.open(map);
                            <?php endif; ?>
                        });

                        google.maps.event.addListener(polyline<?php echo esc_attr($markingId) ?>, 'mouseover', function() {
                            highlightList(<?php echo esc_attr($marking['postid']) ?>);
                        });

                        google.maps.event.addListener(polyline<?php echo esc_attr($markingId) ?>, 'mouseout', function() {
                            unhighlightList(<?php echo esc_attr($marking['postid']) ?>);
                        });

                        wpGmapOverlays[<?php echo esc_attr($markingId) ?>] = {
                            instance: polyline<?php echo esc_attr($markingId) ?>,
                            options: {
                                strokeColor: "<?php echo esc_attr($marking['strokeColor']) ?>",
                                strokeOpacity: <?php echo esc_attr($marking['strokeOpacity']) ?>,
                                strokeWeight: <?php echo esc_attr($marking['strokeWeight']) ?>,
                            },
                            type: '<?php echo esc_attr($marking['type']) ?>',
                            dashed: <?php echo $marking['dashed'] == 1 ? 'true' : 'false' ?>
                        };
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
                            }
                        });

                        google.maps.event.addListener(rectangle<?php echo esc_attr($markingId) ?>, 'click', function() {
                            <?php if ($marking['openas'] == 'modal') : ?>
                                openModal(<?php echo esc_attr($marking['postid']) ?>);
                            <?php elseif ($marking['openas'] == 'window') : ?>
                                infowindow<?php echo esc_attr($markingId) ?>.setContent(infowindow<?php echo esc_attr($markingId) ?>.content);
                                infowindow<?php echo esc_attr($markingId) ?>.setPosition(this.bounds.getCenter());
                                infowindow<?php echo esc_attr($markingId) ?>.open(map);
                            <?php endif; ?>
                        });

                        google.maps.event.addListener(rectangle<?php echo esc_attr($markingId) ?>, 'mouseover', function() {
                            highlightList(<?php echo esc_attr($marking['postid']) ?>);
                        });

                        google.maps.event.addListener(rectangle<?php echo esc_attr($markingId) ?>, 'mouseout', function() {
                            unhighlightList(<?php echo esc_attr($marking['postid']) ?>);
                        });

                        wpGmapOverlays[<?php echo esc_attr($markingId) ?>] = {
                            instance: rectangle<?php echo esc_attr($markingId) ?>,
                            options: {
                                strokeColor: "<?php echo esc_attr($marking['strokeColor']) ?>",
                                strokeOpacity: <?php echo esc_attr($marking['strokeOpacity']) ?>,
                                strokeWeight: <?php echo esc_attr($marking['strokeWeight']) ?>,
                                fillColor: "<?php echo esc_attr($marking['fillColor']) ?>",
                                fillOpacity: <?php echo esc_attr($marking['fillOpacity']) ?>,
                            },
                            type: '<?php echo esc_attr($marking['type']) ?>',
                        };
                    <?php endif;

                }
            }
        ?>
    }
</script>

<div class="wpgmap-ui">
    <?php if ($isShowLists) : ?>
    <div class="wpgmap-ui-list">
        <ul class="wpgmap-ui-list-container" style="height: <?php echo esc_html($mapHeight) ?>">

            <?php foreach ($lists as $list) : ?>
            <li class="wpgmap-ui-list-item">
                <div class="wpgmap-ui-list-item-header" style="color: <?php echo esc_attr($list['color']) ?>"><span class="wpgmap-ui-list-item-header-color" style="background-color: <?php echo esc_attr($list['color']) ?>"></span> <?php echo esc_attr($list['name']) ?></div>

                <?php $counter = 1; foreach ($list['posts'] as $post) : $tag = Helper::showTag($post->ID); ?>
                <div class="wpgmap-ui-list-item-body" data-post-id="<?php echo intval($post->ID) ?>">
                    <div class="wpgmap-ui-list-item-body-block<?php echo $tag ? '' : ' no-secondary' ?>">
                        <div class="wpgmap-ui-list-item-body-block-primary">
                            <span class="wpgmap-ui-list-item-body-block-number"><?php echo intval($counter); $counter++; ?>.</span> <span class="wpgmap-ui-list-item-body-block-title"><?php echo wp_kses_post($post->post_title) ?></span> - <span class="wpgmap-ui-list-item-body-block-subtitle"><?php echo wp_kses_post($post->post_excerpt) ?></span>
                        </div>
                        <div class="wpgmap-ui-list-item-body-block-secondary">
                            <span class="wpgmap-ui-list-item-body-block-text">
                                <?php echo esc_html($tag); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </li>
            <?php endforeach; ?>

        </ul>
        <button class="wpgmap-ui-list-expand-collapse" style="height: <?php echo esc_attr($mapHeight) ?>">
            <span class="wpgmap-ui-list-expand-collapse-left"></span>
            <span class="wpgmap-ui-list-expand-collapse-right"></span>
        </button>
    </div>
    <?php endif; ?>

    <div class="wpgmap-ui-map<?php echo !$isShowLists ? ' wpgmap-ui-map-full-width' : '' ?>">
        <?php if ($isShowLegends) : ?>
        <?php if (count($mapLegends) > 0) : ?>
        <div class="wpgmap-ui-map-legend active">
            <div class="wpgmap-ui-map-legend-title">Legend <span class="wpgmap-ui-map-legend-expand-collapse"></span></div>
            
            <ul class="wpgmap-ui-map-legend-list">
                <?php foreach ($mapLegends as $mapLegend) : ?>
                <li class="wpgmap-ui-map-legend-list-item<?php echo isset($mapLegend['bordered']) ? ' is--bordered' : '' ?><?php echo isset($mapLegend['dashed']) ? ' is--dashed' : '' ?>">
                    <div class="wpgmap-ui-map-legend-list-item-field" data-color="<?php echo esc_attr($mapLegend['color']) ?>"><span class="wpgmap-checkbox-uncheck-icon"></span><span class="wpgmap-checkbox-check-icon"></span></div>
                    <div class="wpgmap-ui-map-legend-list-item-color" style="<?php echo isset($mapLegend['bordered']) || isset($mapLegend['dashed']) ? 'border-color' : 'background-color' ?>: <?php echo esc_attr($mapLegend['color']) ?>;"></div>
                    <div class="wpgmap-ui-map-legend-list-item-label"><?php echo esc_html($mapLegend['name']) ?></div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        <div class="wpgmap-ui-map-google" id="wpgmap-id-<?php echo intval($currentMap->id) ?>" style="height: <?php echo esc_html($mapHeight) ?>"></div>
    </div>
</div>

<?php
    add_action('wp_footer', function () use ($wpgmapPosts) {
        if(count($wpgmapPosts) > 0) : foreach($wpgmapPosts as $post) : ?>
            <div class="wpgmap-ui-modal" id="wpgmap-modal-id-<?php echo $post->ID ?>">
                <div class="wpgmap-ui-modal-wrapper">
                    <div class="wpgmap-ui-modal-title">
                        <?php echo wp_kses_post($post->post_title) ?> <button class="wpgmap-ui-modal-close">Close</button>
                    </div>
                    <div class="wpgmap-ui-modal-body">
                        <?php echo wp_kses_post($post->post_content) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; endif;
    });
?>

<?php endif; ?>