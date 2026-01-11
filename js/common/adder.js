function adderAdd(){
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
}