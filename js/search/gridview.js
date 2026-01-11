/**
 * Created with JetBrains PhpStorm.
 * User: marcello
 * Date: 27/05/13
 * Time: 9.35
 * To change this template use File | Settings | File Templates.
 */

var GridView = new Class({
    Extends: GridView,
    firstLoad: true,
    init: function(table){
        this.parent(table);
        if(this.firstLoad){
            this.loadGrid();
        }
    },
    ajaxForm: function(){
        this.form = this.container.getElement('form');
        // initialize the application
        var o = this;

        // check for the select elements with onchange=submit();
        this.form.getElements('select.autosubmit').each(function(sel){
            sel.set('onchange', '');
            sel.addEvent('change', this._submitForm.bind(this));
        },this);
    },
    loadGrid: function(){
        var url = this.form.get('action') + location.search;
        if(this.firstLoad){
            url += '&format=html';
            this.firstLoad = false;
        }
        this.getRequest().send({ url: url });
    },
    _powerLink: function(evt){
        evt.preventDefault();
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

        this.request.send({
            'url': l.get('href'),
            data:{
            //    'format': 'html'
            }
        });
    },
    _submitForm: function(evt){
        /*
        this.request.send({
            'url': l.get('href'),
            data:{
                'perpage': evt.target.get('value')
            }
        });
        //jQuery(this.form).submit();
        */
    },
});