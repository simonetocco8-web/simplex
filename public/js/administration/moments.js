window.addEvent('domready', function(){
    document.id('makeinvoice').addEvent('click', function(evt){
        evt.preventDefault();
        var url = baseUrl + '/administration/draft';
        var cbs = $$('.cb:checked');
        if(cbs.length == 0){
            alert('Devi selezionare almeno un momento di fatturazione!');
            return;
        }
        cbs.each(function(cb){
            url += '/id/' + cb.get('id').substring(3);
        });
        location.href = url;
    });

    var code_order_selected = '';

    document.id('content').addEvent('change:relay(.cb)', function(e){
        var cb = e.target;
        var code_order = cb.getParent('td').getNext('td').get('text');
        if(cb.get('checked') && (code_order_selected == '' || code_order_selected == code_order)){
            code_order_selected = code_order;
        }
        checkRelatives();
    });

    function checkRelatives(){
        var none = true;
        allcbs = $$('.cb');
        allcbs.each(function(cb){
            var code_order = cb.getParent('td').getNext('td').get('text');
            cb.set('disabled', code_order_selected != code_order);
            if(none && cb.get('checked')){
                none = false;
            }
        });
        if(none){
            code_order_selected = '';
            allcbs.set('disabled', false);
        }
    }

    checkRelatives();
});

