window.addEvent('domready', function(){
    /*var tabs, contents;
    tabs = $$('.submenu');
    contents = $$('.tab_contents');
    var tabView = new MooTabs(tabs, contents);
    */
    
    var myTabPane = new TabPane('tabs_main', {
        tabSelector: 'a.heading_tab',
        contentSelector: 'div.tab_content'
    });
});