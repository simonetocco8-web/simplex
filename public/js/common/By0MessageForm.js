                // TODO - Add loading step
var By0MessageForm = new Class({
    
    Implements: [Options, Events],
    
    options: {
        fmClass: 'formMessage',
        fmSuccessClass: 'success',
        fmErrorClass: 'error',
        unknownErrorMsg: 'Errori nella richiesta',
        fx: true
       /*,
        onClick: $empty,
        onClose: $empty,
        onHide: $empty,
        onOpen: $empty,
        onShow: $empty
        */
    },
    
    initialize: function(form, options){
        this.setOptions(options);
        this.form = document.id(form);
        this.container = this.form.getParent();
        this.form.set('send', {
            onRequest: this._request.bind(this),
            onSuccess: this._success.bind(this),
            onFailure: this._failure.bind(this)
        });
    },
    _request: function(){
        this.container.slide('out');
        this.container.getElements('.' + this.options.fmClass).nix();
    },
    _success: function(json){
        var obj = JSON.decode(json);
        this.fireEvent('success', obj);
        className = obj.result ? this.options.fmSuccessClass : this.options.fmErrorClass;
        this._addMessage(className + ' ' + this.options.fmClass, obj.message);
        
        this.form.reset();
        if(obj.result){
            this.form.destroy();
        }
        this.container.slide('in');
    },
    _failure: function(){
        this._addMessage(this.options.fmErrorClass + ' ' + this.options.fmClass, this.options.unknownErrorMsg);
        this.container.slide('in');
    },
    _addMessage: function(cls, msg){
        this.container.adopt(
            new Element('div',{
                    'class': cls,
                    html: msg
                }
            )
        );
    }
});