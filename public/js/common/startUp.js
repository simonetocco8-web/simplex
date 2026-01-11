/**
 * Lancio la funzione init quando il dom � pronto
 */
window.addEvent('domready', initApplication);

/* 
 * application wrapper 
 */
var application = null;

var $chk = function(obj){
    return !!(obj || obj === 0);
};

/**
 * inizializzo l'applicazione
 */
function initApplication(){
    Locale.use("it-IT");
    application = new Simplex();   
/*    
    roar = new Roar({
        position : 'uppperRight'
    });
*/
}


/**
 * Estendiamo l'oggetto Element con un metodo che, da un form, costruisce un
 * oggetto JSON con le coppie [nome: valore] del form stesso
 */
 
 /*
Element.implement({
    toJsonString:function(){
        var formElements = {};
        var stringArray = [];  //strings storage for all elements
 
        //getting all valid elements
        var formElementsCache = this.getElements('input, textarea, select').filter(function(el){
            var name = el.name;
            var value = el.get('value');
            return !(value === false || !name || el.disabled || (el.get('type') == 'checkbox' && !el.get('checked')));
        });
        
        //first define all elements
        formElementsCache.each(function(el,index){
            var name = el.name;
 
            stringArray[index] = '';
            //converting name to an array (tricky way)
            //and filtering out the last element if it needed, example "myintput[somename][]" will be "myintput[somename]"
            var subNames = name.replace(/]/g, '').split('[').filter(function(sub){ return (sub.length > 0); });
            var total = subNames.length;
            subNames.each(function(sub,i){
                stringArray[index] += "['" + sub + "']";
                //if it is  the las element, then define an array, otherwise object
                arr_obj = (i == (total-1)) ? "[]" : "{}";
                //finally define the element if it not already defined
                var defined = eval("$defined(formElements" + stringArray[index] + ")");
                if (!defined) eval("formElements" + stringArray[index] + " = " + arr_obj);
            });
        });
 
        //then assign values
        formElementsCache.each(function(el,index){
            var value = el.get('value');
            
            eval("formElements" + stringArray[index] + ".push(value)");
        });
 
        //some correction
        for (var element in formElements){
            if (formElements[element].length == 1)
                formElements[element] = formElements[element][0];
        }
 
        return JSON.encode(formElements);
    }
});
*/

/**
 * Operazioni comuni alle varie richieste
 */
 
/*
function _request(){
    $('footer_log').removeClass('footer_error');
    $('footer_log').addClass('footer_loading');
    $('footer_log').set('html', 'Caricamento dati');
}
function _success(){
    $('footer_log').removeClass('footer_loading');
    $('footer_log').set('html', 'Pronto');
}
function _failure(){
    $('footer_log').removeClass('footer_loading');
    $('footer_log').addClass('footer_error');
    $('footer_log').set('html', 'Errori nel caricamento dei dati');
}
Request.HTML = new Class({
    Extends: Request.HTML,
    initialize: function (options) {
        this.parent (options);
        this.addEvent('onRequest', _request);
        this.addEvent ('onSuccess', _success);
        this.addEvent ('onFailure', _failure);
    }
});
Request.JSON = new Class({
    Extends: Request.JSON,
    initialize: function (options) {
        this.parent (options);
        this.addEvent('onRequest', _request);
        this.addEvent ('onSuccess', _success);
        this.addEvent ('onFailure', _failure);
    }
});

*/