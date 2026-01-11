var Simplex = new Class({
    Extends: Simplex,
    cont: null,
    agendaReq: null,
    initialize: function(options){
        this.parent(options);
        this.initAgenda();
    },
    initAgenda: function(){
        this.cont = document.id('agenda-cont');
        this.cont.addEvent('click:relay(a.week-link)', this.changeWeek.bind(this))
    },
    changeWeek: function(evt){
        evt.preventDefault();
        this.agendaReq = new Request.HTML({
            url: evt.target.get('href') + '/format/html',
            update: this.cont,
            useSpinner: true,
            spinnerOptions: {
                message: 'caricamento...'
            }
        });
        this.agendaReq.send();
    }
});