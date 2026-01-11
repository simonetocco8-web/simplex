                // TODO - Incompleto
var By0SearchInput = new Class({
    
    Implements: [Options, Events],
    
    options: {
		searchImg: true,
		searchImgOptions:{
			src: 'search.png',
			title: 'cerca',
			position: 'upperRight',
			edge: 'upperLeft',
			offset: {
				x: 2,
				y: 2
			}
		},
		resContOpts: {
			cls: 'rescont' 
		}
    },
    
    initialize: function(input, options){
        this.setOptions(options);
        this.input = document.id(input);
        
        this.init();
    },
    
    init: function(){
    	if(this.options.searchImg){
    		this.searchImg = new Element('img', {
        		'src': this.options.searchImgOptions.src,
        		'title': this.options.searchImgOptions.title,
        		'styles': {
        			cursor: 'pointer'
        		}
        	}).addEvent('click', this.search.bind(this)).inject(document.body).position({
        		relativeTo: this.input, 
        		position: this.options.searchImgOptions.position, 
        		edge: this.options.searchImgOptions.edge, 
        		offset: this.options.searchImgOptions.offset
        	});
    	}
    	
		this.resultContainer = new Element('div', {
			'class': this.options.resContOpts.cls,
			'styles': {
				'width': this.input.getDimensions().width - 10
			}
		}).inject(new Element('div').inject(this.input.getParent('div')).position({
			relativeTo: this.input,
    		position: 'bottomLeft',
    		edge: 'topLeft'
		})).slide('hide');
		
    },
    search: function(e){
    	this.resultContainer.toggleClass('gridLoading').set('text', 'ricerca in corso').slide('toggle');
    }
});