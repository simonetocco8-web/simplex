var Simplex = new Class({
    Extends: Simplex,
    initialize: function(options){
        this.parent(options);
        this.initStuff();
    },
    initStuff: function(){
        
        this.itrs = $$('tr.check');
        
        this.itrs.each(function(tr){
            tr.set('morph', {duration: 200});
            
            var input = tr.retrieve('own:input', tr.getElement('input').store('parent', tr));
            
            this.parseRelatives(input);
            
            if(input.get('type') == 'radio'){
                input.store('parent_check', tr.getParent('tr').getPrevious('tr'));
            }
            
            var td = tr.retrieve('own:td', tr.getElement('td.check'));
            
            td.getElement('input').setStyle('display', 'none');
            td.set('morph', {duration: 200});
            
            var div = tr.retrieve('own:div', new Element('div', {'class': 'input', 'morph': {duration: 140}}).inject(td));
            var o = this;
            tr.addEvent('click', function(event){
                o.itoggle(this);
            });
            
        }, this);
        var o = this;
        $$('tr.check_all').each(function(tr){
            tr.addEvent('click', function(ev){
                tr.getAllNext('tr').each(function(itr){
                    o.check(itr);
                });
            });
        });
        
        //$$('.is_internal').addEvent('click', this.internalClick.bind(this));
        //$$('.is_office').addEvent('click', this.officeClick.bind(this));
    },
    itoggle: function(tr){
        if (tr.retrieve('own:input').get('checked')){
            this.uncheck(tr);
        } else {
            this.check(tr);
        }
    },
    
    uncheck: function(tr, force){
        var input = tr.retrieve('own:input');
        
        input.set('checked', false);
        
        this.uncheckChilds(input);
        
        tr.retrieve('own:div').morph('.input');
        tr.retrieve('own:td').morph('.blurred');
        tr.morph('.blurred');
    },
    
    check: function(tr){
        var input = tr.retrieve('own:input').set('checked', true);
    
        this.checkParents(input);
       
        tr.retrieve('own:div').morph('.checked');
        tr.retrieve('own:td').morph('.focused');
        tr.morph('.focused');
    },
    checkParents: function(input){
        input.retrieve('parents').each(function(tr){
            this.check(tr);
        }, this);
    },
    uncheckChilds: function(input){
        input.retrieve('childs').each(function(tr){
            this.uncheck(tr);
        }, this);
    },
    parseRelatives: function(input){
        this.parseDeps(input, 'childs');
        this.parseDeps(input, 'parents');
    },
    parseDeps: function(input, what){
        input.store(what, Function.attempt(function(){
            var a = input.get(what).split(',');
            return a.map(function(item, index){
                var t = input.getParent('table');
                var tid = t.get('id');
                return t.getElement('input[name='+tid+'__'+item+']').getParent('tr');
            });
        },function(){
            return [];
        }));
    }
});