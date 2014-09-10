$.each({
	// Useful info about mouse clicking bug in jQuery UI:
	// http://stackoverflow.com/questions/6300683/jquery-ui-autocomplete-value-after-mouse-click-is-old-value
	// http://stackoverflow.com/questions/7315556/jquery-ui-autocomplete-select-event-not-working-with-mouse-click
	// http://jqueryui.com/demos/autocomplete/#events (check focus and select events)

	avgrate: function(item_field,rate_field){
		// alert($(item_field).val());
		$.ajax({
			url:'?page=ajaxhandler1&item='+$(item_field).val(),
			type:'GET',
			success: function(data){
				// $(rate_field).val(data);
			}
		});
	}

},$.univ._import);
