var def_form_validator;
window.addEvent('domready', function(){
    
    var form = document.id('edit');

    def_form_validator = new Form.Validator.Inline(form,{
        onFormValidate: function(pass, form, evt){
            if(pass){
                var suggested = $$('.suggested');
                var msg = '';
                suggested.each(function(el){
                    if(suggested.get('value') == ''){
                        msg += "\n" + suggested.get('data-suggested-label')
                    }
                });
                if(msg != ''){
                    if(!confirm('Simpl.ex ti consiglia di compilare i seguenti campi:' + msg + "\n\nVuoi continuare con il salvataggio?")){
                        evt.preventDefault();
                    }
                }
            }
        }
    });
    form.addEvent('submit', function(evt){
        var form = evt.target;
    });
    
    $$('.more-button').addEvent('click', function(evt){
        evt.stop();
        var bt = evt.target;
        var toAdd = bt.getParent('div').getElement('.to-be-added').clone();
        toAdd.getElements('input').set('value', null);
        toAdd.getElements('select').set('value', 0);
        var wsfx;
        toAdd.set('reveal', {
            onShow: function(e){
                wsfx = new Fx.Scroll(window,{onComplete: function(el){toAdd.highlight();}}).toElement(e);
                if(typeof onMoreAdded == 'function'){
                    onMoreAdded(toAdd);
                }
            }
        });
        toAdd.setStyle('display', 'none').addClass('added').inject(bt, 'before').reveal();
        
        toAdd.getElement('.remove-more').addEvent('click', function(evt){
            evt.stop();
            var bt = evt.target;
            var toAdd = bt.getParent('div.added').nix(true);
        });
    });
    
    $$('.remove-more').addEvent('click', function(evt){
        evt.stop();
        var bt = evt.target;
        var toAdd = bt.getParent('div.added').nix(true);
    });
});