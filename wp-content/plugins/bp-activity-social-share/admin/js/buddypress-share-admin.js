(function ($) {

    jQuery('.bp_share_social_list').sortable({
        scrollSpeed: 1,
        scrollSensitivity: 1,
    });

    jQuery(document).on('click', '.button-primary.bp_share_option_save', function() {
        let sortedList = [];
        let listLegth = jQuery('tr.bp-share-services-row').length;
        jQuery('tr.bp-share-services-row').each(function(index, elem) {
            if (index < listLegth) {
                let temp = [];
                let key = jQuery(this).data('key');
                temp = {
                    oldIndex: jQuery(this).data('pos-index'),
                    newIndex: index,
                    key: key,
                }
                sortedList.push(temp);
            }
        });
        jQuery.ajax({
            url: my_ajax_object.ajax_url,
            type: 'post',
            data: { action: 'bp_share_sort_social_links_ajax', 'nonce': my_ajax_object.nonce, 'sorted_data': sortedList },
            dataType: 'JSON',
            success: function(response) {

            }
        });
        var selected = [];
        jQuery('#bp_share_chb input:checked').each(function() {
            selected.push(jQuery(this).attr('name'));
        });

        var selected_extras = [];
        jQuery('.bp_share_settings_section_callback_class input:checked').each(function() {
            selected_extras.push(jQuery(this).attr('name'));
        });
        var data = {
            'action': 'bp_share_chb_services_ajax',
            'active_chb_array': selected,
            'active_chb_extras': selected_extras,
            'nonce': my_ajax_object.nonce
        };

        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        jQuery.post(my_ajax_object.ajax_url, data, function(response) {
            location.reload();
        });
        return false;
    });

    jQuery(function() {
        jQuery("#drag_social_icon").sortable({
        cursor: "move",
        });
        jQuery("#drag_icon_ul").sortable({
          cursor: "move",
        });
    });

    /*drag-drop*/
    // jQuery(function() {
    //     var $drag_social_icon = jQuery(".social_icon_section > ul");
    //     jQuery("li", $drag_social_icon).draggable({
    //         revert: "invalid",
    //         helper: "clone"
    //     });
    // });

    jQuery(function() {
    function bpas_drag_drop_social_icon() {
        var $drag_social_icon = jQuery(".social_icon_section > ul");
        jQuery("li", $drag_social_icon).draggable({
            revert: "invalid",
            helper: "clone",
            start: function() {
                jQuery(this).css('opacity', '0.5');
            },
            stop: function() {
                jQuery(this).css('opacity', '1');
            }
        });
    }

    jQuery("#drag_icon_ul").droppable({
        accept: ".social_icon_section > #drag_social_icon > li",
        drop: function(event, ui) {
            var name = jQuery(ui.draggable).text();
            var socialclass = 'icon_' + name;
            var newElem = jQuery('<li class="socialicon ui-draggable ' + socialclass + '">' + name + '</li>').hide();
            jQuery("#drag_icon_ul").append(newElem);
            newElem.fadeIn();

            jQuery(ui.draggable).fadeOut("normal", function() {
                jQuery(this).remove();
            });

            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wss_social_icons',
                    term_name: name,
                    nonce: my_ajax_object.nonce
                },
                success: function(res) {
                    if (res.success) {
                        // Additional success handling
                    }
                },
                complete: function() {
                    bpas_drag_drop_social_icon();
                }
            });
        }
    });

    jQuery("#drag_social_icon").droppable({
        accept: ".social_icon_section > #drag_icon_ul > li",
        drop: function(event, ui) {
            var get_icon_name = jQuery(ui.draggable).text();
            var socialclass = 'icon_' + get_icon_name;
            var newElem = jQuery('<li class="socialicon ui-draggable ' + socialclass + '">' + get_icon_name + '</li>').hide();
            jQuery("#drag_social_icon").append(newElem);
            newElem.fadeIn();

            jQuery(ui.draggable).fadeOut("normal", function() {
                jQuery(this).remove();
            });

            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wss_social_remove_icons',
                    icon_name: get_icon_name,
                    nonce: my_ajax_object.nonce
                },
                success: function(res) {
                    if (res.success) {
                        // Additional success handling
                    }
                },
                complete: function() {
                    bpas_drag_drop_social_icon();
                }
            });
        }
    });

    // Initial call to set up draggable and droppable elements.
    bpas_drag_drop_social_icon();
});

})(jQuery);