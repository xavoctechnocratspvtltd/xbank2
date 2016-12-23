// Make sortable containers sortable
xepan_editing_mode = true;
console.log('xepan_editing_mode Set in JavaScript Global Scope');

origin = 'sortable';
component_html = '';
create_sortable = false;
run_time_id = 1;
component_option_page = '';

clipboard_component = undefined;

temp1 = undefined;
temp2 = undefined;
temp3 = undefined;
temp4 = undefined;

current_selected_component = undefined;
last_selected_component = undefined;

save_and_take_snapshot = false;

sortable_disabled = false;

function generateUUID() {
    var d = new Date().getTime();
    var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        var r = (d + Math.random() * 16) % 16 | 0;
        d = Math.floor(d / 16);
        return (c == 'x' ? r : (r & 0x7 | 0x8)).toString(16);
    });
    return uuid;
}

function Utf8Encode(string) {
    string = string.replace(/\r\n/g, "\n");
    var utftext = "";

    for (var n = 0; n < string.length; n++) {
        var c = string.charCodeAt(n);
        if (c < 128) {
            utftext += String.fromCharCode(c);
        } else if ((c > 127) && (c < 2048)) {
            utftext += String.fromCharCode((c >> 6) | 192);
            utftext += String.fromCharCode((c & 63) | 128);
        } else {
            utftext += String.fromCharCode((c >> 12) | 224);
            utftext += String.fromCharCode(((c >> 6) & 63) | 128);
            utftext += String.fromCharCode((c & 63) | 128);
        }
    }
    return utftext;
};

function crc32(str) {
    str = Utf8Encode(str);
    var table = "00000000 77073096 EE0E612C 990951BA 076DC419 706AF48F E963A535 9E6495A3 0EDB8832 79DCB8A4 E0D5E91E 97D2D988 09B64C2B 7EB17CBD E7B82D07 90BF1D91 1DB71064 6AB020F2 F3B97148 84BE41DE 1ADAD47D 6DDDE4EB F4D4B551 83D385C7 136C9856 646BA8C0 FD62F97A 8A65C9EC 14015C4F 63066CD9 FA0F3D63 8D080DF5 3B6E20C8 4C69105E D56041E4 A2677172 3C03E4D1 4B04D447 D20D85FD A50AB56B 35B5A8FA 42B2986C DBBBC9D6 ACBCF940 32D86CE3 45DF5C75 DCD60DCF ABD13D59 26D930AC 51DE003A C8D75180 BFD06116 21B4F4B5 56B3C423 CFBA9599 B8BDA50F 2802B89E 5F058808 C60CD9B2 B10BE924 2F6F7C87 58684C11 C1611DAB B6662D3D 76DC4190 01DB7106 98D220BC EFD5102A 71B18589 06B6B51F 9FBFE4A5 E8B8D433 7807C9A2 0F00F934 9609A88E E10E9818 7F6A0DBB 086D3D2D 91646C97 E6635C01 6B6B51F4 1C6C6162 856530D8 F262004E 6C0695ED 1B01A57B 8208F4C1 F50FC457 65B0D9C6 12B7E950 8BBEB8EA FCB9887C 62DD1DDF 15DA2D49 8CD37CF3 FBD44C65 4DB26158 3AB551CE A3BC0074 D4BB30E2 4ADFA541 3DD895D7 A4D1C46D D3D6F4FB 4369E96A 346ED9FC AD678846 DA60B8D0 44042D73 33031DE5 AA0A4C5F DD0D7CC9 5005713C 270241AA BE0B1010 C90C2086 5768B525 206F85B3 B966D409 CE61E49F 5EDEF90E 29D9C998 B0D09822 C7D7A8B4 59B33D17 2EB40D81 B7BD5C3B C0BA6CAD EDB88320 9ABFB3B6 03B6E20C 74B1D29A EAD54739 9DD277AF 04DB2615 73DC1683 E3630B12 94643B84 0D6D6A3E 7A6A5AA8 E40ECF0B 9309FF9D 0A00AE27 7D079EB1 F00F9344 8708A3D2 1E01F268 6906C2FE F762575D 806567CB 196C3671 6E6B06E7 FED41B76 89D32BE0 10DA7A5A 67DD4ACC F9B9DF6F 8EBEEFF9 17B7BE43 60B08ED5 D6D6A3E8 A1D1937E 38D8C2C4 4FDFF252 D1BB67F1 A6BC5767 3FB506DD 48B2364B D80D2BDA AF0A1B4C 36034AF6 41047A60 DF60EFC3 A867DF55 316E8EEF 4669BE79 CB61B38C BC66831A 256FD2A0 5268E236 CC0C7795 BB0B4703 220216B9 5505262F C5BA3BBE B2BD0B28 2BB45A92 5CB36A04 C2D7FFA7 B5D0CF31 2CD99E8B 5BDEAE1D 9B64C2B0 EC63F226 756AA39C 026D930A 9C0906A9 EB0E363F 72076785 05005713 95BF4A82 E2B87A14 7BB12BAE 0CB61B38 92D28E9B E5D5BE0D 7CDCEFB7 0BDBDF21 86D3D2D4 F1D4E242 68DDB3F8 1FDA836E 81BE16CD F6B9265B 6FB077E1 18B74777 88085AE6 FF0F6A70 66063BCA 11010B5C 8F659EFF F862AE69 616BFFD3 166CCF45 A00AE278 D70DD2EE 4E048354 3903B3C2 A7672661 D06016F7 4969474D 3E6E77DB AED16A4A D9D65ADC 40DF0B66 37D83BF0 A9BCAE53 DEBB9EC5 47B2CF7F 30B5FFE9 BDBDF21C CABAC28A 53B39330 24B4A3A6 BAD03605 CDD70693 54DE5729 23D967BF B3667A2E C4614AB8 5D681B02 2A6F2B94 B40BBE37 C30C8EA1 5A05DF1B 2D02EF8D";
    var crc = 0;
    var x = 0;
    var y = 0;

    crc = crc ^ (-1);
    for (var i = 0, iTop = str.length; i < iTop; i++) {
        y = (crc ^ str.charCodeAt(i)) & 0xFF;
        x = "0x" + table.substr(y * 9, 8);
        crc = (crc >>> 8) ^ x;
    }

    return (crc ^ (-1)) >>> 0;
};



// MAKE SORTABLE ON PAGE 
$(".epan-sortable-component").sortable(s = {
    revert: 100,
    cursor: 'move',
    connectWith: '.epan-sortable-component',
    placeholder: 'epan-place-holder',
    handle: '> .drag-handler',
    tolerance: 'pointer',
    helper: function(event, ui) {
        return $('<div><h1>Dragging ... </h1></div>');
        // $(ui.item).addClass('dragging-shorten').children().sorting('disable');
        // return $(ui).css('opacity',0.3);
    },
    start: function(event, ui) {
        if ($(ui.item).hasClass('ui-sortable')) {
            sortable_disabled = true;
            $(ui.item).sortable("option", "disabled", true);
            $(ui.item).find('.epan-sortable-component').sortable("option", "disabled", true);
        }
    },
    sort: function(event, ui) {
        $(ui.placeholder).html('Drop in ' + $(ui.placeholder).parent().attr('component_type') + ' ??');
    },
    stop: function(event, ui) {
        if (origin === 'toolbox') {

            uuid = generateUUID();

            var regex = new RegExp("__COMPONENTID__", 'g');
            component_html = component_html.replace(regex, uuid);

            new_obj = $(component_html).attr('id', uuid);

            if (create_sortable)
                $(new_obj).sortable(s); //.disableSelection();

            $(new_obj).find('.epan-sortable-component').sortable(s);

            makeSelectable(new_obj);
            makeSelectable($(new_obj).find('.epan-component'));

            if ($('#epan-component-border:checked').size() > 0) {
                $(new_obj).addClass('component-outline');
            }

            if ($('#epan-component-extra-padding:checked').size() > 0) {
                if($(new_obj).hasClass('epan-sortable-component'))
                    $(new_obj).addClass('epan-sortable-extra-padding');
                $(new_obj).find('.epan-sortable-component').addClass('epan-sortable-extra-padding');
            }
            // TODO if editor class is implemented in new object
            // and contenteditable is true
            $(new_obj).popline();

            if ($(new_obj).hasClass('ui-resizable'))
                $(new_obj).resizable();

            // Make drag Handler on mouse Over
            $(new_obj).hover(function(event) {
                var self = this;
                /* Stuff to do when the mouse enters the element */
                component_handel = $('<div class=\'drag-handler\' style=\' z-index:2000\'><i class=\'glyphicon glyphicon-move\'></i></div>');
                $(component_handel).appendTo($(this));

                remove_btn = $('<div class=\'remove_btn\'  style=\' z-index:2000\' title=\'' + $(self).attr('component_type') + '\'><i class=\'glyphicon glyphicon-remove\'></i></div>');
                $(remove_btn).appendTo($(this));
                $(remove_btn).tooltip();
                $(remove_btn).hover(function() {
                    /* Stuff to do when the mouse enters the element */
                    $(self).addClass('component-executed');

                }, function() {
                    /* Stuff to do when the mouse leaves the element */
                    $(self).removeClass('component-executed');

                });
                $(remove_btn).click(function(event) {
                    remove_element($(self));
                });

            }, function() {
                /* Stuff to do when the mouse leaves the element */
                $(this).find('.drag-handler').remove();
                $(this).find('.remove_btn').remove();

            });
    

            var str = $(this).attr('component_type') + '_option.object_dropped("' + $(ui.item).parent('.epan-sortable-component').attr('id') + '","' + $(new_obj).attr('id') + '")';
            try {
                console.log(ui);
                eval(str);
            } catch (err) {
                console.log(err);
            }
            // $(ui.item).addClass('drag-handler-attached');
            ui.item.replaceWith(new_obj);
            origin = 'sortable';
            run_time_id = run_time_id + 1;
        }
        selectComponent(ui.item);
        $(ui.item).css('opacity', '1');
        if (sortable_disabled) {
            sortable_disabled = false;
            $(ui.item).sortable("option", "disabled", false);
            $(ui.item).find('.epan-sortable-component').sortable("option", "disabled", false);
        }
    }
}); //.disableSelection();

function showError(msg) {
    console.log(msg);
    alert(msg);
}

function callService(api, options, successCallBack, errorCallBack) {
    $.ajax({
        url: '?page=service1',
        type: 'GET',
        // dataType: 'default: Intelligent Guess (Other values: xml, json, script, or html)',
        data: options,
    })
        .done(function() {
            if (successCallBack !== undefined)
                successCallBack();
            return true;
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            if (errorCallBack !== undefined)
                errorCallBack(errorThrown);
            throw errorThrown;
        })
        .always(function() {
            console.log("complete");
        });

}

$.fn.reverse = [].reverse;

function updateBreadCrumb() {
    $('#epan-frontend-editing-toolbar-breadcrumb').html('');
    if (current_selected_component === undefined) {
        return;
    }

    $(current_selected_component)
        .parents('.epan-component')
        .andSelf()
    // .reverse()
    .each(function(index, el) {
        var self = this;
        new_btn = $('<div class=\'glyphicon glyphicon-forward pull-left\' style=\'margin:0 5px;\'></div>' + '<div class=\' btn btn-link btn-xs pull-left\'>' + $(el).attr('component_type') + '</div>');
        new_btn.click(function(event) {
            if (self == current_selected_component) {
                $(current_selected_component).effect("bounce", "slow");
                return;
            }
            $(self).dblclick();
        });
        // new_btn.append('<div class=\'glyphicon glyphicon-forward pull-left\' style=\'margin:0 5px;\'>&nbsp;</div>');
        new_btn.appendTo('#epan-frontend-editing-toolbar-breadcrumb');
    });



}


// Based on selection, disable enable the buttons
function updateActionButtons() {
    var btns = []; //['epan-option-button'];

    $.each(btns, function(index, val) {
        /* iterate through array or object */
        btn = btns[index];
        if (current_selected_component === undefined) {
            $('#' + btn).attr('disabled', 'disabled');
        } else {
            $('#' + btn).removeAttr('disabled');
        }
    });
}

function updateOptions() {
    $('.epan-component-options').hide();
    if (current_selected_component === undefined) {
        $('#epan-quick-component-options').slideUp('fast');
        return;
    }
    var str = $(current_selected_component).attr('component_type') + '_options.show()';
    try {
        eval(str);
    } catch (err) {
        console.log(err);
    }
    init_css_options();
    $('.epan-css-options').show();
    $('#epan-quick-component-options').slideDown('fast');
    option_view = $('.epan-component-options[component_type=' + $(current_selected_component).attr('component_type') + ']').show();
}

function selectComponent(obj) {
    $('.ui-selected').removeClass('ui-selected');
    $(obj).addClass('ui-selected');
    current_selected_component = obj;
}

function unSelectAllComponent() {
    $('.ui-selected').removeClass('ui-selected');
    // $(obj).addClass('ui-selected');
    current_selected_component = undefined;
    updateBreadCrumb();
    updateActionButtons();
    updateOptions();
}

// MAKE ALL COMPONENT SELECTABLE FOR OPTIONS AND ACTIONS etc...
function makeSelectable(obj) {
    obj.dblclick(function(event) {
        if ($(this).hasClass('ui-selected')) {
            $(this).removeClass('ui-selected');
            current_selected_component = undefined;
        } else {
            selectComponent(this);
        }
        updateBreadCrumb();
        updateActionButtons();
        updateOptions();
        event.stopPropagation();
    });
}


//REMOVE ELEMENT
function remove_element(obj) {
    // IF component has "data-page-component-id" remove it from database also
    current_selected_component = undefined;
    $(obj).trigger('removeComponent');
    $(obj).remove();
    updateBreadCrumb();
    updateActionButtons();
    $('#epan-quick-component-options').slideUp('fast');

}

$('#epan-save-btn').click(function(event) {

    // $('body').trigger('beforeSave');
    $('body').triggerHandler('beforeSave');
    $('body').univ().errorMessage('Wait.. saving your page !!!');

    unSelectAllComponent();
    $('.drag-handler').remove();
    $('.remove_btn').remove();

    var overlay = jQuery('<div id="overlay"> </div>');
    overlay.appendTo(document.body);

    html_body = $('.top-page').clone();
    $(html_body).find('[data-is-serverside-component=true]').html("");
    html_body = encodeURIComponent($.trim($(html_body).html()));

    html_crc = crc32(html_body);
    calling_page = 'index.php?page=owner_save&cut_page=1';

    if (edit_template == true) {
        html_body = encodeURIComponent($.trim($('#epan-content-wrapper').html()));
        html_crc = crc32(html_body);
        calling_page = 'index.php?page=owner_savetemplate&cut_page=1&template_id=' + current_template_id;
    }

    $("body").css("cursor", "default");

    $.ajax({
        url: calling_page,
        type: 'POST',
        dataType: 'html',
        data: {
            body_html: html_body,
            body_attributes: encodeURIComponent($('body').attr('style')),
            take_snapshot: save_and_take_snapshot ? 'Y' : 'N',
            crc32: html_crc,
            length: html_body.length
        },
    })
        .done(function(message) {
            if (message == 'saved') {
                $('body').univ().successMessage('Saved');
                $(overlay).remove();
            } else {
                $(overlay).remove();
                $('body').univ().errorMessage('Could Not save Page');
                eval(message);
            }
            // $('body').trigger('saveSuccess');
            $('body').triggerHandler('saveSuccess');
            console.log("success");
        })
        .fail(function(err) {
            $('body').trigger('saveFail');
            console.log(err);
            console.log('Error goit');
        })
        .always(function() {
            // $('body').trigger('afterSave');
            $('body').triggerHandler('afterSave');
            console.log("complete");
        });

});

// $('body').dblclick(function(event){
//     unSelectAllComponent();
// });



$(function() {

    $('body').trigger('xepan_editing_mode');

    // IF NOT EDITING TEMPLATE REMOVE CLASSES FROM TEMPLATES TO MAKE THEM NON EDITABLE
    if (edit_template !== true) {
        $.univ().errorMessage('Template Editing is Disabled');
        $('.epan-component').not('.top-page, .top-page *').removeClass('epan-component');
        $('.epan-sortable-component').not('.top-page, .top-page *').removeClass('epan-sortable-component');
        $('.editor').not('.top-page, .top-page *').removeClass('editor');
        $('[contenteditable=true]').not('.top-page, .top-page *').attr('contenteditable', 'false');
    } else {
        $.univ().errorMessage('Editing Template');
    }

    makeSelectable($('.epan-component'));
    updateActionButtons();

    document.execCommand('defaultParagraphSeparator', false, 'p');
    $(".editor").popline({
        position: "relative"
    });
    $('.editor').addClass('editor-attached');

    // $('.ui-resizable').resizable();

    $('#page-property-btn').click(function(event) {
        if (!$('#epan-quick-component-options').is(':visible')) {
            $('#epan-quick-component-options').slideDown('fast');
        }
        last_selected_component = current_selected_component;
        current_selected_component = $('body');
        
        if($('#page-template-btn-div').length){
            $('#page-template-btn-div').remove();
        }

        page_template_btn_div = $('<div id="page-template-btn-div">').prependTo('#xepan-basic-css-panel');

        template_btn = $('<button>').prependTo(page_template_btn_div).addClass('btn btn-default btn-sm');
        $(template_btn).html('Templates').attr('id','pages-templates-btn').on('click',function(event){
            $('#epan-quick-component-options').toggle('slideup');
            $('#page-template-btn-div').remove();
            $.univ.frameURL('Pages','index.php?page=owner_epantemplates');
        });

        pages_btn = $('<button>').prependTo(page_template_btn_div).addClass('btn btn-default btn-sm');
        $(pages_btn).html('Pages').attr('id','pages-pages-btn').on('click',function(event){
            $('#page-template-btn-div').remove();
            $('#epan-quick-component-options').toggle('slideup');
            $.univ.frameURL('Pages','index.php?page=owner_epanpages');
        });


        updateOptions();
    });


    // $('#epan-option-button').click(function() {
    //     if (last_selected_component !== undefined) {
    //         current_selected_component = last_selected_component;
    //         last_selected_component = undefined;
    //     }

    //     updateOptions();
    //     if (!$('#epan-quick-component-options').is(':visible')) {
    //         $('#epan-quick-component-options').slideDown('fast');
    //     }
    // });


    // Hide options in start
    $('#epan-quick-component-options').hide();

    $('#epan-quick-component-options-close').click(function(event) {
        $('#epan-quick-component-options').slideUp('fast');
    });

    // Make drag Handler on mouse Over
    $('.epan-component').hover(function(event) {
        var self = this;
        /* Stuff to do when the mouse enters the element */
        component_handel = $('<div class=\'drag-handler\' style=\' z-index:2000\'><i class=\'glyphicon glyphicon-move\'></i></div>');
        $(component_handel).appendTo($(this));

        remove_btn = $('<div class=\'remove_btn\' style=\' z-index:2000\' title=\'' + $(self).attr('component_type') + '\'><i class=\'glyphicon glyphicon-remove\'></i></div>');
        $(remove_btn).appendTo($(this));
        $(remove_btn).tooltip();
        $(remove_btn).click(function(event) {
            remove_element($(self));
            event.preventDefault();
        });

    }, function() {
        /* Stuff to do when the mouse leaves the element */
        $(this).find('.drag-handler').remove();
        $(this).find('.remove_btn').remove();

    });

    // Remove component-outline class on load to hide border
    $('.component-outline').removeClass('component-outline');
    // Remove easy-drop class on load to hide border
    $('.epan-sortable-extra-padding').removeClass('epan-sortable-extra-padding');


    $('#dashboard-btn').click(function(event) {
        // TODO check if content is changed
        window.location.replace('index.php?page=owner_dashboard');
    });

    $('#template-btn').click(function(event) {
        // console.log(current_template_id);
        // return;
        window.location.replace('index.php?edit_template=' + current_template_id);

        console.log("after edit" + current_template_id);

    });

    // $('.components-section').jScrollPane();

    shortcut.add("Ctrl+Shift+Up", function() {
        if (current_selected_component === undefined) return;
        parent_component = $(current_selected_component).parent('.epan-component');
        // console.log(parent_component);
        if (parent_component.length === 0) {
            $('body').univ().errorMessage('On Top Component');
            return;
        }
        selectComponent(parent_component);
        updateBreadCrumb();
        updateActionButtons();
        updateOptions();
        // event.stopPropagation();

    });

    shortcut.add("Ctrl+Shift+Down", function(event) {
        if (current_selected_component === undefined) {
            child_component = $('.top-page').children('.epan-component:first-child');
        } else {
            child_component = $(current_selected_component).children('.epan-component:first-child');
        }
        // console.log(parent_component);
        if (child_component.length === 0) {
            $('body').univ().errorMessage('No Child element found');
            return;
        }
        selectComponent(child_component);
        updateBreadCrumb();
        updateActionButtons();
        updateOptions();
        event.stopPropagation();

    });

    shortcut.add("Ctrl+Shift+Left", function(event) {
        prev_sibling = $(current_selected_component).prev('.epan-component');
        // console.log(parent_component);
        if (prev_sibling.length === 0) {
            $('body').univ().errorMessage('No Previous element found');
            return;
        }
        selectComponent(prev_sibling);
        updateBreadCrumb();
        updateActionButtons();
        updateOptions();
        event.stopPropagation();

    });

    shortcut.add("Ctrl+Shift+Right", function(event) {
        next_sibling = $(current_selected_component).next('.epan-component');
        // console.log(parent_component);
        if (next_sibling.length === 0) {
            $('body').univ().errorMessage('No Next element found');
            return;
        }
        selectComponent(next_sibling);
        updateBreadCrumb();
        updateActionButtons();
        updateOptions();
        event.stopPropagation();

    });

    shortcut.add("F2", function(event) {
        $('.epan-frontend-editing-toolbar').toggle('slideup');
    });

    shortcut.add("F4", function(event) {
        $('#epan-quick-component-options').toggle('slideup');
    });

    shortcut.add("F9", function(event) {
        unSelectAllComponent();
        $('#epan-quick-component-options').hide('fast');
        $('.epan-frontend-editing-toolbar').hide('fast');
    });

    shortcut.add("Esc", function(event) {
        unSelectAllComponent();
        $('#epan-quick-component-options').hide('fast');
    });

    shortcut.add("Ctrl+Shift+d", function(event) {
        if (current_selected_component !== undefined) {
            if (confirm('Do you really Want to Delete ...'))
                remove_element($(current_selected_component));
        }
        event.preventDefault();
        event.stopPropagation();
    });


    shortcut.add("F1", function(event) {
        $('body').univ().frameURL('Help', '?page=help');
    });

    shortcut.add("Ctrl+Shift+<", function(event) {
        if (current_selected_component === undefined) return;
        $(current_selected_component).insertBefore($(current_selected_component).prev('.epan-component'));
    });

    shortcut.add("Ctrl+Shift+>", function(event) {
        if (current_selected_component === undefined) return;
        $(current_selected_component).insertAfter($(current_selected_component).next('.epan-component'));
    });

    shortcut.add("Ctrl+s", function(event) {
        $('#epan-save-btn').click();
    });

    shortcut.add("Tab", function(event) {
        if (current_selected_component === undefined) {
            next_component = $('.top-page').children('.epan-component:first-child');
        } else {
            var $x = $('.epan-component:not(#web_index)');
            next_component = $x.eq($x.index($(current_selected_component)) + 1);
        }

        if($(next_component).attr('id') === undefined){
            next_component = $('.top-page').children('.epan-component:first-child');
            if($(next_component).attr('id') === undefined){
                event.stopPropagation();
                $.univ.errorMessage('No Component On Screen');
                return;
            }
        }

        selectComponent(next_component);
        updateBreadCrumb();
        updateActionButtons();
        updateOptions();
        event.stopPropagation();
    }, {
        disable_in_input: true
    });

    shortcut.add("Shift+Tab", function(event) {
        if (current_selected_component === undefined) {
            next_component = $('.top-page').children('.epan-component:first-child');
        } else {
            var $x = $('.epan-component:not(#web_index)');
            next_component = $x.eq($x.index($(current_selected_component)) - 1);
        }

        if($(next_component).attr('id') === undefined){
            next_component = $('.top-page').children('.epan-component:first-child');
            if($(next_component).attr('id') === undefined){
                event.stopPropagation();
                $.univ.errorMessage('No Component On Screen');
                return;
            }
        }

        selectComponent(next_component);
        updateBreadCrumb();
        updateActionButtons();
        updateOptions();
        event.stopPropagation();
    });

    shortcut.add("Ctrl+Shift+X", function(event) {
        if (current_selected_component === undefined) {
            $('body').univ().errorMessage('No Component Selected, Memory not altered');
            return;
        }

        clipboard_component = $(current_selected_component).clone(true, true);
        remove_element($(current_selected_component));
    });

    shortcut.add("Ctrl+Shift+C", function(event) {
        if (current_selected_component === undefined) {
            $('body').univ().errorMessage('No Component Selected, Memory not altered');
            return;
        }

        clipboard_component = $(current_selected_component).clone(true, true);
    });

    shortcut.add("Ctrl+Shift+V", function(event) {
        if (current_selected_component === undefined) {
            $('body').univ().errorMessage('No Component Selected, Cannot Copy as first child');
            return;
        }
        $(clipboard_component).appendTo($(current_selected_component));

    });

    // Keep alive
    function keepalive() {
        $.ajax({
            url: 'index.php?page=owner_keepalive&cut_page=1'
        });
    }
    setInterval(keepalive, 300000);

});