var Simplex = new Class({
    Extends: Simplex,
    initialize: function(options){
        this.parent(options);
        this.initImporti();
        this.initAddMore();
        
        this.calc(null);
        this.recalculate();
    },
    initImporti: function(){
        $$('.moment-importo, .moment-iva, .moment-sconto, .spese').addEvent('change', this.calc.bind(this));
    },
    calc: function(ev){
        var imps = $$('.moment-importo');
        var tval = 0, tiva = 0, tivato = 0;
        imps.each(function(e,i){
            var val = parseFloat(e.get('value'));
            var tr = e.getParent('tr');
            if(val){
                var sconto = parseFloat(tr.getElement('.moment-sconto').get('value'));
                if(sconto) val = val - val * sconto / 100;
                tr.getElement('.moment-scontato').set('text', val);
                tval += val;
                var iva = parseFloat(tr.getElement('.moment-iva').get('value'));
                if(iva){
                    var ivar = val * iva / 100;
                    tiva += ivar;
                    val = val + ivar;
                }
                tivato += val;
            }
        });
        var trasferta = parseFloat(document.id('trasferta').get('value'));
        if(trasferta) {
//            tval += trasferta;
            tivato += trasferta;
        }
        var varie = parseFloat(document.id('varie').get('value'));
        var varie_iva = parseFloat(document.id('varie_iva').get('value'));
        var varie_tot = 0;
        if(varie){
            var varie_iva_val = 0;
            if(varie_iva){
                varie_iva_val = varie * varie_iva / 100;
            }
            varie_tot = varie + varie_iva_val;

  //          tval += varie;
            tivato += varie_tot;
  //          tiva += varie_iva_val;
        }
        if(varie_tot){
            document.id('varie-tot').set('text', varie_tot.toFixed(2));
        }else{
            document.id('varie-tot').set('text', '');
        }

        document.id('imponibile').set('text', tval.toFixed(2));
        document.id('totale_a_pagare').set('text', tivato.toFixed(2));
        document.id('tiva').set('text', tiva.toFixed(2));
        this.recalculate();
    },
    initAddMore: function(){
        document.id('add-more').addEvent('click', this.addMore.bind(this));
        $$('.remove-more-td')[0].setStyle('display', 'none');
        $$('.remove-more-td').addEvent('click', this.removeTr.bind(this));
        $$('.importo-rata').addEvent('blur', this.recalculate.bind(this));
        this.recalculate();
    },
    addMore: function(ev){
        ev.preventDefault();
        var tr = document.id('tr-to-add');
        var ntr = tr.clone();
        tr.getParent('tbody').adopt(ntr);
        ntr.getElement('.remove-more-td').setStyle('display', 'block').addEvent('click', this.removeTr.bind(this));
        ntr.getElement('.importo-rata').addEvent('blur', this.recalculate.bind(this));
        new By0DatePicker(ntr.getElement('.validate-date'), {pickerClass: 'datepicker_vista' });
        this.recalculate();
    },
    removeTr: function(ev){
        ev.preventDefault();
        ev.target.getParent('tr').destroy();
        this.recalculate();
    },
    recalculate: function(){
        var val = parseFloat(document.id('totale_a_pagare').get('text').trim());
        if(val){
            var t = 0;
            var irs = $$('.importo-rata');
            var num = irs.length;
            irs.each(function(e, i){
                if(i == num - 1){
                    
                }else{
                    var ev = parseFloat(e.get('value'));
                    if(ev)
                        t += ev;
                }
            });
            irs[irs.length - 1].set('value', val - t);
        }
    }
});
