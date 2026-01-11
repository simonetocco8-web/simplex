MooTools.lang.setLanguage('it-IT');
window.addEvent('domready', function(){
    var form = document.id('edit');
    
    new Form.Validator.Inline(form);
    form.addEvent('submit', function(evt){
        var form = evt.target;
    });
});