window.addEvent('domready', function(){
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
    
    var reqh = new Request.HTML({useSpinner: true});
    
    $$('.edit-detail').addEvent('click', function(evt){
        evt.stop();
        reqh.setOptions({
            url: evt.target.get('href'),
            update: evt.target.getParent('div.single-container')
        }).send();
    });
    
    var reqj = new Request.JSON({useSpinner: true});
    
    $$('.delete-detail').addEvent('click', function(evt){
        evt.stop();
        var d = evt.target.getParent('div.single-container');
        //d.spin();
        reqj.setOptions({
            url: evt.target.get('href')
        }).send();
    });
});