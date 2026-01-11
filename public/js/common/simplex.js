var Simplex = new Class({
    Implements: [Options, Events],
    options: {
       /*,
        onClick: $empty,
        */
    },
    grids: [],
    initialize: function(form, options){
        this.setOptions(options);

        this.controllerName = $('controllerName').get('value');
        this.actionName = $('actionName').get('value');
        this.taskName = ($('taskName')) ? $('taskName').get('value') : false;

        this.params = null;

        this.initChangeInternal();

        this.initSideBar();
        this.initTabViews();

        this.initGridViews();

        this.initMenus();

        this.initNotifications();

        this.initAlerts();

        this.initDateFields();

        this.initSearchs();
        
        this.initChat();
    /*
        this.checkForMenus();

        this.checkDatesElement();

        this.buildSearchValidator();

        this.initAutocompleters();
        */
    },
    initSearchs: function(){
        $$('.search-form').each(function(f){
            var t = f.get('rel');
            var g = null;
            this.grids.each(function(gg){
                if(gg.getId() == t){
                    g = gg;
                }
            });
            if(g){
                this.formRequest = new Form.Request(f, g.container.getParent(),{
                    onSuccess: g.refresh.bind(g),
                    resetForm: false
                });
            }/*else{
                this.formRequest = new Form.Request(f, f.getParent('div').getNext(),{
                    onSuccess: function(tree, elems, html, js){
                        var obj = JSON.decode(html, true);
                        if(obj){
                            if(!!(obj.deletedependencyerrors || obj.deletedependencyerrors === 0) && obj.deletedependencyerrors){
                                window.location.href = baseUrl + '/error/delete/mem/ses';
                            }
                            html = obj.list;
                        }        
                    },
                    resetForm: false
                });
            }*/
        }, this);
    },
    initChat: function(){
        this.chat = new By0Chat();
    },
    initChangeInternal: function(){
        document.id('_cinternal').addEvent('change', function(ev){
            //document.id('container').spin();
            new Request.JSON({
                url: baseUrl + '/login/cinternal/format/json/id_internal/' + ev.target.get('value'),
                onSuccess: function(obj){
                    if(obj.result){
                        location.href = baseUrl;
                    }else{
                        //document.id('container').unspin();
                        alert('impossibile effettuare l\'operazione');
                    }
                }
            }).send();
        });
    },
    initDateFields: function(){
        new By0DatePicker($$('input.date-fields'), {
            pickerClass: 'datepicker_vista'
        });
        new By0DatePicker($$('input.validate-date:not([class*="no-startup"])'), {
            pickerClass: 'datepicker_vista'
        });
        new By0DatePicker($$('input.validate-datetime'), {
            pickerClass: 'datepicker_vista',
            timePicker: true
        });
    },
    initNotifications: function(){
        this.notifications = new Purr({
            'mode': 'bottom',
            'position': 'right'
        });
    },
    notify: function(text, options){
    	this.notifications.alert(text, options ? options : {});
    },
    notifyWithStatus: function(text, status){
        this.notify(text, {
            'className': status
        });
    },
    initAlerts: function(){
    	this.alerts = new TinyAlert({position:'br'});
    },
    alert: function(caption, text){
    	//this.alerts.show(caption, text);
    },
    initMenus: function(){
    	this.menu = new DMenu({className: 'action-menu'});
        
        var menus = $$('.contextual-menu');
        
        menus.each(function(menu){
            var mm = menu.getElements('ul');
            var dims = [];
            mm.each(function(m){
                dims.push(m.getCoordinates(menu));
                m.set('styles', {display: 'none'});
            });
            //mm.set('styles', {display: 'none'});
            var mc = menu.getCoordinates();
            mm.each(function(m, i){
                var li = m.getParent('li');
                var lidim = li.getPosition(menu);
                //var cc = m.measure(function(){return m.getCoordinates()});
                var cc = dims[i];
                m.set('styles', {display: 'block'});
                
                m.slide('hide');
                var opts = {
                    position: 'bottomleft',
                    edge: 'topleft'
                };
                if(lidim.x  + cc.width > mc.width){
                    if(cc.width < mc.width){
                        opts = {
                            position: 'bottomright',
                            edge: 'topright'
                        };
                    }
                }
                
                var d = m.getParent('div');                
                d.set('styles', {width: cc.width});
                d.position({
                    relativeTo: li,
                    position: opts.position,
                    edge: opts.edge
                });

                li.addEvents({
                    'mouseenter': function(evt){
                        m.slide('in');
                    },
                    'mouseleave': function(evt){
                        m.slide('out');
                    }
                });
            });
        });
    },
    initSideBar: function(){
        $$( '.headitem' ).each(function(item){
            if(item.getNext('ul')){
                var thisSlider = new Fx.Slide( item.getNext( 'ul' ), { duration: 500 } );
                thisSlider.hide();
                if(item.hasClass('active')){
                    thisSlider.toggle();
                }
                item.addEvent( 'click', function(e){
                    thisSlider.toggle();
                    e.stop();
                });
            }
            if(item.hasClass('active')){
                item.getParent('li').addClass('head_active');
            }
        });

        var self = this;
        $$('.toggle-sidebar').addEvent('click', this.toggleSidebar);
        var sidebarClosed = Cookie.read('by0sidebarclosed');
        if(sidebarClosed && sidebarClosed != 'false'){
            Cookie.write('by0sidebarclosed', false);
            $$('.toggle-sidebar').fireEvent('click');
        }
    },
    toggleSidebar: function(){
        $$('#main-container, #right-container, #content, #sidebar').toggleClass('sidebar-closed');
        var oldStatus = Cookie.read('by0sidebarclosed');
        if(!oldStatus || oldStatus == 'false') oldStatus = false;
        else oldStatus = true;
        Cookie.write('by0sidebarclosed', !oldStatus);
    },
    initTabViews: function(){

    },
    initGridViews: function(){

        var ts = $$('.listview');
        ts.each(function(t){
            this.grids.push( new GridView(t) );
        }, this);
    }
});