

var onMoreAdded = function(el){    
    if(el.hasClass('rcadded')){
        el.getElement('.textboxlist').destroy();
        new TextboxList(el.getElement('.rc_tbl'), {
            unique: true,
            max: 1,
            plugins: {
                autocomplete: {
                    minLength: 0,
                    queryRemote: true,
                    remote: {
                        url: baseUrl + '/common/users/type/rc/format/json'
                    }
                }
            }
        });
    }
};

/* POWER MOMENTS Consuntivo*/
function powMoms(){
    var mm = $$('.moment_open');
    mm.addEvent('click', function(ev){
        ev.preventDefault();
        loadCons(ev.target);
    });

    $$('.corder_open').addEvent('click', function(ev){
        ev.preventDefault();
        loadGlobalCons(ev.target);
    });

    var ccosv;
    $$('.m_done').addEvent('click', function(e){
        e.stop();
        
        var id_moment = e.target.get('id').substring(2);
        //var val_cons = document.id('imp_cons_' + id_moment).get('text'); 

        new MavDialog({
            'title': 'Fase conclusa',
            'url': baseUrl + '/orders/closefase/format/html/id/' + id_moment,
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
                var form = document.id('close_form');
                var bf = new By0MessageForm('close_form');
                bf.addEvent('success', function(response){
                    var id = document.id('id').get('value');
                    updateCons(id);
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

    $$('.m_edit').addEvent('click', function(evt){

        var pos = document.getScroll().y;
        evt.stop();

        var id_moment = evt.target.get('id').substring(2);

        new MavDialog({
            'title': 'Modifica Consuntivo',
            'url': baseUrl + '/orders/mcm/format/html/id_moment/' + id_moment + '/id_order/' + document.id('id').get('value'),
            'loadingMessage': '<div class="spinner-simple">caricamento...</div>',
            fxShow: {
                'top' : [pos - 400, pos]
            },
            fxHide: {
                'top' : pos - 400
            },
            ok: false,
            width: 712,
            title: false,
            onMessageSet: function(){
                application.initDateFields();
                var form = document.id('mcm_form');
                var mcbf = new By0MessageForm('mcm_form');
                mcbf.addEvent('success', function(response){
                    //updateMCons(id_moment, evt.target.getParent('td'));
                    updateCons(document.id('id').get('value'));
                });
                mcv = new Form.Validator.Inline(form);
                form.addEvent('submit', function(evt){
                    evt.preventDefault();
                    if(document.id('date_done')){
                        if(document.id('date_done').get('value') != '' && !confirm('Stai per chiudere la fase di lavorazione. Sei sicuro?')){
                            return;
                        }
                    }
                    if(mcv.validate()){
                        form.send();
                    }
                });
                document.id('c_n_km').addEvent('change', function(ev){
                    var v = ev.target.get('value') / 100;
                    var h = Math.floor(v);
                    var m = Math.round(((v - h) * 60) / 10) * 10;
                    $$('#c_ore_viaggio_minute option').each(function(opt){
                        if(opt.get('value') <= m){
                            opt.set('selected', 'selected');
                        }
                    });
                    if(v) document.id('c_ore_viaggio_hour').set('value', h);
                });
                document.id('c_costo_km').addEvent('change', function(ev){
                    var v = ev.target.get('value') * document.id('c_n_km').get('value');
                    if(v) document.id('tot_km_cost').set('text', 'costo totale: ' + v + ' €');
                        else document.id('tot_km_cost').set('text', '').toFixed(2);
                });
            }
        });
    });
}

function powMomsP(){
    var mm = $$('.mpm');
    mm.addEvent('click', function(ev){
        ev.preventDefault();
        var id = document.id('id').get('value');
        var mid = ev.target.get('id').substring(4);
        new MavDialog({
            'title': 'Modifica Pianificazione Momento di Lavorazione',
            'url': baseUrl + '/orders/mpm/format/html/mid/' + mid + '/id/' + id,
            'loadingMessage': '<div class="spinner-simple">caricamento...</div>',
            fxShow: {
                'top' : [-400, 0]
            },
            fxHide: {
                'top' : -400
            },
            ok: false,
            width: 712,
            title: false,
            onMessageSet: function(){
                application.initDateFields();
                var form = document.id('mpm_form');
                var mpmbf = new By0MessageForm('mpm_form');
                mpmbf.addEvent('success', function(response){
                    updatePlan(id);
                });
                mpmv = new Form.Validator.Inline(form);
                form.addEvent('submit', function(evt){
                    evt.preventDefault();
                    if(mpmv.validate()){
                        form.send();
                    }
                });
                linkHours();
            }
        }); 
    });
}

function power_upload(){
    var sid = jQuery('#sid').val();
    var fileUpload = jQuery('#file_upload');
console.log(sid);
console.log(jQuery('#id').val());
    fileUpload.uploadify({
        'debug': true,
        'uploader'  : baseUrl + '/js/uploadify/uploadify.swf',
        'script'    : baseUrl + '/files/uploadorder',
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

window.addEvent('domready', function(){
    powerPlans();
    powerCons();
    powerComm();
    
    powMoms();
    powMomsP();

    power_upload();

    if(document.id('cancel-order-link')){
        document.id('cancel-order-link').addEvent('click', function(evt){
            if(document.id('id_status').get('value') == 3){
                if(!confirm("Stai per annullare una commessa che risulta completata. \nVuoi continuare con l\'operazione?")){
                    return false;
                }
            }
        });
    }
});

function loadCons(el){
    var aid = el.get('id');
    var ntdid = 't' + aid;
    var ntd = document.id(ntdid);
    var id_moment = aid.substring(3);
    if(!ntd){
        var ntr = new Element('tr', {'class' : 'moment_detail_tr'})
                .adopt(ntd = new Element('td', {'id' : ntdid, 'class' : 'moment_detail_td', 'colspan' : 8, 'html' : '<div class="spinner-simple">caricamento...</div>'}));
        ntr.inject(el.getParent('tr'), 'after');
        updateMCons(id_moment, ntd);
    }else{
        ntd.destroy();
    }
}

function loadGlobalCons(el){
    var aid = el.get('id');
    var ntdid = 'tg' + aid;
    var ntd = document.id(ntdid);
    var id_order = aid.substring(3);
    if(!ntd){
        var ntr = new Element('tr', {'class' : 'moment_detail_tr'})
                .adopt(ntd = new Element('td', {'id' : ntdid, 'class' : 'moment_detail_td', 'colspan' : 8, 'html' : '<div class="spinner-simple">caricamento...</div>'}));
        ntr.inject(el.getParent('tr'), 'after');
        updateGCons(id_order, ntd);
    }else{
        ntd.destroy();
    }
}


var mcreq;
function updateMCons(id_moment, td){
    //if(!mcreq){
     mcreq = new Request.HTML({
        url: baseUrl + '/orders/ucm/format/html/',
        method: 'post',
        update: td,
        useSpinner: true,
        onSuccess: function(){
            //application.initMenus();
            powerMCons(id_moment);
        }
    });
    //}
    mcreq.send({
        data:{
            id_moment: id_moment,
            id_order: document.id('id').get('value')
        }
    });
}

var gcreq;
function updateGCons(id_order, td){
    //if(!mcreq){
     gcreq = new Request.HTML({
        url: baseUrl + '/orders/ucg/format/html/',
        method: 'post',
        update: td,
        useSpinner: true,
        onSuccess: function(){
            //application.initMenus();
            //powerGCons(id_order);
        }
    });
    //}
    gcreq.send({
        data:{
            id_order: id_order
        }
    });
}


function powerComm(){
    var cnv;
    if(document.id('mcomm')){
        document.id('mcomm').addEvent('click', function(evt){
            evt.stop();
            var id = document.id('id').get('value');
            new MavDialog({
                'title': 'Modifica Commessa',
                'url': baseUrl + '/orders/mcomm/format/html/id/' + id,
                'loadingMessage': '<div class="spinner-simple">caricamento...</div>',
                fxShow: {
                    'top' : [-400, 0]
                },
                fxHide: {
                    'top' : -400
                },
                ok: false,
                width: 712,
                title: false,
                onMessageSet: function(){
                    var form = document.id('mcomm_form');
                    var mpbf = new By0MessageForm('mcomm_form');
                    mpbf.addEvent('success', function(response){
                        document.id('note_detail').set('html', document.id('note').get('value'));
                    });
                    mpv = new Form.Validator.Inline(form);
                    form.addEvent('submit', function(evt){
                        evt.preventDefault();
                        if(mpv.validate()){
                            form.send();
                        }
                    });
                }
            }); 
        });
    }
}


function powerPlans(){
    var csv;
    if(document.id('mp')){
        document.id('mp').addEvent('click', function(evt){
            evt.stop();
            var id = document.id('id').get('value');
            new MavDialog({
                'title': 'Modifica Pianificazione',
                'url': baseUrl + '/orders/mp/format/html/id/' + id,
                'loadingMessage': '<div class="spinner-simple">caricamento...</div>',
                fxShow: {
                    'top' : [-400, 0]
                },
                fxHide: {
                    'top' : -400
                },
                ok: false,
                width: 712,
                title: false,
                onMessageSet: function(){
                    application.initDateFields();
                    var form = document.id('mp_form');
                    form.getElements('.rc_tbl').each(function(el){
                        new TextboxList(el, {
                            unique: true,
                            max: 1,
                            plugins: {
                                autocomplete: {
                                    minLength: 0,
                                    queryRemote: true,
                                    remote: {
                                        url: baseUrl + '/common/users/type/rc/format/json'
                                    }
                                }
                            }
                        });
                    });
                    
                    var mpbf = new By0MessageForm('mp_form');
                    mpbf.addEvent('success', function(response){
                        window.location.reload();
                        updatePlan(id);
                    });
                    mpv = new Form.Validator.Inline(form);
                    form.addEvent('submit', function(evt){
                        evt.preventDefault();
                        if(mpv.validate()){
                            form.send();
                        }
                    });
                    adderAdd();
                    //linkHours();
                }
            }); 
        });
    }

    $$('.incarico-link').addEvent('click', function(e){
        if(!confirm('Sei sicuro di voler prendere in carico la lavorazione di questa commessa?')){
            e.stop();
        }
    });
}

function powerCons(){
    if(document.id('mc')){
        document.id('mc').addEvent('click', function(evt){
            var pos = document.getScroll().y;
            evt.stop();
            var id = document.id('id').get('value');
            new MavDialog({
                'title': 'Modifica Consuntivo',
                'url': baseUrl + '/orders/mc/format/html/id/' + id,
                'loadingMessage': '<div class="spinner-simple">caricamento...</div>',
                fxShow: {
                    'top' : [pos - 400, pos]
                },
                fxHide: {
                    'top' : pos - 400
                },
                ok: false,
                width: 712,
                title: false,
                onMessageSet: function(){
                    var form = document.id('mc_form');
                    var tbl_moment = new TextboxList(form.getElement('.ente'), {
                        //unique: true,
                        max: 1,
                        plugins: {
                            autocomplete: {
                                minLength: 0,
                                queryRemote: true,
                                remote: {
                                    url: baseUrl + '/orders/enti/format/json'
                                }
                            }
                        }
                    });
                    
                    var mcbf = new By0MessageForm('mc_form');
                    mcbf.addEvent('success', function(response){
                        updateCons(id);
                    });
                    mcv = new Form.Validator.Inline(form);
                    form.addEvent('submit', function(evt){
                        evt.preventDefault();
                        if(mcv.validate()){
                            form.send();
                        }
                    });
                }
            }); 
        });
    }
}

function powerMCons(id_moment){
    if(document.id('mcm_' + id_moment)){
        document.id('mcm_' + id_moment).addEvent('click', function(evt){
            var pos = document.getScroll().y;
            evt.stop();
            
            new MavDialog({
                'title': 'Modifica Consuntivo',
                'url': baseUrl + '/orders/mcm/format/html/id_moment/' + id_moment + '/id_order/' + document.id('id').get('value'),
                'loadingMessage': '<div class="spinner-simple">caricamento...</div>',
                fxShow: {
                    'top' : [pos - 400, pos]
                },
                fxHide: {
                    'top' : pos - 400
                },
                ok: false,
                width: 712,
                title: false,
                onMessageSet: function(){
                    application.initDateFields();
                    var form = document.id('mcm_form');
                    var mcbf = new By0MessageForm('mcm_form');
                    mcbf.addEvent('success', function(response){
                        //updateMCons(id_moment, evt.target.getParent('td'));
                        updateCons(document.id('id').get('value'));
                    });
                    mcv = new Form.Validator.Inline(form);
                    form.addEvent('submit', function(evt){
                        evt.preventDefault();
                        if(mcv.validate()){
                            form.send();
                        }
                    });
                }
            });
        });
    }
}


var upreq;
function updatePlan(id){
    if(!upreq){
     upreq = new Request.HTML({
        url: baseUrl + '/orders/up/format/html/',
        method: 'post',
        update: 'pianificazione_div',
        useSpinner: true,
        onComplete: function(){
            application.initMenus();
            powerPlans();
            powMomsP();
        }
    });   
    }
    upreq.send({
        data:{
            id: id
        }
    });
}

var ucreq;
function updateCons(id){
    if(!ucreq){
     ucreq = new Request.HTML({
        url: baseUrl + '/orders/uc/format/html/',
        method: 'post',
        update: 'consuntivo_div',
        useSpinner: true,
        onComplete: function(){
            application.initMenus();
            powMoms();
            powerCons();
        }
    });   
    }
    ucreq.send({
        data:{
            id: id
        }
    });
}

function linkHours(){
    var u = document.id('valore_g_uomo');
    var o = document.id('ore_hm');
    var g = document.id('gg_hm');
    var t = document.id('total').get('value');
    u.addEvent('change', function(e){
        var v =  u.get('value');
        if(v && v != 0){
            var ov = (t / v * 8);
            var gv = t / v;
            o.set('value', ov.toFixed(2));
            g.set('value', gv.toFixed(2));
        }
    });
    o.addEvent('change', function(e){
        var v =  o.get('value');
        if(v && v != 0){
            var uv = (t / v * 8);
            var gv = t / uv;
            u.set('value', uv.toFixed(2));
            g.set('value', gv.toFixed(2));
        }
    });
    g.addEvent('change', function(e){
        var v =  g.get('value');
        if(v && v != 0){
            var ov = v * 8;
            var uv = (t / v);
            u.set('value', uv.toFixed(2));
            o.set('value', ov.toFixed(2));
        }
    });
}