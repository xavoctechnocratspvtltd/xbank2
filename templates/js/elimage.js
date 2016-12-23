$.each({
	// Useful info about mouse clicking bug in jQuery UI:
	// http://stackoverflow.com/questions/6300683/jquery-ui-autocomplete-value-after-mouse-click-is-old-value
	// http://stackoverflow.com/questions/7315556/jquery-ui-autocomplete-select-event-not-working-with-mouse-click
	// http://jqueryui.com/demos/autocomplete/#events (check focus and select events)

	myelimage: function(other_field){

		var q=this.jquery;
		console.log(other_field);
		var fm = $('<div/>').dialogelfinder({
												url : 'elfinder/php/connector.php',
												lang : 'en',
												width : 840,
												destroyOnClose : true,
												getFileCallback : function(files, fm) {
													$(other_field).val(files.url);
												},
												commandsOptions : {
													getfile : {
														oncomplete : 'close',
														folders : true
													}
												}
											}).dialogelfinder('instance');

	}

},$.univ._import);

/*		$(this.jquery).click(function(event) {
			alert('HI');
			// var fm = $('<div/>').dialogelfinder({
			// 										url : 'elfinder/php/connector.php',
			// 										lang : 'en',
			// 										width : 840,
			// 										destroyOnClose : true,
			// 										getFileCallback : function(files, fm) {
			// 											$(other_field).val(files.url);
			// 										},
			// 										commandsOptions : {
			// 											getfile : {
			// 												oncomplete : 'close',
			// 												folders : true
			// 											}
			// 										}
			// 									}).dialogelfinder('instance');
		});
	}
*/