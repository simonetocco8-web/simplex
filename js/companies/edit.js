
var onMoreAdded = function(el){
    if(el.hasClass('address')){
        el.getElement('.textboxlist').destroy();
        new AddressPicker(el, {prefix: 'contacts_addresses_'});
    }
};

var Simplex = new Class({
    Extends: Simplex,
    initialize: function(options){
        this.parent(options);
        this.initPromotore();
        this.initIban();
        this.initAddress($$('.address')[0]);
        this.initChecker();
        this.initSegnalatoDa();
        this.initPromotore();
        this.initPromotore2();
        this.initRS();
    },
    initRS: function(){
        document.id('ragione_sociale').addEvent('keyup', function(){
            this.set('value', this.get('value').toUpperCase());
        });
    },
    initSegnalatoDa: function(){
        var tbl_sd = new TextboxList('segnalato_da', {
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
    },
    initPromotore2: function(){
        var tbl_pr = new TextboxList('id_promotore', {
            unique: true,
            max: 1,
            plugins: {
                autocomplete: {
                    minLength: 0,
                    queryRemote: true,
                    remote: {
                        url: baseUrl + '/common/companies/type/promotori/format/json'
                    }
                }
            }
        });
        var id_promotore = parseInt(document.id('id_promotore2').get('value'));
        if(id_promotore){
            tbl_pr.add(document.id('promotore_name').get('value'), id_promotore);
        }
    },
    initIban: function(){
        document.id('iban').meiomask('fixed.Iban');
    },
    initPromotore: function(){
        var isp = document.id('is_promotore');
        var ispp = document.id('promotore_percent').getParent('dl');
        isp.addEvent('click', function(evt){
            if(!isp.get('checked')) ispp.show();
            else ispp.hide();
        });
        if(!isp.get('checked')){
            ispp.hide();
        }
    },
    initAddress: function(element){
        new AddressPicker(element, {prefix: 'contacts_addresses_'});
    },
    initChecker: function(){
        this.pimsg = document.id('pi-msg').slide('hide');
        this.cid = document.id('company_id');
        this.cdata = document.id('company_data');
        this.pi = document.id('partita_iva');
        if(this.cid.get('value') === '' && document.id('new_company').get('value') == 1){
            this.cdata.hide();
            document.id('edit').addEvent('submit', this.preventSubmit);
        }
        document.id('pi_check').addEvent('click', this.checkPI.bind(this));
        document.id('pi_temp').addEvent('click', this.tempPI.bind(this));
    },
    tempPI: function(ev){
        this.pimsg.slide('out');
        ev.preventDefault();
        this.pi.set('value', 'Provvisoria');
        this.PIChecked({company: {temp: 1}});
    },
    preventSubmit: function(ev){
        ev.stop();
    },
    getPIReq: function(){
        if(!this.pir){
            this.pir = new Request.JSON({
                url: baseUrl + '/companies/detail/format/json',
                onSuccess: this.PIChecked.bind(this)
            });
        }
        return this.pir;
    },
    checkPI: function(ev){
        ev.preventDefault();
        var v = this.pi.get('value').trim();
        if(v.length != 11) return;
        if(v == 'Provvisoria') return;
        this.pimsg.slide('in');
        this.getPIReq().post({'pi': v});
    },
    PIChecked: function(o){
        if(!o.company.company_id){
            if(o.company.temp){
                this.pimsg.set('html', '<b>Attenzione!</b><br>Stai registrando una azienda senza inserire la Partita IVA. ' +
                                       'Tale azienda sar&agrave; registrata in maniera <b>provvisoria</b>.<br>' +
                                       'Sar&agrave; comunque possibile registrare offerte ed impegni per la stessa ma ' +
                                       '<b>non si potranno aggiudicare offerte</b> fin quando non sar&agrave; inserita la ' +
                                       'Partita IVA.').slide('in');
            }else{
                //new company
                this.pimsg.set('html', '<b>nuova azienda</b>').slide('in');
            }
            this.cdata.show();
            document.id('edit').removeEvent('submit', this.preventSubmit);
        }else{
            var tint = document.id('internal_id').get('value');
            var ex = false;
            o.company.internals.each(function(e){
                if(e.internal_id == tint) ex = true;
            });
            if(ex){
                this.pimsg.set('html','Un\'azienda con questa partita iva &egrave; gi&agrave; presente nel sistema.<br />' +
                    'Modifica la partita iva inserita o vedi il dettaglio dell\'<a href="' +
                    baseUrl + '/companies/detail/id/' + o.company.company_id + '" ' +
                    'class="link-button">azienda</a>').slide('in');
            }else{
                this.pimsg.set('html', 'Un\'azienda con questa partita iva &egrave; gi&agrave; presente nel sistema per un altra azienda interna.<br />' +
                    'Modifica la partita iva inserita o ' +
                    '<a href="' +baseUrl + '/companies/setint/id/' + o.company.company_id + '/int/' + tint + '" class="link-button">associa</a>' +
                    ' i dati dell\'azienda anche a questa azienda interna').slide('in');
            }
        }
    }
});