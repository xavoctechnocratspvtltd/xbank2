$.each({
    xtinymce: function(obj) {
        tinymce.baseURL = "templates/js/tinymce";

        console.log(tinymce);

        
        tinymce.editors=[];
        tinymce.activeEditors=[];

        $(document).on('focusin', function(event) {
            if ($(event.target).closest(".mce-window").length) {
                event.stopImmediatePropagation();
            }
        });

        tinymce.init({
            selector: '#'+$(obj).attr('id'),
            init_instance_callback : function(editor) {
                console.log("Editor: " + editor.id + " is now initialized.");
            },
            file_browser_callback: function elFinderBrowser(field_name, url, type, win) {
                $('<div/>').dialogelfinder({
                    url: 'elfinder/php/connector.php',
                    lang: 'en',
                    width: 840,
                    destroyOnClose: true,
                    getFileCallback: function(files, fm) {
                        $('#' + field_name).val(files.url);
                    },
                    commandsOptions: {
                        getfile: {
                            oncomplete: 'close',
                            folders: true
                        }
                    }
                }).dialogelfinder('instance');
            },
            plugins: [
                "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
                "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                "save table contextmenu directionality emoticons template paste textcolor colorpicker"
            ],
            toolbar1: "insertfile undo redo | styleselect fontselect fontsizeselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | l      ink image | print preview media fullpage | forecolor backcolor emoticons",
            // toolbar2: "print preview media | forecolor backcolor emoticons",
            image_advtab: true,
            save_enablewhendirty: false,
            content_css: 'templates/css/epan.css',
            setup: function(ed) {
                ed.on("change", function(ed) {
                    tinyMCE.triggerSave();
                });
            }
        });
    }
}, $.univ._import);