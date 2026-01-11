var GridView = new Class({
    Extends: GridView,
    ajaxForm: function(){
        this.form = this.container.getElement('form');
        // initialize the application
        var o = this;
        var app = Sammy(function() {
            // define a 'route'
            this.get(/\/files\/get\/(.*)/, function() {
                var data = this.params.format ? {} : {format: 'html'};
                if(!pe) pe = jQuery('#p');
                var eles = this.path.split('/');
                if(eles.length) pe.val(eles[eles.length - 1]);
                o.getRequest().send({ url: this.path, data: data});
            });
        });

        app.run();
    },
    refresh: function(tree, elems, html, js){
        this.parent(tree, elems, html, js);
        powerLinks();
    }
});

function powerLinks(){
    jQuery('#files-list').find('.list-links a').click(function(){
        if(confirm('eliminare il file o la cartella?')){
            jQuery.ajax({
                type: "POST",
                url: jQuery(this).attr('href') + '/format/json',
                dataType: 'json',
                success: function(r) {
                    if(r.result){
                        loadList(pe.val());
                    }else{
                        alert('errori nella eliminazione');
                    }
                }
            });
        }
        return false;
    });
}

var lc = null;
function loadList(path){
    lc.load(baseUrl + '/files/get/format/html/p/' + path, function(){
        powerLinks();
    });
}

var pe = null;
    jQuery(document).ready(function() {
        pe = jQuery('#p');
    lc = jQuery('#files-list');
    
    powerLinks();
      
    var name = jQuery( "#name" ),
        allFields = jQuery( [] ).add( name ),
        tips = jQuery( ".validateTips" );

    function updateTips( t ) {
        tips
            .text( t )
            .addClass( "ui-state-highlight" );
        setTimeout(function() {
            tips.removeClass( "ui-state-highlight", 1500 );
        }, 500 );
    }

    /*
    function checkLength( o, n, min, max ) {
        if ( o.val().length > max || o.val().length < min ) {
            o.addClass( "ui-state-error" );
            updateTips( "Length of " + n + " must be between " +
                min + " and " + max + "." );
            return false;
        } else {
            return true;
        }
    }
    */

    function checkRegexp( o, regexp, n ) {
        if ( !( regexp.test( o.val() ) ) ) {
            o.addClass( "ui-state-error" );
            jQuery('#create-folder-error').show();
            updateTips( n );
            return false;
        } else {
            return true;
        }
    }
    var nff = jQuery( "#newfolder-form" );
    nff.dialog({
        autoOpen: false,
        width: 250,
        modal: true,
        buttons: {
            "Crea una nuova cartella": function() {
                var bValid = true;
                allFields.removeClass( "ui-state-error" );
                jQuery('#create-folder-error').hide();

                //bValid = bValid && checkRegexp( name, /^!(\/)+$/i, "Invalid folder name." );
                bValid = bValid && checkRegexp( name, /^[^\\/?%*:|"<>]+$/i, "Invalid folder name." );

                if ( bValid ) {
                    jQuery.ajax({
                        type: "POST",
                        url: baseUrl + '/files/mkdir/format/json/name/' + name.val() + '/p/' + pe.val(),
                        dataType: 'json',
                        success: function(r) {
                            if(r.result){
                                loadList(pe.val());
                            }else{
                                alert('errori nella creazione cartella');
                            }
                        }
                    });
                    jQuery( this ).dialog( "close" );
                }
            },
            Cancel: function() {
                jQuery( this ).dialog( "close" );
            }
        },
        close: function() {
            allFields.val( "" ).removeClass( "ui-state-error" );
        }
    });
    
    jQuery( "#create-folder" ).click(function() {
        nff.dialog( "open" );
    });

    var sid = jQuery('#sid').val();

    var fileUpload = jQuery('#file_upload');

    fileUpload.uploadify({
        'uploader'  : baseUrl + '/js/uploadify/uploadify.swf',
        'script'    : baseUrl + '/files/upload',
        'cancelImg' : baseUrl + '/js/uploadify/cancel.png',
        'buttonText': 'carica file',
        'method'    : 'post',
        'scriptData': {
            'PHPSESSID': sid,
            'p': pe.val()
        },
        'multi'     : true,
        //'folder'    : '/uploads',
        'auto'      : true,
        //'onComplete': function(event, ID, fileObj, response, data){
        'onComplete': function(){
            loadList(pe.val());
        },
        'onSelectOnce': function(){
            fileUpload.uploadifySettings('scriptData', {
                p: pe.val()
            });
            fileUpload.uploadifyUpload();
        }
    });

    var archiveUpload = jQuery('#archive_upload');
    archiveUpload.uploadify({
        'uploader'  : baseUrl + '/js/uploadify/uploadify.swf',
        'script'    : baseUrl + '/files/upload',
        'cancelImg' : baseUrl + '/js/uploadify/cancel.png',
        'buttonText': 'carica archivio',
        'method'    : 'post',
        'fileDesc'  : 'File Compressi (Zip)',
        'fileExt'  : '*.zip',
        'scriptData': {
            'PHPSESSID': sid,
            'p': pe.val(),
            'a': 1
        },
        'multi'     : false,
        'auto'      : true,
        //'onComplete': function(event, ID, fileObj, response, data){
        'onComplete': function(){
            loadList(pe.val());
        },
        'onSelectOnce': function(){
            archiveUpload.uploadifySettings('scriptData', {
                p: pe.val()
            });
            archiveUpload.uploadifyUpload();
        }
    });

    document.id('makeArchive').addEvent('click', function(evt){
        evt.preventDefault();
        var url = baseUrl + '/files/archive';
        var cbs = $$('.cb:checked');
        if(cbs.length == 0){
            alert('Devi selezionare almeno un file!');
            return;
        }
        url += '/p/' + pe.val();
        var files = '';
        cbs.each(function(cb){
            files += '/f/' + cb.get('id').substring(3);
        });
        files = btoa(files);
        //console.log(encodeURIComponent(url));
        location.href = url + '/f/' + files;
    });

    document.id('content').addEvent('change:relay(.cb)', function(e){
        if(e.target.get('id') == 'cb_..') e.target.set('checked', false);
        var cbs = $$('.cb:checked');
        if(cbs.length > 0){
            document.id('makeArchive').show();
        }else{
            document.id('makeArchive').hide();
        }
    });
});