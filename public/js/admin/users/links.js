var Simplex = new Class({
    Extends: Simplex,
    initialize: function(options){
        this.parent(options);
        this.initStuff();
    },
    initStuff: function(){
        
        this.itrs = $$('tr.check, tr.radio');
        
        this.itrs.each(function(tr){
            tr.set('morph', {duration: 200});
            
            var input = tr.retrieve('own:input', tr.getElement('input').store('parent', tr));
            
            if(input.get('type') == 'radio'){
                input.store('parent_check', tr.getParent('tr').getPrevious('tr'));
            }
            
            var td = tr.retrieve('own:td', tr.getElement('td.check'));
            
            td.getElement('input').setStyle('display', 'none');
            
            var div = tr.retrieve('own:div', new Element('div', {'class': 'input', 'morph': {duration: 140}}).inject(td));
            var o = this;
            tr.addEvent('click', function(event){
                o.itoggle(this);
            });
            
        }, this);

        
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
        if(input.get('type') == 'radio'){
            if (!force){
                //if (input.get('checked')) return;
            }
        }else{
            var tr_dep = input.get('value');
            var childs = document.id('of_' + tr_dep);
            if(childs) childs.dissolve();
        }
        
        input.set('checked', false);
        
     //   var deps = input.get('deps');
     //   if (deps){
     //       this.uncheckDepending(input.get('id'));
     //   }
        tr.retrieve('own:div').morph('.input');
        tr.retrieve('own:td').morph('.blurred');
        tr.morph('.blurred');
    },
    
    check: function(tr){
        var input = tr.retrieve('own:input').set('checked', true);
        if (input.get('type') == 'radio'){
            this.uncheckOthers(input);
        }else{
            this.uncheckRadios();
            var tr_dep = input.get('value');
            var childs = document.id('of_' + tr_dep);
            if(childs) childs.reveal();
        }
        
      //  var deps = input.get('deps');
      //  if (deps){
       //     this.checkDependants(deps.split(','));
       // }
        tr.retrieve('own:div').morph('.checked');
        tr.retrieve('own:td').morph('.focused');
        tr.morph('.focused');
    },
    uncheckOthers: function(input){
        var parent = input.retrieve('parent');
        var parent_int = input.retrieve('parent_check');
        this.itrs.each(function(tr){
            if(tr.retrieve('own:input').get('type') != 'radio'){
                if(tr != parent_int){
                    this.uncheck(tr);
                }
            }else{
                if(tr != parent){
                    this.uncheck(tr);
                }
            }
        }, this);
    },
    uncheckRadios: function(){
        this.itrs.each(function(tr){
            if(tr.retrieve('own:input').get('type') == 'radio'){
                this.uncheck(tr);
            }
        }, this);
    }
});