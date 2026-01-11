var onMoreAdded = function(el){
    if(el.hasClass('address')){
        el.getElement('.textboxlist').destroy();
        new AddressPicker(el, {prefix: 'contacts_addresses_'});
    }
};

window.addEvent('domready', function(){
    var cpwv;
    if(document.id('change_pw')){
	    document.id('change_pw').addEvent('click', function(evt){
	        evt.stop();
	        new MavDialog({
	            'title': 'Cambia Password',
	            'url': evt.target.get('title'),
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
    }

    var addp = new AddressPicker($$('.address')[0], {prefix: 'contacts_addresses_'});

    /*
    document.id('btn_new-contact').addEvent('click', function(e){
    	e.stop();
    	document.id('btn_select-contact').hide();
    	document.id('btn_new-contact').hide();
    	document.id('new-contact-form').reveal();
    });
    
    document.id('btn_select-contact').addEvent('click', function(e){
    	e.stop();
    	document.id('btn_new-contact').hide();
    	document.id('btn_select-contact').hide();
    	document.id('select-contact-form').reveal();
    });
    */
});
