window.addEvent('domready', function(){
    function powerMenu(){
        document.id('crev').addEvent('change', function(e){
    	    e.stop;
    	    var id_offer = document.id('id_offer').get('value');
    	    var rev = e.target.get('value');
    	    var year = document.id('year').get('value');
    	    location.href=baseUrl + "/offers/detail/id_offer/" + id_offer + '/y/' + year + "/r/" + rev;
        });
        
        if(document.id('arev')){
	        var arevreq = new Request.JSON({
	    	    url: baseUrl + "/offers/cr/format/json",
	    	    onSuccess: function(json){
	    		    if(json.result){
	    			    application.roar('Operazione effettuata', 'La revisione &egrave; stata attivata con successo');
	    			    application.alert('Operazione effettuata', 'La revisione &egrave; stata attivata con successo');
                        document.id('arev').destroy();
	    		    }else{
	    			    this.onFailure();
	    		    }
	    	    },
	    	    onFailure: function(){
	    		    application.roar('Problemi', 'Impossibile effettuare l\'operazione.');
	    	    }
	        });
	        document.id('arev').addEvent('click', function(e){
	    	    e.stop;
	    	    var id_offer = document.id('id_offer').get('value');
	    	    var rev = document.id('rev').get('value');
	    	    
	    	    arevreq.post({
	    		    'id_offer': id_offer,
	    		    'r': rev
	    	    });
	        });
        }
        
        var csv;
        if(document.id('cs')){
	        document.id('cs').addEvent('click', function(evt){
	            evt.stop();
	            var id = document.id('id').get('value');
	            new MavDialog({
	                'title': 'Cambia Stato',
	                'url': baseUrl + '/offers/cs/format/html/id/' + id,
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
	                    var form = document.id('cs_form');
	                    var bf = new By0MessageForm('cs_form');
	                    bf.addEvent('success', function(response){
                            updateTop(response.id);
	                	    //document.id('stato_offerta').set('text', response.status + ' | ');
	                    });
	                    csv = new Form.Validator.Inline(form);
	                    form.addEvent('submit', function(evt){
	                        evt.preventDefault();
	                        if(csv.validate()){
	                            form.send();
	                        }
	                    });
	                }
	            }); 
	        });
        }
      
        var ccosv;
        if(document.id('cco')){
	        document.id('cco').addEvent('click', function(evt){
	            evt.stop();
	            var id = document.id('id').get('value');
	            new MavDialog({
	                'title': 'Crea Commessa',
	                'url': baseUrl + '/offers/cco/format/html/id/' + id,
	                'loadingMessage': '<div class="spinner-simple">caricamento...</div>',
	                fxShow: {
	                    'top' : [-400, 0]
	                },
	                fxHide: {
	                    'top' : -400
	                },
	                ok: false,
	                width: 412,
	                title: false,
	                onMessageSet: function(){
	                    var form = document.id('cco_form');
	                    var bf = new By0MessageForm('cco_form');
	                    bf.addEvent('success', function(response){
                            updateTop(id);
	                	    //window.location = baseUrl + 'offers/detail/id/' + id;
	                    });
	                    ccosv = new Form.Validator.Inline(form);
	                    form.addEvent('submit', function(evt){
	                        evt.preventDefault();
	                        if(ccosv.validate()){
	                            form.send();
	                        }
	                    });
	                }
	            }); 
	        });
        }
    }
    
    powerMenu();
    
    var topreq;
    function updateTop(id){
        if(!topreq){
             topreq = new Request.HTML({
                url: baseUrl + '/offers/top/format/html/',
                method: 'post',
                update: 'offer-top-div',
                useSpinner: true,
                onComplete: function(){
                    powerMenu();
                    power_upload();
                    application.initMenus();
                }
            });   
        }
        topreq.send({
            data:{
                id: id
            }
        });
    }

    function power_upload(){
        var sid = jQuery('#sid').val();
        var fileUpload = jQuery('#file_upload');

        fileUpload.uploadify({
            'debug': true,
            'uploader'  : baseUrl + '/js/uploadify/uploadify.swf',
            'script'    : baseUrl + '/files/uploadoffer',
            'cancelImg' : baseUrl + '/js/uploadify/cancel.png',
            'buttonText': 'carica pdf',
            'method'    : 'post',
            'fileDesc'  : 'Documenti (Pdf)',
            'fileExt'  : '*.pdf',
            'scriptData': {
                'PHPSESSID': sid,
                'id': jQuery('#id').val()
            },
            'multi'     : false,
            //'folder'    : '/uploads',
            'auto'      : true,
            //'onComplete': function(event, ID, fileObj, response, data){
            'onComplete': function(){
                window.location.reload();
            },
            'onSelectOnce': function(){
                fileUpload.uploadifySettings('scriptData', {
                    'id': jQuery('#id').val()
                });
                fileUpload.uploadifyUpload();
            }
        });
    }

    power_upload();
});