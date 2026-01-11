window.addEvent('domready', function(){
    var dv, _c, _cnew, _cduplica, _new, _duplica, next, cancel;
    if(document.id('done_button')){
	    document.id('done_button').addEvent('click', function(evt){
	        evt.preventDefault();
            var el = evt.target;
	        new MavDialog({
	            'title': 'Cambia Stato',
	            'url': evt.target.get('href') + '/format/html',
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
	                var form = document.id('done_form');
                    document.id('scn_button').addEvent('click', function(e){
                        document.id('ac').set('value', 1);
                    });
	                var bf = new By0MessageForm('done_form');
	                bf.addEvent('success', function(response){
                        var ob = response;
                        if(ob.ac == '1'){
                            location.href = baseUrl + '/tasks/edit/idp/' + ob.result;
                        }else{
	                	    location.reload(true);
                        }
	                });
	                dv = new Form.Validator.Inline(form);
	                form.addEvent('submit', function(evt){
	                    evt.preventDefault();
	                    if(dv.validate()){
	                        form.send();
	                    }
	                });
	            }
	        });
	    });
    }

    if(document.id('pedit_button')){
        document.id('pedit_button').addEvent('click', function(evt){
           location.href = evt.target.get('data-href');
        });
    }
    if(document.id('c_button')){
	    document.id('c_button').addEvent('click', function(evt){
            next = false;
            closeTask(evt);
	    });
    }
    if(document.id('cancel_button')){
        document.id('cancel_button').addEvent('click', function(evt){
            cancel = true;
            closeTask(evt);
        });
    }
    if(document.id('cnew_button')){
	    document.id('cnew_button').addEvent('click', function(evt){
            next = 'n';
            closeTask(evt);
	    });
    }
    if(document.id('cduplica_button')){
	    document.id('cduplica_button').addEvent('click', function(evt){
            next = 'd';
            closeTask(evt);
	    });
    }
    if(document.id('new_button')){
	    document.id('new_button').addEvent('click', function(evt){
            location.href = baseUrl + '/tasks/edit/idp/' + document.id('id').get('value') + '/w/' + 'n';
	    });
    }
    if(document.id('duplica_button')){
	    document.id('duplica_button').addEvent('click', function(evt){
            location.href = baseUrl + '/tasks/edit/idp/' + document.id('id').get('value') + '/w/' + 'd';
	    });
    }

    function closeTask(evt){
        evt.preventDefault();
        var el = evt.target;
        new MavDialog({
            'title': 'Chiudi Impegno',
            'url': evt.target.get('data-href') + '/format/html',
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
                var form = document.id('done_form');
                if(cancel){
                    document.id('cancel_task').set('value', 1);
                    document.id('label-done').set('html', 'Note di Annullamento');
                }
                var bf = new By0MessageForm('done_form');
                bf.addEvent('success', function(response){
                    var ob = response;
                    if(!next){
                        location.reload(true);
                    }else{
                        location.href = baseUrl + '/tasks/edit/idp/' + ob.result + '/w/' + next;
                    }
                });
                dv = new Form.Validator.Inline(form);
                form.addEvent('submit', function(evt){
                    evt.preventDefault();
                    if(dv.validate()){
                        form.send();
                    }
                });
            }
        });
    }

    if(document.id('pp_button')){
        document.id('pp_button').addEvent('click', function(evt){
	        evt.preventDefault();
            var el = evt.target;
	        new MavDialog({
	            'title': 'Rinvia Impegno',
	            'url': el.get('data-href') + '/format/html',
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
	                var form = document.id('pp_form');
	                var bf = new By0MessageForm('pp_form');
	                bf.addEvent('success', function(response){
                        var ob = response;
                        location.reload(true);
	                });
	                dv = new Form.Validator.Inline(form);
	                form.addEvent('submit', function(evt){
	                    evt.preventDefault();
	                    if(dv.validate()){
	                        form.send();
	                    }
	                });
	            }
	        });
	    });
    }
});