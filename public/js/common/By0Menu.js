                
var By0Menu = new Class({
    
    Implements: [Options, Events],
    
    options: {
        fx: 'slide'
    },
    
    initialize: function(element, options){
        this.setOptions(options);
        this.menu = $(element);
        
        this.init();
    },
    
    init: function(){
        var mm = this.menu.getElements('ul');
        var dims = [];
        mm.each(function(m){
            dims.push(m.getCoordinates(this.menu));
            m.set('styles', {display: 'none'});
        });        
        
        var mc = this.menu.getCoordinates();
        mm.each(function(m, i){
            var li = m.getParent('li');
            var cc = dims[i];
            m.set('styles', {display: 'block'});
            m.slide('hide');
            var opts = {
                position: 'bottomleft',
                edge: 'topleft'
            };
            //console.log(cc);
            //console.log(mc);
            if(cc.right + cc.width > mc.width && cc.width < mc.width){
                opts = {
                    position: 'bottomright',
                    edge: 'topright'
                };
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
    }
});