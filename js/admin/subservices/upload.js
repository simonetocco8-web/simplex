jQuery(document).ready(function() {
var el = jQuery('<ul/>').addClass('contextual-menu').append(jQuery('<li/>').append(jQuery('<input/>', {
    'id': 'file_upload',
    'type': 'file',
    'name': 'file_upload'
})));
    jQuery('#content h2').after(el);
    
    jQuery('#file_upload').uploadify({
        'uploader'  : baseUrl + '/js/uploadify/uploadify.swf',
        'script'    : baseUrl + '/files/upload',
        'cancelImg' : baseUrl + '/js/uploadify/cancel.png',
        'buttonText': 'carica template',
        'method'    : 'post',
        'fileExt'   : '*.docx',
        'fileDesc'  : 'Docx template',
        'scriptData': {
            'PHPSESSID': jQuery('#sid').val(),
            'p': jQuery('#p').val(),
            'r': jQuery('#r').val(),
            'o': 1,
            'pif': 1
        },
        'auto'      : true,
        'onComplete': function(event, ID, fileObj, response, data){
            alert('template aggiornato');
        }
    });
});