var Simplex = new Class({
    Extends: Simplex,
    initialize: function(options){
        this.parent(options);
        this.initStuff();
    },
    initStuff: function(){
        this.initPagination();
        this.initPaginationOptions();
    },
    initSearchs: function(){
        $$('.search-form').each(function(f){
            var t = f.get('rel');
            this.formRequest = new Form.Request(f, f.getParent('div').getNext(),{
                onSuccess: this.initStuff.bind(this),
                resetForm: false
            });
        }, this);
    },
    initPagination: function(){
        $$('a.paginate').addEvent('click', this.paginate.bind(this));
    },
    initPaginationOptions: function(){
        $$('select.perpage').addEvent('change', this.paginateOptions.bind(this));
    },
    paginate: function(ev){
        ev.preventDefault();
        this.formRequest.setOptions({
            extraData: {
                'page': ev.target.get('rel')
            }
        }).send();
    },
    paginateOptions: function(ev){
        this.formRequest.setOptions({
            extraData: {
                'perpage': ev.target.get('value')
            }
        }).send();
    }
});