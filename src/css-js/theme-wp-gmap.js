(function(){
    function registerModalCloseEvent() {
        var closeButtons = document.querySelectorAll('.wpgmap-ui-modal-close');
        for (var i = 0; i < closeButtons.length; i++) {
            closeButtons[i].addEventListener('click', function(event) {
                event.preventDefault();
                this.closest('.wpgmap-ui-modal').classList.remove('active');
            });
        }
    }

    function legendFieldCheckUncheck() {
        var legendFields = document.querySelectorAll('.wpgmap-ui-map-legend-list-item-field');
        for (var i = 0; i < legendFields.length; i++) {
            legendFields[i].addEventListener('click', function(event) {
                event.preventDefault();
                this.classList.toggle('active');
            });
        }
    }

    function legendFieldCollapseExpand() {
        var icons = document.querySelectorAll('.wpgmap-ui-map-legend-expand-collapse');
        for (var i = 0; i < icons.length; i++) {
            icons[i].addEventListener('click', function(event) {
                event.preventDefault();
                this.closest('.wpgmap-ui-map-legend').classList.toggle('active');
            });
        }
    }

    function onSelectListItem() {
        var items = document.querySelectorAll('.wpgmap-ui-list-item-body');
        var removeActiveClassFromOthers = function (excludeElement) {
            for (var i = 0; i < items.length; i++) {
                if (items[i] !== excludeElement) {
                    items[i].classList.remove('active');
                }
            }
        }

        for (var i = 0; i < items.length; i++) {
            items[i].addEventListener('click', function(event) {
                event.preventDefault();
                var postId = this.getAttribute('data-post-id');

                removeActiveClassFromOthers(this);
                this.classList.toggle('active');

                resetOverlayscolor();
                if (this.classList.contains('active')) {
                    highlightConnectedOverlay(postId);
                }
            });
        }
    }

    function resetOverlayscolor() {
        for(overlayId in wpGmapOverlays) {
            var overlay = wpGmapOverlays[overlayId];

            if (overlay.dashed) {
                var lineSymbol = {
                    path: "M 0,-1 0,1",
                    scale: 4,
                    strokeColor: overlay.options.strokeColor,
                    strokeOpacity: overlay.options.strokeOpacity,
                    strokeWeight: overlay.options.strokeWeight,
                };

                var options = {
                    icons: [
                        {
                            icon: lineSymbol,
                            offset: "0",
                            repeat: "20px",
                        },
                    ]
                }
                
                overlay.instance.setOptions(options);
            } else if (overlay.type !== 'marker') {
                var options = {};
                if (overlay.options.strokeColor) {
                    options.strokeColor = overlay.options.strokeColor;
                }
                if (overlay.options.fillColor) {
                    options.fillColor = overlay.options.fillColor;
                }
                overlay.instance.setOptions(options);
            }
        }
    }

    function highlightConnectedOverlay(postId) {
        var highlightColor = wpGmapHighlightColor;

        if (wpGmapPostMapping[postId]) {
            for (var j = 0; j < wpGmapPostMapping[postId].length; j++) {
                var overlay = wpGmapOverlays[wpGmapPostMapping[postId][j]];

                if (overlay.dashed) {
                    var lineSymbol = {
                        path: "M 0,-1 0,1",
                        scale: 4,
                        strokeColor: highlightColor,
                        strokeOpacity: overlay.options.strokeOpacity,
                        strokeWeight: overlay.options.strokeWeight,
                    };

                    var options = {
                        icons: [
                            {
                                icon: lineSymbol,
                                offset: "0",
                                repeat: "20px",
                            },
                        ]
                    }

                    overlay.instance.setOptions(options);
                } else if (overlay.type !== 'marker') {
                    var options = {};
                    if (overlay.options.strokeColor) {
                        options.strokeColor = highlightColor;
                    }
                    if (overlay.options.fillColor) {
                        options.fillColor = highlightColor;
                    }
                    overlay.instance.setOptions(options);
                }
            }
        }
    }

    function legendCheckUncheck() {
        var fields = document.querySelectorAll('.wpgmap-ui-map-legend-list-item-field');

        for (var i = 0; i < fields.length; i++) {
            fields[i].addEventListener('click', function(event) {
                event.preventDefault();
                
                showHideOverlays();
            });
        }
    }

    function showHideOverlays() {
        var fields = document.querySelectorAll('.wpgmap-ui-map-legend-list-item-field');
        var selectedColors = [];

        for (var i = 0; i < fields.length; i++) {
            var isChecked = fields[i].classList.contains('active');
            if (isChecked) {
                selectedColors.push(fields[i].getAttribute('data-color'));
            }
        }

        if (selectedColors.length === 0) {
            showAllOverlays();

            return false;
        }

        var showOverlays = [];
        for (var j = 0; j < selectedColors.length; j++) {
            var overlayIds = getOverlayIdsByColor(selectedColors[j]);
            
            if (overlayIds.length > 0) {
                showOverlays = showOverlays.concat(overlayIds);
            }
        }

        showOverlays = uniques(showOverlays);
        onlyShowSelectedOverlays(showOverlays);
    }

    function getOverlayIdsByColor(color) {
        var result = [];

        for (var instanceId in wpGmapOverlays) {
            if (wpGmapOverlays.hasOwnProperty(instanceId)) {
                if (wpGmapOverlays[instanceId].options.strokeColor === color) {
                    result.push(instanceId);
                }
            }
        }

        return result;
    }

    function uniques(arr) {
        var a = [];

        for (var i = 0, l = arr.length; i < l; i++) {
            if (a.indexOf(arr[i]) === -1 && arr[i] !== '') {
                a.push(arr[i]);
            }
        }
            
        return a;
    }

    function onlyShowSelectedOverlays(overlays) {
        for (var instanceId in wpGmapOverlays) {
            if (wpGmapOverlays.hasOwnProperty(instanceId)) {
                if (overlays.includes(instanceId)) {
                    wpGmapOverlays[instanceId].instance.setVisible(true);
                } else {
                    wpGmapOverlays[instanceId].instance.setVisible(false);
                }
            }
        }
    }

    function showAllOverlays() {
        for (var instanceId in wpGmapOverlays) {
            if (wpGmapOverlays.hasOwnProperty(instanceId)) {
                wpGmapOverlays[instanceId].instance.setVisible(true);
            }
        }
    }

    function listExpandCollapsed() {
        var elements = document.querySelectorAll('.wpgmap-ui-list-expand-collapse');

        for (var i = 0; i < elements.length; i++) {
            elements[i].addEventListener('click', function(event) {
                event.preventDefault();
                this.closest('.wpgmap-ui').classList.toggle('is-collapsed');
            });
        }
    }

    registerModalCloseEvent();
    legendFieldCheckUncheck();
    legendFieldCollapseExpand();
    onSelectListItem();
    legendCheckUncheck();
    listExpandCollapsed();
})();