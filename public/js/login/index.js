var form;

$(document).ready(function() {
    form = $('#login-form');
    form.submit(function(ev) {
        login();
        ev.preventDefault();
    });
});

function login(){
    $('.messages').remove();
    //$('#login-in').spin();
    $.ajax({
        type: "POST",
        url: form.attr('action'),
        dataType: 'json',
        data: form.serialize(),
        success: function(r) {
            //$('#login-in').unspin();
            if(r.result){
                if(r.internal){
                    window.location.reload();
                }else{
                    var lis = buildInternals(r.internals);
                    showMessage(r.message, 'success', lis);
                }
            }else{
                //form.reset();
                showMessage(r.message, 'error', false);
            }
        }
    });   
}

function showMessage(msg, cls, lis){
    var indl, ndl;
    $('#result').append(ndl = $('<dl/>').addClass('messages').append(indl = $('<dl/>').addClass(cls).html(msg)));
    if(lis){
        $.each(lis, function(i,v){
            indl.append($('<ul/>').append(v));
        });
    }
    //ndl.slide('hide').slide('in');
}

function buildInternals(is){
    var lis = [];
    $.each(is, function(idx, i){
        var li = $('<li/>').append($('<a/>').attr({'href': '#', 'rel': i.internal_id}).html('<b>' + i.abbr + '</b> - ' + i.full_name).click(function(ev){
            ev.preventDefault();
            $('#id_internal').attr('value', $(this).attr('rel'));
            login();
        }));
        lis.push(li);
    });
    return lis;
}