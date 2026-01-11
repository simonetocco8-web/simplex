/**
 * Created by JetBrains PhpStorm.
 * User: Marcello
 * Date: 12/11/11
 * Time: 18.26
 * To change this template use File | Settings | File Templates.
 */

var $cal;
window.addEvent('domready', function(){
    var myTabPane = new TabPane('tabs_main', {
        tabSelector: 'a.heading_tab',
        contentSelector: 'div.tab_content',
        onChange: function(index){
  //          if(index == 1) $cal.fullCalendar('render');
        }
    });

    if(document.id('messagemenu')){
        var myTabPane = new TabPane('messagecontainer', {
            tabSelector: 'a.messagemenu-tab',
            contentSelector: 'div.messagecontent',
            onChange: function(index){
      //          if(index == 1) $cal.fullCalendar('render');
            }
        });

        $$('.message-delete-a').addEvent('click', function(ev){
            var url = ev.target.get('href') + '/format/json';
            var delmsgreq = new Request.JSON({
                url: url,
                onSuccess: function(resp){
                    if(resp.result){
                        ev.target.getParent('.message-detail').slide('out');
                        (function(){ev.target.getParent('.message-detail').getParent('div').destroy()}).delay(510);
                    }else{
                        alert(resp.message);
                    }

                },
                onFailure: function(){
                    alert('problemi con la richiesta');
                }
            }).send();
            ev.preventDefault();
        });
    }

    var recapContainer = document.id('recap-container');
    var recapRequest = new Request.HTML({
        useSpinner: true,
        method: 'get',
        update: recapContainer
    });

    recapRequest.send({ url: '/dashboard/recap/format/html'});

    recapContainer.addEvent('click:relay(a.recap-link)', function(event, target){
        event.preventDefault();
        recapRequest.send({
            url: target.get('href')
        });
    });

});

var formData = {
    id_who: 'own',
    done: '0'
};
jQuery(document).ready(function() {

    $cal = jQuery('#agenda');
    $cal.fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            //right: 'month,agendaWeek,agendaDay'
            right: ''
        },
        viewDisplay: function(view) {
            if(view.name == 'agendaDay')
                view.setHeight(8999);
        },
        defaultView: 'agendaDay',
        timeFormat: {
            agenda: 'H:mm{ - H:mm}', // 5:00 - 6:30
            '': 'H(:mm)'
        },
        axisFormat: 'H:mm',
        //   theme: true,
        editable: true,
        firstDay: 1,
        buttonText:{
            today: 'oggi',
            month: 'mese',
            week: 'settimana',
            day: 'giorno'
        },
        monthNames: ['Gennaio', 'Febraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio',
            'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'],
        monthNamesShort: ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug',
            'Ago', 'Set', 'Ott', 'Nov', 'Dic'],
        events:{
            url: $cal.attr('data-events-url'),
            data: formData
        },
        eventBackgroundColor: '#f2f2f2',
        eventTextColor: '#444444',
        eventRender: function(event, element) {
            element.tipsy({
                gravity: 's',
                fallback: event.description
            });
        },
        allDayText: 'tutto il giorno',
        dayNames: ['Domenica', 'Lunedì', 'Martedì', 'Mercoledì',
            'Giovedì', 'Venerdì', 'Sabato'],
        dayNamesShort: ['Dom', 'Lun', 'Mar', 'Mer',
            'Gio', 'Ven', 'Sab'],
        eventDrop: function(event, delta) {
            alert(event.title + ' was moved ' + delta + ' days\n' +
                '(should probably update your database)');
        },
        loading: function(bool) {
            // if (bool) $('#loading').show();
            // else $('#loading').hide();
        }
    });

    // sidebar
    var sticky = jQuery('.sticky');
    var win = jQuery(window);
    var stickyTop = sticky.offset().top; // returns number
    sticky.css({ position: 'fixed'});

    var stickyCheck = false;
    jQuery(window).scroll(function(){ // scroll event
        var nano = jQuery('.nano');
        var windowTop = win.scrollTop(); // returns number

        if(stickyCheck){
            nano.height(win.height() - nano.offset().top + windowTop);
            nano.nanoScroller();
            stickyCheck = false;
        }

        if (stickyTop < windowTop) {
            sticky.css({ position: 'fixed', top: 0 });
            stickyCheck = true;
        }
        else {
            //sticky.css('position','static');
            sticky.css('top', stickyTop - windowTop);

            nano.height(win.height() - nano.offset().top + windowTop);
            nano.nanoScroller();
        }
    });

    jQuery('.nano').height(jQuery(window).height() - jQuery('.nano').offset().top);
    jQuery(".nano").nanoScroller();
    jQuery(".nano").bind("scrollend", function(e){
        var el = jQuery(e.currentTarget);
        var href = el.find('.message-more-link').attr('href');

        /*
        jQuery.ajax({
            url: href,
            success: function(html) {
                el.find('.content').html(el.html() + html);
            }
        });
        */
    });

    var mms = jQuery('.message-list');
    jQuery.each(mms, function(i, el){
        jQuery(el).delegate('a.message-more-link', 'click', function(ev){
            ev.preventDefault();
            var a = jQuery(this);
            jQuery.ajax({
                url: a.attr('href'),
                success: function(html){
                    var cnt = jQuery(el).find('.content');
                    cnt.html(cnt.html() + html); // No need to attach more click events.
                    jQuery(".nano").nanoScroller(); // TODO: ugly
                }
            });
            a.parent().remove();
        });
    });
});