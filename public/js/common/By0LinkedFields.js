                // TODO - Incompleto
var By0LinkedFields = new Class({
    
    Implements: [Options, Events],
    
    options: {
        event: 'change',
        callback: function(){},
        delay: 0
    },
    
    initialize: function(fields, options){
        this.setOptions(options);
        this.fields = fields;
        var o = this;
        this.fields.each(function(el){
            el.addEvent(o.options.event, function(evt){
                o.timeoutId = o.options.callback.delay(o.options.delay, evt, [el]);
            });
        });
    }
});