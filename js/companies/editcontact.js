/**
 * Created by IntelliJ IDEA.
 * User: marcello
 * Date: 11-ott-2010
 * Time: 21.22.16
 * To change this template use File | Settings | File Templates.
 */

var onMoreAdded = function(el){
    if(el.hasClass('address')){
        el.getElement('.textboxlist').destroy();
        new AddressPicker(el, {prefix: 'contacts_addresses_'});
    }
};

var Simplex = new Class({
    Extends: Simplex,
    initialize: function(options){
        this.parent(options);
        this.initAddress($$('.address')[0]);
    },
    initAddress: function(element){
        new AddressPicker(element, {prefix: 'contacts_addresses_'});
    }
});