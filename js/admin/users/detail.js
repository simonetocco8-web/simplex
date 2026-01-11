window.addEvent('domready', function(){
    var cpwv;
    document.id('change_pw').addEvent('click', function(evt){
        //alert('oooooooooo');
        evt.stop();
        new MavDialog({
            'title': 'Cambia Password',
            'url': evt.target.get('href'),
            fxShow: {
                'top' : [-400, 0]
            },
            fxHide: {
                'top' : -400
            },
            ok: false,
            width: 612,
            title: false,
            onMessageSet: function(){
                var form = document.id('cpw_form');
                new By0MessageForm('cpw_form');
                cpwv = new Form.Validator.Inline(form);
                form.addEvent('submit', function(evt){
                    evt.preventDefault();
                    if(cpwv.validate()){
                        form.send();
                    }
                });
            }
        }); 
    });
});