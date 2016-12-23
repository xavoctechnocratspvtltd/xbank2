$.each({
    // Useful info about mouse clicking bug in jQuery UI:
    // http://stackoverflow.com/questions/6300683/jquery-ui-autocomplete-value-after-mouse-click-is-old-value
    // http://stackoverflow.com/questions/7315556/jquery-ui-autocomplete-select-event-not-working-with-mouse-click
    // http://jqueryui.com/demos/autocomplete/#events (check focus and select events)

    myautocomplete: function(data, other_field, options, id_field, title_field, send_other_fields) {

        var q = this.jquery;


        // console.log('hi');
        // console.log(send_other_fields);
        // console.log(data);

        this.jquery.autocomplete($.extend({
            source: function(request, response) {
                var other_fields_to_send = {};
                $.each(send_other_fields, function(index, val) {
                    other_fields_to_send['o_' + val.replace('#', '')] = $(val).val();
                });
                // console.log(other_fields_to_send);
                $.getJSON(data,
                    $.extend(other_fields_to_send, {
                        term: request.term
                    }), response);
            },
            focus: function(event, ui) {
                // Imants: fix for item selecting with mouse click
                var e = event;
                while (e.originalEvent !== undefined) e = e.originalEvent;
                if (e.type != 'focus') q.val(ui.item[title_field]);

                return false;
            },
            select: function(event, ui) {
                q.val(ui.item[title_field]);
                $(other_field).val(ui.item[id_field]);

                return false;
            },
            change: function(event, ui) {
                var data = $.data(this); //Get plugin data for 'this'
                // console.log(data);
                // console.log(data.selectedItem);
                if ($(this).data('ui-autocomplete').selectedItem == undefined) {
                    if ("mustMatch" in options) q.val('');
                    $(other_field).val(q.val());
                    return false;
                } else {
                    if ('mustNotMatch' in options) {
                        q.val('');
                        $(other_field).val(q.val());
                        return false;
                    }
                }
            }
        }, options))
            .data("ui-autocomplete")._renderItem = function(ul, item) {
                return $("<li></li>")
                    .data("ui-autocomplete-item", item)
                    .append("<a>" + item[title_field] + "</a>")
                    .appendTo(ul);
        };

    }

}, $.univ._import);