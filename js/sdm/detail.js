window.addEvent('domready', function(){
    function powerUp(){
        var mrej = document.id('mod_reject');
        var msb = document.id('mod_sendback');
        var mrv = document.id('mod_review');
        var mnv = document.id('mod_noverify');
        var mr = document.id('mod_responsabile');
        var ms = document.id('mod_solver');
        var mt = document.id('mod_trattamento');
        var mv = document.id('mod_verifica');
        var mpv = document.id('mod_prevention_verifica');
        var mpnv = document.id('mod_prevention_noverify');
        var mpa = document.id('mod_prevention_action');

        if(mrej){
            mrej.addEvent('click', function(evt){
                evt.preventDefault();
                var el = evt.target;
                new MavDialog({
                    'title': 'Rifiuta Segnalazione',
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
                        var form = document.id('mod_reject_form');
                        var bf = new By0MessageForm('mod_reject_form');
                        bf.addEvent('success', function(response){
                            var ob = response;
                            reload();
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

        if(msb){
            msb.addEvent('click', function(evt){
                evt.preventDefault();
                var el = evt.target;
                new MavDialog({
                    'title': 'Rimanda Segnalazione',
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
                        var form = document.id('mod_sendback_form');
                        var bf = new By0MessageForm('mod_sendback_form');
                        bf.addEvent('success', function(response){
                            var ob = response;
                            reload();
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

        if(mrv){
            mrv.addEvent('click', function(evt){
                evt.preventDefault();
                var el = evt.target;
                new MavDialog({
                    'title': 'Revisiona Segnalazione',
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
                        var form = document.id('mod_review_form');
                        var bf = new By0MessageForm('mod_review_form');
                        bf.addEvent('success', function(response){
                            var ob = response;
                            reload();
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

        if(mt){
            mt.addEvent('click', function(evt){
                evt.preventDefault();
                var el = evt.target;
                new MavDialog({
                    'title': 'Modifica Informazioni Trattamento',
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
                        var form = document.id('mod_trattamento_form');
                        var bf = new By0MessageForm('mod_trattamento_form');
                        new By0DatePicker('date_resolution', {
                            pickerClass: 'datepicker_vista'
                        });
                        bf.addEvent('success', function(response){
                            var ob = response;
                            reload();
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

        if(mpa){
            mpa.addEvent('click', function(evt){
                evt.preventDefault();
                var el = evt.target;
                new MavDialog({
                    'title': 'Modifica Informazioni Azione Preventiva',
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
                        var form = document.id('mod_prevenzione_form');
                        var bf = new By0MessageForm('mod_prevenzione_form');
                        new By0DatePicker('date_prevention', {
                            pickerClass: 'datepicker_vista'
                        });
                        bf.addEvent('success', function(response){
                            var ob = response;
                            reload();
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
        
        if(mr){
            mr.addEvent('click', function(evt){
                evt.preventDefault();
                var el = evt.target;
                new MavDialog({
                    'title': 'Modifica Responsabile',
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
                        new By0DatePicker('date_feedback', {
                            pickerClass: 'datepicker_vista'
                        });
                        var form = document.id('mod_responsabile_form');
                        var bf = new By0MessageForm('mod_responsabile_form');
                        bf.addEvent('success', function(response){
                            var ob = response;
                            reload();
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

        if(ms){
            ms.addEvent('click', function(evt){
                evt.preventDefault();
                var el = evt.target;
                new MavDialog({
                    'title': 'Modifica Risolutore',
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
                        new By0DatePicker('date_expected_resolution', {
                            pickerClass: 'datepicker_vista'
                        });
                        var form = document.id('mod_solver_form');
                        var bf = new By0MessageForm('mod_solver_form');
                        bf.addEvent('success', function(response){
                            var ob = response;
                            reload();
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

        if(mnv){
            mnv.addEvent('click', function(evt){
                evt.preventDefault();
                var el = evt.target;
                new MavDialog({
                    'title': 'Soluzione non verificata',
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
                        new By0DatePicker('date_expected_resolution', {
                            pickerClass: 'datepicker_vista'
                        });
                        var form = document.id('mod_solver_form');
                        var bf = new By0MessageForm('mod_solver_form');
                        bf.addEvent('success', function(response){
                            var ob = response;
                            reload();
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

        if(mpnv){
            mpnv.addEvent('click', function(evt){
                evt.preventDefault();
                var el = evt.target;
                new MavDialog({
                    'title': 'Azione Preventiva non verificata',
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
                        new By0DatePicker('date_expected_prevention', {
                            pickerClass: 'datepicker_vista'
                        });
                        var form = document.id('mod_prevention_form');
                        var bf = new By0MessageForm('mod_prevention_form');
                        bf.addEvent('success', function(response){
                            var ob = response;
                            reload();
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
        
        if(mv){
            mv.addEvent('click', function(evt){
                evt.preventDefault();
                var el = evt.target;
                new MavDialog({
                    'title': 'Modifica Dati Verifica',
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
                        var form = document.id('mod_verifica_form');
                         new By0DatePicker('date_verifica', {
                            pickerClass: 'datepicker_vista'
                        });
                        var cb = jQuery('#prevenzione');
                        var sl = jQuery('#preventer-select');
                        cb.change(function(ev){
                            sl.slideToggle();
                        });
                        var bf = new By0MessageForm('mod_verifica_form');
                        bf.addEvent('success', function(response){
                            var ob = response;
                            reload();
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

        if(mpv){
            mpv.addEvent('click', function(evt){
                evt.preventDefault();
                var el = evt.target;
                new MavDialog({
                    'title': 'Modifica Dati Verifica Azione Preventiva',
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
                        var form = document.id('mod_prevention_verifica_form');
                        new By0DatePicker('date_prevention_verifica', {
                            pickerClass: 'datepicker_vista'
                        });
                        var bf = new By0MessageForm('mod_prevention_verifica_form');
                        bf.addEvent('success', function(response){
                            var ob = response;
                            reload();
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
    }
    
    var rlreq;
    function reload(){
        if(!rlreq){
            rlreq = new Request.HTML({
                url: baseUrl + '/sdm/detail/id/' + document.id('sdm_id').get('value') + '/format/html',
                update: 'main_sdm',
                useSpinner: true,
                onSuccess: function(r){
                    powerUp();
                }
            });
        }
        rlreq.send();
    }
    
    powerUp();
});