var Paginate = (function(){
    var PaginateSlider = new Class({
        
        Extends: Slider,
        
        initialize: function(element, knob, options){
            this.parent(element, knob, options);
            this.drag.addEvents({
                beforeStart: (function(){
                    this.isDropping = true;
                }).bind(this),
                complete: (function(){
                    this.isDropping = false;
                }).bind(this)
            });
        },
        
        clickedElement: function(event){
            if(this.isDropping) return;
            
            this.parent(event);
        }
        
    });
    
    return function(){
        $$('div.pagination').each(function(el){
            if(el.retrieve('paginate')) return;
            
            el.store('paginate', true);
            
            var div = new Element('div', {
                'class' : 'paginate'
            });
            var link = new Element('a', {
                href : '#',
                'class' : 'link',
                styles: {
                    display: 'none'
                },
                html: 2
            });
            var s1 = new Element('span', {
                'class' : 'per',
                styles: {
                    display: 'none'
                },
                html: 5
            });
            var slider = new Element('div', {
                'class': 'slider'
            }).adopt(new Element('div', {
                'class' : 'knob',
                'title' : 'cambia pagina'
            }));
            var s2 = new Element('span', {
                styles: {float: 'left'},
                html: 'pagina <span class="current">2</span> di 11'
            });
            div.adopt(link, s1, slider, s2).inject(el, 'after');
            el.hide();
            var per = s1.get('text').toInt(),
            page = s2.getElement('span.current').get('text').toInt()-1;
            
            new PaginateSlider(slider, slider.getElement('div.knob'), {
                wheel: false,
                steps: link.get('text').toInt()-1,
                onChange: function(step){
                    s2.getElement('span.current').set('text', step+1);
                },
                onComplete: function(step){
                    if(step!=page) window.location.href = link.get('href').substitute({start: step*per});
                }
            }).set(page);
        });
    };
})();

window.addEvent('domready', Paginate);