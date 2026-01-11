window.addEvent('domready', function(){
    
    var ncv;
    function powerInvoice(){
        if(document.id('nc')){
            document.id('nc').addEvent('click', function(evt){
                evt.preventDefault();
                var iid = document.id('invoice_id').get('value');
                new MavDialog({
                    'title': 'Emetti Nota Credito',
                    'url': baseUrl + '/administration/nc/format/html/iid/' + iid,
                    'loadingMessage': '<div class="spinner-simple">caricamento...</div>',
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
                        application.initDateFields();
                        var form = document.id('nc_form');
                        var bf = new By0MessageForm('nc_form');
                        bf.addEvent('success', function(response){
                            //if(response.result) update(response.id);
                            // dovremmo reindirizzare al dettaglio nota credito
                            if(response.result)
                                location.href = baseUrl + '/administration/invoice/id/' + response.id;
                        });
                        ncv = new Form.Validator.Inline(form);
                        form.addEvent('submit', function(evt){
                            evt.preventDefault();
                            if(ncv.validate()){
                                form.send();
                            }
                        });
                    }
                }); 
            });
        }
    }
    
    powerInvoice();

});