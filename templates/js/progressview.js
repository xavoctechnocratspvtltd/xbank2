$.each({
        updateProgress: function(data) {
                    //called when successful
                    progress_data = data;

                    var token = Math.floor(Math.random() * 100000);

                    var q = $('.progress-base');
                    $.each(progress_data, function(index, val) {
                        if ($('#pb' + index).length) {

                            $('#pb' + index).parent().find('b').text(index + ' : ' + val['running'] + ' / ' + val['total'] + ' [' + val['detail'] + ']');

                            if (progress_data[index]['total'] != 'undefined') {
                                var per;
                                per = Math.floor(progress_data[index]['running'] / progress_data[index]['total'] * 100);
                                console.log(per);
                                $('#pb' + index).progressbar('option', {
                                    value: per
                                });
                            } else {
                                $('#pb' + index).progressbar('option', {
                                    value: false
                                });
                            }
                        } else {
                            q.append('<div class="xprogress-bar" token="' + token + '" ><b>' + index + '</b><div  id="pb' + index + '" style="background-color:rgb(200,214,80)"></div></div>');
                            $('#pb' + index).progressbar({
                                value: 0
                            });
                            $('#pb' + index).find('.ui-progressbar-value').css('background-color', '#eef');
                            console.log('adding');
                        }
                        $('#pb' + index).parent().attr('token', token);
                    });

                    $('.xprogress-bar[token!=' + token + ']').remove();
                }
        },
    $.univ._import);