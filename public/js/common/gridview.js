/**
 * Powerup a table
 */
var GridView = new Class({
    Implements: [Options, Events],
    options: {
        checkedTrClass : 'list_item_selected',
        checkedCheckboxClass: 'list_item_td_selected'
    },
    gridFirstLoaded: false,
    initialize: function(table, options){
        this.setOptions(options);
        this.init(table);
    }, 
    getId: function(){
        return this.table.get('id');
    },
    init: function(table){
        this.table = document.id(table);   
        this.id = this.table.get('id');
        this.container = this.table.getParent('.dagrid_container');
        this.request = new Request.HTML({
            useSpinner: true,
            method: 'get',
            update: this.container.getParent(),
            onSuccess: this.refresh.bind(this)
        });
        this.initAdvancedSearch();
        this.initListExport();
        this.ajaxForm();
        this.ajaxLinks();
    },
    getProps: function(element){
        var vals = element.get('class').split(' ').filter(function(cls){
            return cls.test(':');
        });
        if (!vals.length){
            return {};
        } else {
            props = {};
            vals.each(function(cls){
                var split = cls.split(':');
                if (split[1]){
                    try {
                        props[split[0]] = JSON.decode(split[1]);
                    } catch(e){}
                }
            });
            return props;
        }
    },
    initListExport: function(){
        var o = this;
        $$('.list-export').addEvent('click', function(e){
            e.preventDefault();
            document.id('_export').set('value', 1);
            o.form.submit();
            document.id('_export').set('value', 0);
        });
    },
    initAdvancedSearch: function(){
        var asdiv = document.id(this.id + '-search');
        if(asdiv){
            jQuery('.multiple-select').each(function(i,e){
                try{
                    jQuery(e).multiselect({
                        selectedList: 3,
                        checkAllText: 'tutte',
                        uncheckAllText: 'nessuna',
                        noneSelectedText: 'Scegli una o piu\' opzione',
                        selectedText: '# selezionati'
                    });
                }catch(er){
                    
                }
            });
            jQuery('.date-range').each(function(i,e){
                jQuery(e).daterangepicker({
                    dateFormat: 'dd/mm/yy',
                    rangeStartTitle: 'Data Da',
                    rangeEndTitle: 'Data a',
                    doneButtonText: 'fatto',
                    prevLinkText: 'prec',
                    nextLinkText: 'succ',
                    doneButtonText: 'fatto'
                });
            });
            jQuery('.numeric-range').each(function(i,e){
                var id = '#' + jQuery(e).attr('id') + '_slider_div';
                jQuery(id)[0].slide = null;
                var $el = jQuery(id);
                var min = $el.data('vmin');
                var max = $el.data('vmax');
                var values = [min, max];
                $el.slider({
                    range: true,
                    min: $el.data('min'),
                    max: $el.data('max'),
                    step: $el.data('step'),
                    values: values,
                    slide: function( event, ui ) {
                        jQuery(e).val( + ui.values[ 0 ] + "-" + ui.values[ 1 ] );
                    }
                });
            });
            this.container.getElements('.search-autocomplete').each(function(ac){
                var props = this.getProps(ac);
                new TextboxList(ac, {
                    unique: true,
                    max: (props.m ? 10 : 1),
                    plugins: {
                        autocomplete: {
                            minLength: 0,
                            queryRemote: true,
                            remote: {
                                url: baseUrl + props.u + '/format/json',
                                extraParams: props
                            }
                        }
                    }
                });
            }, this);
            var ashidden = asdiv.getElementById('ashidden');
            if(ashidden){
                if(ashidden.get('value') == 1){
                    asdiv.hide();
                }
                document.id('switch-companies-search').addEvent('click', function(e){
                    e.preventDefault();
                    asdiv.toggle();
                    ashidden.set('value', ((ashidden.get('value') == 1) ? 0 : 1));
                    //document.id('companies').getElement('tr#fast-search-tr').toggle();
                });
            }
        }
    },
    ajaxLinks: function(){
        this.container.getElements('a.sorter, a.paginate').each(function(el){
            el.addEvent('click', this._powerLink.bind(this));
        }, this);
    },
    _powerLink: function(evt){
        //evt.stop();
        var l = evt.target;
        var sfl = this.form.getElement('input[name=sfl]').get('value');
        var sdl = this.form.getElement('input[name=sdl]').get('value');
        var ed = this.parseLinksParams([
                sfl,
                sdl,
                'page'
            ], l.get('href'));
        if(ed[sfl]) document.id(sfl).set('value', ed[sfl]);
        if(ed[sdl]) document.id(sdl).set('value', ed[sdl]);
/*
        this.formRequest.setOptions({
            extraData: {
                'page': ed.page
            }
        }).send();
  */
         /*
        this.request.send({
            'url': l.get('href'), 
            data:{
                'format': 'html'
            }
        });*/
    },
    ajaxForm: function(){
        this.form = this.container.getElement('form');
        /*
        this.formRequest = new Form.Request(this.form, this.container.getParent(),{
            extraData: {
                'format': 'html'
            },
            onSuccess: this.refresh.bind(this)
        });
        */

        // initialize the application
        var o = this;
        //console.log('#' + this.container.get('id'));
        var app = Sammy(function() {
            // define a 'route'
            this.get(o.form.get('action'), function() {
                if(this.params.dalist){
                    o.gridFirstLoaded = true;
                    o.getRequest().send({ url: this.path });
                }else if(o.gridFirstLoaded){
                    location.href = o.form.get('action');
                }
            });
            // hack to reload page on global search
            this.get('search', function(){
                location.reload();
            });

        });

        // start the application
        app.run();






        // check for the select elements with onchange=submit();
        this.form.getElements('select.autosubmit').each(function(sel){
            sel.set('onchange', '');
            sel.addEvent('change', this._submitForm.bind(this));
        },this);
    },
    _submitForm: function(evt){
        jQuery(this.form).submit();
    },
    parseLinksParams: function(params, href){
        var ret = {};
        params.each(function(e){
            var x;
            var r = '\/' + e + '\/([a-z|A-Z|0-9|_|-]*)\/?';
            href.match(r);
            var v = RegExp.$1;
            ret[e] = v;
        });        
        return ret;
    },
    addParams: function(){
        var cid = this.containerDiv.get('id');
        var base = (cid) ? '#' + cid + ' ' : '';
        var url = '';
        $$(base + '.list_param').each(function(e){
            url += '/' + e.get('id') + '/' + e.get('value');
        });
        return url;
    },
    refresh: function(tree, elems, html, js){
        var obj = JSON.decode(html, true);
        if(obj){
            if(!!(obj.deletedependencyerrors || obj.deletedependencyerrors === 0) && obj.deletedependencyerrors){
                window.location.href = baseUrl + '/error/delete/mem/ses';
            }
            html = obj.list;
            application.resetIds(this.table.id);
        }        
        this.init(this.id);
        
        /*application.checkForSelectableTables();
        application.resetUISize(1000);
        application.pushUpdateEvents(this);
        $(this.containerDiv).unspin();*/
    },
    cercaCallback: function(html){
        this.updateTable(null, null, html, null);
    },
    getUrl: function(action, custom){
        return baseUrl + '/' + this.listController + '/' + action + custom + this.addParams();
    },
    cerca: function(){
        var o = this;
        $(this.containerDiv).spin();
        var url = this.getUrl(this.listAction, '/format/html/search/1');
        $('search_form').set('send', {url: url, method: 'post', onSuccess : o.cercaCallback.bind(o) }).send();
    },
    changePage : function(page_){
        $(this.containerDiv).spin({message: 'Caricamento pagina in corso'});
        var sortBy_ = $('sortBy').value;
        var sortDir_ = $('sortDir').value;
        var url = this.getUrl(this.listAction, '/format/html');
        this.request.send({ url: url, data: {page: page_, sortBy: sortBy_, sortVersus: sortDir_}});
    },
    changeOrder : function(field, versus){
        $(this.containerDiv).spin({message:'Ordinamento in corso'});
        var url = this.getUrl(this.listAction, '/format/html');
        this.request.send({ url: url, data: {sortBy: field, sortVersus: versus}});   
    },
    cestina : function(ids){
        if(this.ids.length > 0){                                                        
            $(this.containerDiv).spin();
            var url = this.getUrl(this.cestinaAction, '/format/json');
            this.request.send({ url : url, data: {toDelete: this.ids}});
        }else{
            StickyWin.alert('Attenzione', 'Nessun elemento selezionato!');
        }
    },
    ripristina : function(ids){
        if(this.ids.length > 0){
            $(this.containerDiv).spin();                                              
            var url = this.getUrl(this.ripristinaAction, '/format/json');
            this.request.send({ url : url, data: {ripristina: this.ids}});
        }else{
            StickyWin.alert('Attenzione', 'Nessun elemento selezionato!');
        }
    },
    elimina : function(ids){
        if(this.ids.length > 0){
            $(this.containerDiv).spin();
            var url = this.getUrl(this.eliminaAction, '/format/json');
            this.request.send({ url : url, data: {elimina: this.ids}});
        }else{
            StickyWin.alert('Attenzione', 'Nessun elemento selezionato!');
        }
    },
    editInline: function(data){
        if(this.isEditingInline){
            alert('Annullare o Salvare prima l\'operazione in corso!');
            return;
        }
        var pfix = $(this.table.get('id') + '_tr_prefix') ? 
            $(this.table.get('id') + '_tr_prefix').get('value')
            : 'tr';
        this.isEditingInline = true;
        var id = data.id;
        var r = this.getEditRequest();
        if(id){
            $(pfix + '_' + id).spin();
            r.rowid = pfix + '_' + id;
            r.ownerlist = this;
            r.post({id :id});
        }else{
            r.table = this.table;
            r.post({id: 'new'});
            r.ownerlist = this;
        }
    },
    cancelEditInline: function(data){
        var id = data.id;
        if(id == 'id'){
            this.table.getElement('tbody').getLast('tr').destroy();
        }else{
            var r = this.getEditRequest();
            var pfix = $(this.table.get('id') + '_tr_prefix') ? 
                $(this.table.get('id') + '_tr_prefix').get('value')
                : 'tr';
            r.replaced[pfix + '_' + id].replaces($(pfix + '_' + id));
            r.replaced.erase(pfix + '_' + id);
        }
        this.isEditingInline = false;
    },
    saveInline: function(data){
        var id = data.id;
        $(this.table.get('id') + '_eil_id').set('value', id);
        var o = this;
        var fid = 'eil_' + this.table.get('id');
        $(fid).set('send', {
            onSuccess: function(j){
                var r = o.getEditRequest();
                var pfix = $(o.table.get('id') + '_tr_prefix') ? 
                    $(o.table.get('id') + '_tr_prefix').get('value')
                    : 'tr';
                $(pfix + '_' + id).spin();
                r.rowid = pfix + '_' + id;
                if(id == 'id') r.nrowid = JSON.decode(j).result;
                r.post({id : ((id != 'id') ? id : JSON.decode(j).result), noEdit:1});
            }
        });
        $(fid).send();
    },
    deleteInline: function(data){
        this.ids = [data.id];
        this.elimina(this.ids);
    },
    afterCestino : function(html){
        
    },
    checkAllCallBack : function(evt){
        this.checkAll(evt.target.checked);
    },
    checkAll: function(checked){
        this.trs.each(function(tr){
            if(tr.container){this.check(tr, checked);}
        }, this);
    },
    clickEvt: function(evt){
        var tr = $(evt.target).getParent('tr');
        this.check(tr, !tr.input.checked);
    },
    check : function(tr, checked, force){
        // FORSE DEVO RIMETTERE QUESTO CONTROLLO
        if(tr.getElement('td').getElement('input').checked != checked || force)
        {
            tr.getElement('td').getElement('input').checked = checked;
            tr.toggleClass(this.options.checkedTrClass);
            tr.getElement('td').toggleClass(this.options.checkedCheckboxClass);
            if(checked) { 
                ++this.selectedCount;
            } else {
                iAll.checked = false;
                --this.selectedCount;
            }
            if(this.ids){
                var input_id = parseInt(tr.getElement('td').getElement('input').id.substring(3), 10);
                tr.getElement('td').getElement('input').checked = checked;
                if(checked){this.ids.include(input_id);}
                else{this.ids.erase(input_id);}
            }     
            /*
            var o = this;
            $$('.s_page').each(function(e){
                e.set('html', o.selectedCount);
            });
            $$('.s_totale').each(function(e){
                e.set('html', o.ids.length);
            });
            */
        }
    },
    getTable: function(){
        return this.table;
    },
    updateCheck: function(ids){
        this.ids.each(function(id){
            var realId = 'cb_' + id;
            if($(realId)){
                this.check($(realId).getParent('tr'), true);
            }
        }, this);
        $$('.s_totale').each(function(e){
            e.set('html', this.ids.length);
        }, this);
    },
    getRequest: function(){
        return this.request;
    },
    getEditRequest: function(){
        if(!this.editreq){
            var action = $(this.table.get('id') + '_eil_action') ? 
                $(this.table.get('id') + '_eil_action').get('value')
            : 'list';
            this.editreq = new Request.HTML({
                url: this.getUrl(action, '/edit/inline/format/html'),
                onSuccess: this.onEditData,
                onFailure: this.onEditFailure
            });
            this.editreq.replaced = $H({});
            this.editreq.owner = this;
        }
        return this.editreq;
    },
    onEditData: function(a, b, c){
        var tr = new Element('div', {html: '<table><tbody>' + c + '</tbody></table>'}).getElement('tr');
        if(this.rowid){
            var old = $(this.rowid);
            old.unspin();
            var pfix = $(this.owner.table.get('id') + '_tr_prefix') ? 
                    $(this.owner.table.get('id') + '_tr_prefix').get('value')
                    : 'tr';
            tr.set('id', (this.nrowid) ? pfix + '_' + this.nrowid : this.rowid);
            this.replaced[this.rowid] = old;
            tr.replaces(old);
        }else{
            var pfix = $(this.owner.table.get('id') + '_tr_prefix') ? 
                    $(this.owner.table.get('id') + '_tr_prefix').get('value')
                    : 'tr';
            tr.set('id', pfix + '_id');
            this.table.getElement('tbody').adopt(tr);
        }
        this.owner.isEditingInline = false;
        this.rowid = null;
        this.nrowid = null;
        this.ownerlist.fireEvent('onEditInlineSuccess');
    },
    onEditFailure: function(a, b, c){
        var old = $(this.rowid);
        old.unspin();
        alert('problemi durante il caricamento dei dati');
        this.isEditingInline = false;
    },
    table: null,
    containerDiv: null,
    trs: null,
    inputs: null,
    iAll: null,
    selectedCount: 0,
    ids: null,
    listController: null,
    listAction: null,
    request: null,
    editreq: null,
    isEditingInline: false
}); 