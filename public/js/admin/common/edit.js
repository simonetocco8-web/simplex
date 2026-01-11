window.addEvent('domready', function(){
    
    var els = $$('.with-parent');
    els.each(function(self){
        var parent = document.id(self.get('self_parent_field'));
        var parent_table = self.get('parent_table');
        var self_table = self.get('self_table');
        var parent_field = self.get('parent_field');
        var self_field = self.get('self_field');
        var self_parent_field = self.get('self_parent_field');
        
        var dpreq;
        document.id(self_parent_field).addEvent('change', function(e){
            var s = e.target;
            var id = s.get('value');
            
            if(!dpreq){
                dpreq = new Request.HTML();         
            }
           
            dpreq.setOptions({
                url: baseUrl + '/common/dependentselect/format/html/',
                method: 'post',
                update: document.id('id_subservice')
           }).post({
               'parent_table' : self.get('parent_table'),
               'self_table' : self.get('self_table'),
               'parent_field' : self.get('parent_field'),
               'self_field' : self.get('self_field'),
               'self_label_field' : self.get('self_label_field'),
               'value' : id
           });
        });
    });
    
});