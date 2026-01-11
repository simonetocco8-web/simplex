window.addEvent('domready', function(){
    var tables = $$('.borders');
    var req = new Request.HTML({
        onSuccess: function(html){
            // caricare l'elemento tabella dall'html
            powerTable(t, this);
        }
    });
    tables.each(function(t){
        powerTable(t, req);
    });
});

function powerTable(t, req){
    var ls = t.getElements('th a');
    ls.addEvent('click', function(evt){
        evt.preventDefault();
        var url = evt.target.get('href');
        if(!url.contains('/format/html'))
            url += '/format/html';
        req.send({url : url,update : t.getParent('div')});
    });
}