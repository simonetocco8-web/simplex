var getCompanyId = function() {
    return {id: document.id('id_company').get('value')};
};

var getSubServiceId = function() {
    return {where_value: document.id('id_subservice').get('value')};
};

var onMoreRemoved = function(el){ 
    calcTotals();
    assignMomentIndexes();
};

var assignMomentIndexes = function(){
    var mm = $$('.mmf'), mi = $$('.moment_index'), idx = 0;
    mm.each(function(m){
        m.set('name', 'moments_fatturazione_' + idx);
        m.getPrevious('input').set('name', 'moments_fatturazione_' + idx++);
    });
    idx = 0;
    mi.each(function(m){
        m.set('value', idx++);
    });
}

var onMoreAdded = function(el){
    el.getElement('.textboxlist').destroy();
    var eldate = el.getElement('.validate-date');
    var dp = new By0DatePicker(el.getElement('.validate-date'), {
        pickerClass: 'datepicker_vista'
    });
    assignMomentIndexes();
    var ft = el.getElement('input[type=checkbox]').set('checked', false).set('value' , '1');
    eldate.addEvent('blur', function(ev){
        checkDates.delay(100, ev);
    });
    
    var tbl_moment = new TextboxList(el.getElement('.moment-tipologia'), {
        //unique: true,
        max: 1,
        plugins: {
            autocomplete: {
                minLength: 0,
                queryRemote: true,
                remote: {
                    url: baseUrl + '/common/tbl/table/moment_defs/field/name/where_field/id_subservice/format/json',
                    extraParams: getSubServiceId
                }
            }
        }
    });
    
    def_form_validator = new Form.Validator.Inline(document.id('edit'), {
            ignoreHidden: false
    });
    
    calcTotals();
    doValore(el);
};

var calcTotals = function(){
    var s = parseFloat(document.id('sconto').get('value'));
    if(!s) s = 0;
    var pts = $$('input[name=promotore_value_flag]:checked');
    var pt = false;
    if(pts.length > 0) pt = pts[0].get('value');
    var pp = 0;
    var pv = 0;
    switch(pt){
        case 'V':
            pv = parseFloat(document.id('promotore_value').get('value'));
            if(!pv) pv = 0;
           break;
        case 'P':
            pp = parseFloat(document.id('promotore_percent').get('value'));
            if(!pp) pp = 0;
            break;
    }

    var t = parseFloat(0);
    $$('.moment-importo').each(function(el){
        var div_ = el.getParent('div');
        el.addEvent('blur', function(ev){
            doValore(div_);
            calcTotals();
        });
        var i = parseFloat(el.get('value'));
        if(!i) i = 0;
        t += i;
        doValore(div_);
        calcValore(div_);
    });
    document.id('t_totale').set('text', t + ' euro'); 
    var ts = parseFloat(t - (t * s / 100));
    document.id('t_totalescontato').set('text',  ts + ' euro');
    var al_partner = 0;
    if(pt == 'P'){
        al_partner = (ts * pp / 100) + ' euro'
    }else if(pt == 'V'){
        al_partner = pv + ' euro'
    }
    document.id('t_alpartner').set('text', al_partner);
}

var doValore = function(div){
    var imp = parseFloat(div.getElement('.moment-importo').get('value')); 
    if(!imp){
        div.getElement('.valore-g-uomo').set('value', '').set('readonly', true);
        div.getElement('.moment-ore').set('value', '').set('readonly', true);
        div.getElement('.moment-giorni').set('value', '').set('readonly', true);
    }else{
        div.getElement('.valore-g-uomo').set('readonly', false);
        div.getElement('.moment-ore').set('readonly', false);
        div.getElement('.moment-giorni').set('readonly', false);
        calcValore(div);
    }
}

var calcValore = function(div){
    var u = div.getElement('.valore-g-uomo');
    var o = div.getElement('.moment-ore');
    var g = div.getElement('.moment-giorni');
    var t = div.getElement('.moment-importo').get('value');
    var s = parseFloat(document.id('sconto').get('value'));
    if(s){
        t = t - (t * s / 100);
    }
    
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
    
    var v = parseFloat(u.get('value'));
    if(v){
        var ov = (t / v * 8);
        var gv = t / v;
        o.set('value', ov.toFixed(2));
        g.set('value', gv.toFixed(2));
    }
}

var checkDates = function(){
    var dates = $$('.moment_date');
    var last_checked = null;
    dates.each(function(el){
        if((v = el.get('value')) != ''){
            var v = Date.parse(v);
            if(v.isValid()){
                if(last_checked != null && v.diff(last_checked) > 0){
                    el.set('value', '');
                }else{
                    last_checked = v;
                }
            }
        }
    });
}

window.addEvent('domready', function() {
    var csreq = new Request.HTML();
    var ppreq = new Request.JSON();

    var tbl_luogo;
    var tbl_moment;
    var tbl_sd;

    def_form_validator = new Form.Validator.Inline(document.id('edit'), {
            ignoreHidden: false
    });
    
    document.id('moments_expected_date').addEvent('blur', function(ev){
        checkDates.delay(100, ev);
    });
    
    document.id('id_service').addEvent('change', function(e) {
        var s = e.target;
        var id = s.get('value');

        csreq.setOptions({
            url: baseUrl + '/admin/subservicesbyserviceforselect/format/html/id/' + id,
            update: document.id('id_subservice')
        }).send();
    });

    document.id('id_promotore').addEvent('change', function(e) {
        var s = e.target;
        var id = s.get('value');
        if(id != 0){
            ppreq.setOptions({
                url: baseUrl + '/companies/percentforpartner/format/json/id/' + id,
                onSuccess: function(o) {
                    document.id('promotore_percent').set('value', o.result);
                }
            }).send();
        }else{
            document.id('promotore_percent').set('value', '');
        }
    });

    if(document.id('nr-button')) document.id('nr-button').addEvent('click', function(){
        document.id('nr').set('value', 1);
    });

    document.id('ppp').addEvent('change', function(e){
        var r = e.target;
        var c = r.get('checked');
        if(c){
            document.id('pppv').hide();
            document.id('pppf').show();
            calcTotals();
        }
    });
    document.id('ppv').addEvent('change', function(e){
        var r = e.target;
        var c = r.get('checked');
        if(c){
            document.id('pppf').hide();
            document.id('pppv').show();
            calcTotals();
        }
    });

    tbl_luogo = new TextboxList('luogo', {
        unique: true,
        max: 1,
        plugins: {
            autocomplete: {
                minLength: 0,
                queryRemote: true,
                remote: {
                    url: baseUrl + '/companies/acaddresses/format/json',
                    extraParams: getCompanyId
                }
            }
        }
    });
    
    tbl_sd = new TextboxList('segnalato_da', {
        unique: true,
        max: 1,
        plugins: {
            autocomplete: {
                minLength: 0,
                queryRemote: true,
                remote: {
                    url: baseUrl + '/common/users/format/json'
                }
            }
        }
    });

    $$('.moment-tipologia').each(function(el){
        tbl_moment = new TextboxList(el, {
            //unique: true,
            max: 1,
            plugins: {
                autocomplete: {
                    minLength: 0,
                    queryRemote: true,
                    remote: {
                        url: baseUrl + '/common/tbl/table/moment_defs/field/name/where_field/id_subservice/format/json',
                        extraParams: getSubServiceId
                    }
                }
            }
        });
    });
    

    var linked_callback = function(el) {
        var d1 = document.id('date_offer');
        var d2 = document.id('date_end');
        var g = document.id('validita');
        var gv = parseInt(g.get('value'));
        var d1v = d1.get('value');
        var d2v = d2.get('value');

        switch (el) {
            case d1:
                if (gv != '') {
                    d2.set('value', Date.parse(d1v).increment('day', gv).format('%x'));
                } else {
                    if (d2v != '') {
                        g.set('value', Date.parse(d1v).diff(Date.parse(d2v)));
                    }
                }
                break;
            case g:
                if (d1v != '') {
                    d2.set('value', Date.parse(d1v).increment('day', gv).format('%x'));
                }
                break;
            case d2:
                if (d1v != '') {
                    g.set('value', Date.parse(d1v).diff(Date.parse(d2v)));
                }
                break;
        }
    };

    var lf = new By0LinkedFields($$('.linked1'), {event: 'blur', callback: linked_callback, delay: 100});
    
    document.id('sconto').addEvent('blur', function(){
        calcTotals();
    });
    document.id('promotore_percent').addEvent('blur', function(){
        calcTotals();
    });
    document.id('promotore_value').addEvent('blur', function(){
        calcTotals();
    });
    calcTotals();
});
