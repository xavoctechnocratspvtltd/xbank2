$.each({
        updateProgress: function(data) {
                    // console.log('Data ');
                    // console.log(data);
                    //called when successful
                    progress_data = data;

                    var token = Math.floor(Math.random() * 100000);

                    var q = $('.progress-base');
                    $.each(progress_data, function(index, val) {
                        if ($('#pb' + index).length) {

                            var heading = index;
                            if(val['running'] != undefined)
                                heading += ' : '+val['running'];
                            if(val['total'] != undefined)
                                heading += ' / ' + val['total'];
                            if(val['detail'] != undefined)
                                heading += ' [' + val['detail'] + ']';

                            $('#pb' + index).parent().find('b').text(heading);

                            if (progress_data[index]['total'] != 'undefined' && progress_data[index]['running'] > 0){
                                var per;
                                per = Math.floor(progress_data[index]['running'] / progress_data[index]['total'] * 100);
                                // console.log('percentage');
                                // console.log(progress_data);
                                // console.log(per);
                                // console.log(index);
                                // console.log('percentage finish');
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
                            // console.log('append adding');
                        }
                        $('#pb' + index).parent().attr('token', token);
                    });

                    $('.xprogress-bar[token!=' + token + ']').remove();
                }
        },
    $.univ._import);