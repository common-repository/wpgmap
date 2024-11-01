(function ($) {
    var template = {
        marker: $('#wp-gmap-template-marker').html(),
        circle: $('#wp-gmap-template-circle').html(),
        polygon: $('#wp-gmap-template-polygon').html(),
        polyline: $('#wp-gmap-template-polyline').html(),
        rectangle: $('#wp-gmap-template-rectangle').html(),
    }

    function watchSettingsDropdown() {
        $('#wpgmap-settings').change(function() {
            var isConfirmed = confirm('Your changes will be lost. Are you sure?');
            if (isConfirmed) {
                var settingsId = $(this).val();
                var formUrl = $(this).closest('form').attr('action');

                if (settingsId) {
                    location.href = formUrl + '&settings=' + settingsId;
                }
            }
        });
    }

    function addLegends() {
        $('html').on('click', '.add-more-legend', function(e) {
            e.preventDefault();

            var total = $('.wpgmap-legend-list li').length;
            if (total >= 2) {
                alert(atob('RlJFRSB2ZXJzaW9uIGRvZXMgbm90IGFsbG93IG1vcmUgdGhhbiAyIGxlZ2VuZHM='));
                return false;
            }

            $('.wpgmap-legend-list li:first').clone().appendTo('.wpgmap-legend-list');
            $('.wpgmap-legend-list li:last').find('input').val('');
            $('.wpgmap-legend-list li:last input:first').focus();
        });
    }

    function deleteConfirmation() {
        $('html').on('click', 'input[value="Delete"]', function (e) {
            var isConfirmed = confirm('Are you sure?');
            if (!isConfirmed) {
                e.preventDefault();
            }
        })
    }

    function removeLegend() {
        $('html').on('click', '.remove-legend', function() {
            var count = $(this).closest('ul').find('li').length;
            if (count <= 1) {
                return false;
            }

            var isConfirmed = confirm('Are you sure?');
            if (isConfirmed) {
                $(this).closest('li').remove();
            }
        });
    }

    function onChangeLegend() {
        $('html').on('change', '.wpgmap-markings-legend-select', function () {
            var idString = $(this).closest('.wp-gmap-marker').attr('id');
            var id = idString.replace('wp-gmap-marker-', '');
            var value = $(this).val();
            var text = $(this).find('option[value="'+value+'"]').text();
            var option = null;

            if (text.indexOf('::') > -1) {
                var textArray = text.split('::');
                option = textArray[1];
            }

            $('#wpgmap-markings-'+id+'-strokeColor').val(value);
            if ($('#wpgmap-markings-'+id+'-fillColor').length > 0) {
                $('#wpgmap-markings-'+id+'-fillColor').val(value);
            }

            if (option === 'dashed') {
                if ($('#wpgmap-markings-'+id+'-dashed').length > 0) {
                    $('#wpgmap-markings-'+id+'-dashed').val(1);
                }
            } else if (option === 'bordered') {
                if ($('#wpgmap-markings-'+id+'-fillOpacity').length > 0) {
                    $('#wpgmap-markings-'+id+'-fillOpacity').val(0);
                }
            }
        });
    }

    function onClickMarkerRemove() {
        $('html').on('click', '.wp-gmap-marker-remove', function(e) {
            e.preventDefault();
    
            var isConfirmed = confirm('Are you sure you want to remove?');
            if (!isConfirmed) {
                return false;
            }
            
            var idString = $(this).closest('.wp-gmap-marker').attr('id');
            var id = idString.replace('wp-gmap-marker-', '');
            
            if (wpGmapOverlays[id]) {
                if (wpGmapOverlays[id].hasOwnProperty('overlay')) {
                    wpGmapOverlays[id].overlay.setMap(null);
                } else {
                    wpGmapOverlays[id].setMap(null);
                }
                delete wpGmapOverlays[id];
    
                for (var i = 0; i < wpGmapMarkings.length; i++) {
                    if (wpGmapMarkings[i].id == id) {
                        wpGmapMarkings.splice(i, 1);
                    }
                }
    
                wpGmap();
            }
        });
    }

    function onClickMarkerPreview() {
        $('html').on('click', '.wp-gmap-marker-preview', function(e) {
            e.preventDefault();
            
            var idString = $(this).closest('.wp-gmap-marker').attr('id');
            var id = idString.replace('wp-gmap-marker-', '');
            
            if (wpGmapOverlays[id]) {
                for (var i = 0; i < wpGmapMarkings.length; i++) {
                    if (wpGmapMarkings[i].id == id) {
                        var options = {};
                        options.fillColor = wpGmapMarkings[i].options.fillColor || null;
                        options.fillOpacity = wpGmapMarkings[i].options.fillOpacity || null;
                        options.strokeColor = wpGmapMarkings[i].options.strokeColor || null;
                        options.strokeOpacity = wpGmapMarkings[i].options.strokeOpacity || null;
                        options.strokeWeight = wpGmapMarkings[i].options.strokeWeight || null;
                        options.icon = wpGmapMarkings[i].options.icon || null;
    
                        if (wpGmapMarkings[i].type === 'polyline' && wpGmapMarkings[i].options.dashed == '1') {
                            var lineSymbol = {
                                path: "M 0,-1 0,1",
                                scale: 4,
                                strokeColor: options.strokeColor,
                                strokeOpacity: options.strokeOpacity,
                                strokeWeight: options.strokeWeight,
                            };
    
                            options.strokeOpacity = 0;
                            options.icons = [
                                {
                                    icon: lineSymbol,
                                    offset: "0",
                                    repeat: "20px",
                                },
                            ];
    
                            delete options.strokeColor;
                            delete options.strokeWeight;
                        }
    
                        if (wpGmapOverlays[id].hasOwnProperty('overlay')) {
                            wpGmapOverlays[id].overlay.setOptions(options);
                        } else {
                            wpGmapOverlays[id].setOptions(options);
                        }
                    }
                }
            }
        });
    }

    function onChangeMarkerBlockInputs() {
        $('html').on('change', '.wp-gmap-marker input, .wp-gmap-marker select', function() {
            var idString = $(this).closest('.wp-gmap-marker').attr('id');
            var id = idString.replace('wp-gmap-marker-', '');
            var result = {};
    
            $(this).closest('.wp-gmap-marker-body-list').find('input, select').each(function(index, element) {
                var elementId = $(element).attr('id');
                var elementValue = $(element).val();
    
                var elementIdArray = elementId.split('-');
                var key = elementIdArray[elementIdArray.length - 1];
    
                result[key] = elementValue;
            });
    
            var index = -1;
            for (var i = 0; i < wpGmapMarkings.length; i++) {
                if (wpGmapMarkings[i].id == id) {
                    index = i;
                }
            }
    
            if (result.hasOwnProperty('name')) {
                wpGmapMarkings[index].name = result.name;
            }
    
            if (result.hasOwnProperty('fillColor')) {
                wpGmapMarkings[index].options.fillColor = result.fillColor;
            }
    
            if (result.hasOwnProperty('fillOpacity')) {
                wpGmapMarkings[index].options.fillOpacity = result.fillOpacity;
            }
    
            if (result.hasOwnProperty('strokeColor')) {
                wpGmapMarkings[index].options.strokeColor = result.strokeColor;
            }
    
            if (result.hasOwnProperty('strokeOpacity')) {
                wpGmapMarkings[index].options.strokeOpacity = result.strokeOpacity;
            }
    
            if (result.hasOwnProperty('name')) {
                wpGmapMarkings[index].options.strokeWeight = result.strokeWeight;
            }
    
            if (result.hasOwnProperty('icon')) {
                wpGmapMarkings[index].options.icon = result.icon;
            }
    
            if (result.hasOwnProperty('dashed')) {
                wpGmapMarkings[index].options.dashed = result.dashed;
            }
    
            if (result.hasOwnProperty('postid')) {
                wpGmapMarkings[index].options.postid = result.postid;
            }
    
            if (result.hasOwnProperty('openas')) {
                wpGmapMarkings[index].options.openas = result.openas;
            }
    
            updateRawData();
        });
    }

    function nano(template, data) {
        return template.replace(/\{([\w\.]*)\}/g, function (str, key) {
            var keys = key.split("."),
                v = data[keys.shift()];
            for (var i = 0, l = keys.length; i < l; i++) v = v[keys[i]];
            return (typeof v !== "undefined" && v !== null) ? v : "";
        });
    }

    function updateRawData() {
        $('#wpgmap-rawdata').val(JSON.stringify(wpGmapMarkings));
    }

    function getLegendHtml() {
        var html = '';
        var arr = [];
        $('.wpgmap-legend-list input[type="text"]').each(function(index, element) {
            if ($(element).val().length > 0) {
                var obj = { name: $(element).val() };
                arr.push(obj);
            }
        });

        $('.wpgmap-legend-list input[type="color"]').each(function(index, element) {
            if (arr[index]) {
                arr[index]['color'] = $(element).val();
            }
        });

        if (arr.length > 0) {
            $.each(arr, function(index, item) {
                html += '<option value="'+ item.color +'">'+ item.name +'</option>';
            });
        }

        return html;
    }

    function syncLegendsToMarkerBlock() {
        var legendHtml = getLegendHtml();
        $('.wpgmap-markings-legend-select').each(function(index, markerBlock) {
            $(markerBlock).html('<option value=""></option>' + legendHtml);

            var idString = $(markerBlock).closest('.wp-gmap-marker').attr('id');
            var id = idString.replace('wp-gmap-marker-', '');
            var strokeColorElement = $(markerBlock).closest('.wp-gmap-marker-body-list').find('#wpgmap-markings-'+id+'-strokeColor');
            if (strokeColorElement) {
                $(markerBlock).val(strokeColorElement.val());
            }
        });
    }

    function onChangeLegendList() {
        $('html').on('change', '.wpgmap-legend-list input', function () {
            syncLegendsToMarkerBlock();
        });
    }

    $(document).ready(function () {
        watchSettingsDropdown();
        addLegends();
        removeLegend();
        deleteConfirmation();
        onChangeLegend();
        onClickMarkerRemove();
        onClickMarkerPreview();
        onChangeMarkerBlockInputs();
        onChangeLegendList();
    });

    window.wpGmap = function () {
        var html = '';
        
        if (wpGmapMarkings.length > 0) {
            for (var i = 0; i < wpGmapMarkings.length; i++) {
                var marker = wpGmapMarkings[i];

                if (template[marker.type]) {
                    html += nano(template[marker.type], marker);
                }
            }

            $('.wp-gmap-button-submit').show();
        } else {
            $('.wp-gmap-button-submit').hide();
        }

        $('#wp-gmap-markings').html(html);
        updateRawData();

        $('#wp-gmap-markings [data-val]').each(function(index, element) {
            $(element).val($(element).attr('data-val'));
        });

        syncLegendsToMarkerBlock();
    }

    if (typeof wpGmapMarkings !== 'undefined' && wpGmapMarkings.length > 0) {
        wpGmap();
    }

    window.wpGmapOnSelectMarking = function (id) {
        var id = 'wp-gmap-marker-' + id;

        $('.wp-gmap-marker').removeClass('active');
        $('#' + id).addClass('active');
    }

})(jQuery);
