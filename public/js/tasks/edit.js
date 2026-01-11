var getCompanyId = function(){
    return {id_company: document.id('id_company').get('value')};
}

var getContactId = function(){
    return {
        id_contact: document.id('id_subject').get('value'),
        id_company: document.id('id_company').get('value')
    };
}

var tbls_what = [null, null, null];
var enable_dep_what = function(el, index){
    if(index == 6) return;
    if(index == 5) index = 3;
    if(!tbls_what[index]){
        var url = baseUrl;
        switch(index){
            case 1:
                url += '/contacts/numbersfor/format/json';
                extraParams = getContactId;
                break;
            case 2:
                url += '/contacts/mailsfor/format/json';
                extraParams = getContactId;
                break;
            case 3:
            case 5:
                url += '/contacts/addressesfor/format/json';
                extraParams = getContactId;
                break;
        }
        tbls_what[index] = new TextboxList(el.getElement('input').get('id'), {
            unique: false,
            max: 1,
            plugins: {
                autocomplete: { 
                    minLength: 0,
                    onlyFromValues: false,
                    queryRemote: true,
                    method: 'by0',
                    remote: {
                        url: url,
                        extraParams: getContactId
                    }
                }
            }
        });
    }
}

window.addEvent('domready', function(){
    var comreq = new Request.JSON();

    var tbl_contact;
    var tbl_p_contact;

    var deps_what = $$('.dep_what');
    //var deps_what_show = document.id('.dep_what.show');
    what_changed(document.id('what'));

    if(document.id('id_company')){
        document.id('id_company').addEvent('change', function(e){
            /*
            var s = e.target;
            var id = s.get('value');

            csreq.setOptions({
                url: baseUrl + '/contacts/contactsbycompanyforselect/format/html/id/' + id,
                update: document.id('id_company_contact')
            }).send();
            */
        });
    }

    if(document.id('p_subject')){
        var tbl_p_contact = new TextboxList('p_subject', {
            unique: false,
            max: 1,
            plugins: {
                autocomplete: {
                    minLength: 0,
                    //onlyFromValues: true,
                    queryRemote: true,
                    method: 'by0',
                    remote: {
                        url: baseUrl + '/contacts/tbl/withid/1/format/json',
                        extraParams: getCompanyId
                    }
                }
            }
        });
    }

    var tbl_contact = new TextboxList('subject', {
        unique: false,
        max: 1,
        onBitBoxAdd: function(bit){
            if(bit.value[0]){
                document.id('id_subject').set('value', bit.value[0]);
            }
        },
        plugins: {
            autocomplete: {
                minLength: 0,
                //onlyFromValues: true,
                queryRemote: true,
                method: 'by0',
                remote: {
                    url: baseUrl + '/contacts/tbl/withid/1/format/json',
                    extraParams: getCompanyId
                }
            }
        }
    });

    document.id('what').addEvent('change', function(evt){
        what_changed(evt.target);
    });

    function what_changed(el){
        deps_what.hide();
        var v = el.get('value').toInt();
        if(v){
            $$('.dep_what_' + v).show();
            enable_dep_what($$('.dep_what_' + v)[0], v);
        }
        return;
        var states = [false, false, false];
        var v = el.get('value');
        if(v != ''){
            states = [false, false, false];
            states[v - 1] = true;
        }
        deps_what.each(function(ele, i){
            if(states[i]){
                ele.show();
                enable_dep_what(ele, i);
            }else{
                ele.hide();
            }
        });
    }
});
