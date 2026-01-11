var Simplex = new Class({
    Extends: Simplex,
    initialize: function(options){
        this.parent(options);
        this.initExclude();
        this.initLinkOffice();
    },
    initExclude: function(){
        if(document.id('ms')){
            this.excludeLink = document.id('ms').addEvent('click', this.excludeClick.bind(this));
        }
    },
    excludeClick: function(ev){
        ev.preventDefault();
        this.getExcludeReq().send();
    },
    getExcludeReq: function(){
        if(!this.excludeReq){
            this.excludeReq = new Request.JSON({
                url: this.excludeLink.get('href'),
                onSuccess: this.excludedCB.bind(this),
                onFailure: this.excludeFailure.bind(this)
            });
        }
        return this.excludeReq;
    },
    excludedCB: function(e){
        if(e.result){
            application.notifyWithStatus(e.message, 'success');
            var img = this.excludeLink.getElement('img').dispose();
            this.excludeLink.set('text', '').adopt(img).appendText(e.newLinkText);
        }else{
            application.notifyWithStatus(e.message, 'error');
        }
    },
    excludeFailure: function(e){
        application.notifyWithStatus('Impossibile avviare la richiesta', 'error');
    },
    initLinkOffice: function(){
        var l = document.id('lo_link');
        if(l) l.addEvent('click', this.linkOffice.bind(this));
    },
    linkOffice: function(ev){
        ev.preventDefault();
        new MavDialog({
            'title': 'Cambia Sede di Riferimento',
            'url': ev.target.get('href'),
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
                    document.id('lo_text').set('text', response.office);
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
    }
});