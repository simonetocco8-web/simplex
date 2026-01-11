window.addEvent('domready', function(){
    
    var npv;
    function powerTranche(){
        if(document.id('np')){
            document.id('np').addEvent('click', function(evt){
                evt.preventDefault();
                var tid = document.id('tranche_id').get('value');
                new MavDialog({
                    'title': 'Nuovo Pagamento',
                    'url': baseUrl + '/administration/np/format/html/tid/' + tid,
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
                        var form = document.id('np_form');
                        var bf = new By0MessageForm('np_form');
                        bf.addEvent('success', function(response){
                            if(response.result) update(response.id);
                        });
                        npv = new Form.Validator.Inline(form);
                        form.addEvent('submit', function(evt){
                            evt.preventDefault();
                            if(npv.validate()){
                                form.send();
                            }
                        });
                    }
                }); 
            });
        }
    }
    
    powerTranche();
    
    var upreq;
    function update(id){
        if(!upreq){
             upreq = new Request.HTML({
                url: baseUrl + '/administration/tranche/format/html/',
                method: 'post',
                update: 'tranche-detail',
                useSpinner: true,
                onComplete: function(){
                    powerTranche();
                }
            });   
        }
        upreq.send({
            data:{
                id: id
            }
        });
    }

});