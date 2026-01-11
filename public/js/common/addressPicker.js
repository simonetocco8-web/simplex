/**
 * Created by IntelliJ IDEA.
 * User: marcello
 * Date: 10-ott-2010
 * Time: 23.36.33
 * To change this template use File | Settings | File Templates.
 */

var AddressPicker = new Class({

    Implements: [Options, Events],

    options: {
        prefix: ''
       /*,
        onClick: $empty,
        onClose: $empty,
        onHide: $empty,
        onOpen: $empty,
        onShow: $empty
        */
    },

    initialize: function(container, options){
        this.setOptions(options);
        this.container = document.id(container);
        this.helper = this.container.getElement('.helper');
        this.hlpcnt = this.helper.getParent('.helper-cnt').hide();
        this.cap = this.container.getElement('.cap');
        this.localita = this.container.getElement('.localita');
        this.provincia = this.container.getElement('.provincia');

        this.help_link = this.container.getElement('.help-link');
        this.help_link.addEvent('click', this.showHelp.bind(this));

        var options = {
            unique: true,
            max: 1,
            plugins: {
                autocomplete: {
                    minLength: 1,
                    onlyFromValues: true,
                    queryRemote: true,
                    method: 'nofilter'
                }
            }
        }; 
        
        this.tbl = new TextboxList(this.helper, Object.merge(options, {plugins: {autocomplete:{remote: {
            url: baseUrl + '/common/address/format/json'
        }}},onBitBoxAdd: this.bitAdded.bind(this)
        }));
    },
    showHelp: function(ev){
        this.hlpcnt.show();
        this.help_link.hide();
    },
    hideHelp: function(){
        this.hlpcnt.hide();
        this.help_link.show();
    },
    bitAdded: function(bit){
        var values = bit.getValue()[2];
        bit.remove();
        this.hideHelp();
        this.cap.set('value', values.cap);
        this.localita.set('value', values.localita);
        this.provincia.set('value', values.provincia);
    }
});