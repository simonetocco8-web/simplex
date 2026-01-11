                // TODO - Add loading step
var By0Chat = new Class({

    extraData: [],
    Implements: [Options, Events],

    options: {
       /*,
        onUpdate: $empty,
        onMessagePush: $empty,
        onMessagePull: $empty,
        onActivate: $empty,
        onDeactivate: $empty
        */
    },

    initialize: function(options){
        this.me = document.id('xsrff').get('value');
        this.holders = {};
        this.data = {
            messages: [],
            lastTimestamp: 0
        };
        this.initSound();
        this.initUI();
        this.initEvents();
    },
    initEvents: function(){
        this.forcePlaySound = false;
        var o = this;
        window.addEvents({
            focus: function(){
                o.forcePlaySound = false;
                /*
                clearInterval(o.timeoutId);
                document.title = o.oldTitle;
                */
            },
            blur: function(){
                o.forcePlaySound = true;
            }
        });
    },
    initSound: function(){
        soundManager.url = baseUrl + '/media/soundmanager/';
        var o = this;
        this.soundLoaded = false;
        soundManager.onready(function(){
            o.sound = soundManager.createSound({
                id: 'aSound',
                url: baseUrl + '/media/notification.wav'
                // onload: myOnloadHandler,
                // other options here..
            });
            o.soundLoaded = true;
        });
        soundManager.ontimeout(function(){

        });
    },
    initUI: function(){
        this.createChatBox();
        /*
        this.chatPanel.position({
            relativeTo: this.chatHolder,
            position: 'bottomright',
            edge: 'topright'
        });
        */
        this.chatPanel.hide();
        this.chatOpen.addEvent('click', this.togglePanel.bind(this));
        this.chatClose.addEvent('click', this.deactivate.bind(this));
        var status = Cookie.read('by0chatstatus');
        if(!status || status == 1){
            this.activate();
        }else{

        }
    },
    createChatBox: function(){
        //this.chatGlobal = new Element('div', {'class': 'chat-global'}).adopt([
            this.chatHolder = new Element('div', {'id': 'chat-holder', 'class': 'chat-holder inactive'}).adopt([
                this.chatPanel = new Element('div', {'id': 'chat-panel', 'class': 'chat-panel'}).adopt([
                    new Element('div', {'class': 'chat-head'}).adopt([
                        new Element('span', {'html': 'chat '}),
                        this.chatClose = new Element('a', {'id': 'chat-btn2', 'href': '#', 'html': 'chiudi'})
                    ]),
                    this.chatUsers = new Element('ul', {'id': 'chat-users', 'class': 'chat-users'})
                ]),
                new Element('div', {styles: {'clear': 'both'}}),
                this.chatOpener = new Element('div', {'id': 'chat-opener', 'class': 'chat-opener'}).adopt(
                    new Element('h3').adopt(
                        this.chatOpen = new Element('a', {'id': 'chat-btn1', href: '#'}).adopt([
                            new Element('span', {'html': 'chat '}),
                            this.chatStatus = new Element('span', {'html': '(offline)', 'id': 'chat-on'})
                        ])
                    )
                )
            ]);
        //]);
        this.chatHolder.set('styles',{
            'position': 'fixed',
            'bottom': 0,
            'right': 0
        });
        document.id(document.body).adopt(this.chatHolder);
        this.holders[0] = this.chatHolder;
    },
    togglePanel: function(evt){
        if(evt) evt.preventDefault();
        if(this.chatHolder.hasClass('inactive')){
            this.activate();
            Cookie.write('by0chatstatus', '1');
        }
        this.chatPanel.toggle();
    },
    activate: function(){
        this.chatHolder.removeClass('inactive');
        this.initRequest();
        this.req.startPolling(this.pushData.bind(this));
    },
    pushData: function(){
        return this.data;
    },
    deactivate: function(evt){
        if(evt) evt.preventDefault();
        this.chatHolder.addClass('inactive');
        this.req.stopPolling();
        this.chatStatus.set('html', '(offline)');
        this.chatPanel.hide();
        Cookie.write('by0chatstatus', '0');
    },
    initRequest: function(){
        if(!this.req){
            this.req = new Request.JSON({
                url: baseUrl + '/chat/update/format/json',
                initialDelay: 0,
                delay: 3000,
                limit: 360000,
                onSuccess: this.handleResponse.bind(this),
                onRequest: this.resetData.bind(this)
            });
        }
    },
    resetData: function(evt){
        this.data.messages = [];
    },
    handleResponse: function(json){
        this.handleUsers(json.users);
        this.handleMessages(json.messages);
    },
    handleMessages: function(messages){
        this.canPlaySound = false;
        messages.each(function(msg){
            var uid = (msg.sender_id == this.me) ? msg.receiver_id : msg.sender_id;
            this.doChatBox(uid,
                    (msg.sender_id == this.me) ? msg.receiver_username : msg.sender_username,
                    (msg.sender_id == this.me) ? msg.receiver_nome : msg.sender_nome,
                    (msg.sender_id == this.me) ? msg.receiver_cognome : msg.sender_cognome);
            this.pushNewMessage(uid, msg.sender_username, msg.message, msg.ts, false);
            this.data.lastTimestamp = msg.ts;
        }, this);

        if(this.canPlaySound){
            this.playSound();
        }
    },
    playSound: function(){
        if(this.soundLoaded){
            this.sound.play();
        }
    },
    handleUsers: function(users){
        this.chatStatus.set('html', '('  + users.length + ')');
        this.chatUsers.empty();
        users.each(function(u){
            this.chatUsers.adopt(this.createUserElement(u));
        }, this);
    },
    createUserElement: function(user){
        var li = new Element('li', {'class': 'chat-user'}).adopt([
            new Element('a', {href:'#', 'html': user.username}),
            new Element('span', {'html': user.nome + ' ' + user.cognome})
        ]);
        li.addEvent('click', this.doChatBox.pass([user.logged_user_id, user.username, user.nome, user.cognome, true], this));
        return li;
    },
    doChatBox: function(user_id, username, nome, cognome, force_open){
    //doChatBox: function(user){
       // evt.preventDefault();
        var uid = user_id;
        if(this.holders[uid] == undefined){
            this.canPlaySound = true;
            this.holders[uid] = new Element('div', {'class': 'chat-holder', 'id': 'chat-user-' + uid}).adopt(
                new Element('div', {'class': 'chat-panel'}).adopt([
                    new Element('div', {'class': 'chat-story'}),
                    new Element('div', {'class': 'chat-input'}).adopt([
                        new Element('textarea', {'events': {
                            'keyup': this.taku.bind(this)
                        }})
                    ])
                ]),
                new Element('div', {'class': 'chat-opener'}).adopt(
                    new Element('div', {'class': 'chat-close'}).adopt(
                        new Element('a', {href: '#', html: 'X', events: {
                            'click': this.closeChat.pass([uid], this)
                        }})
                    ),
                    new Element('h3').adopt([
                        new Element('a', {'href': '#', 'html': username + ' ' + nome + ' ' + cognome, 'events':{
                            'click': this.rtogglePanel.pass([uid], this)
                        }})
                    ])
                )
            );

            document.body.adopt(this.holders[uid]);
            var nm = Hash.getKeys(this.holders).length - 1;
            this.holders[uid].set('styles', {
                'position': 'fixed',
                'bottom': 0,
                'right': nm * 200
            });
            if(!force_open){
                this.rtogglePanel(uid);
            }
            this.holders[uid].addClass('new-messages');
        }else{
            if(this.holders[uid].hasClass('inactive')){
                this.canPlaySound = true;
                //this.rtogglePanel(uid);
                //this.holders[uid].toggle();
                this.holders[uid].addClass('new-messages');
            }
        }
        if(this.forcePlaySound){
            this.canPlaySound = true;
/*
            this.oldTitle = document.title;
            console.log(document.title);
            console.log(window.document.title);
            var msg = this.oldTitle + " - nuovo messaggio!";
            this.timeoutId = setInterval(function() {
                document.title = document.title == msg ? this.oldTitle : msg;
            }, 1000);
*/
        }
    },
    closeChat: function(uid){
        this.holders[uid].destroy();
        delete this.holders[uid];
    },
    rtogglePanel: function(uid){
        this.holders[uid].getElement('.chat-panel').toggle();
        this.holders[uid].toggleClass('inactive');
        this.holders[uid].removeClass('new-messages');
    },
    taku: function(evt){
        if(evt.event.keyCode == 13){
            evt.preventDefault();
            this.data.messages.push({
                'message': evt.target.get('value'),
                'to': evt.target.getParent('.chat-holder').get('id').substring(10)
            });
            //this.req.sendNow(this.pushData.bind(this));
            evt.target.set('value', '');
            // INVIO IL MESSAGGIO
        }
    },
    pushNewMessage: function(sid, sname, message, ts){
        var cstory = this.holders[sid].getElement('.chat-story');
        var lastFromDiv = cstory.getElements('.chat-msg-from').getLast();
        if(!lastFromDiv || lastFromDiv.get('html') != sname)
            cstory.adopt(new Element('div', {'class': 'chat-msg-from', 'html': sname}));
        cstory.adopt(
            new Element('div', {'class': 'chat-msg'}).adopt(
                new Element('div', {'class': 'chat-msg-time', 'html': ts}),
                new Element('div', {'class': 'chat-msg-body', 'html': message})
            )
        );
    }
});
/*
<div class="chat-holder" id="">
    <div class="chat-story">
        <div class="chat-msg-from">
            me
        </div>
        <div class="chat-msg">
            <div class="chat-msg-time">
                14:39
            </div>
            <div class="chat-msg-body">
                messaggio
            </div>
        </div>
        <div class="chat-msg">
            <div class="chat-msg-time">
                14:39
            </div>
            <div class="chat-msg-body">
                messaggio secondo
            </div>
        </div>
        <div class="chat-msg-from">
            rco
        </div>
        <div class="chat-msg">
            <div class="chat-msg-time">
                14:39
            </div>
            <div class="chat-msg-body">
                come no sldasjd lkashd kljs a a a a a a a hdjkasdh laksdj hlasdjaks dhalkjsdhas kdjhl dash dajlk
            </div>
        </div>
        <div class="chat-msg">
            <div class="chat-msg-time">
                14:39
            </div>
            <div class="chat-msg-body">
                a dodop
            </div>
        </div>
    </div>
    <div class="chat-input"></div>
    <div class="chat-opener">
        <div class="chat-close"><a href="#">X</a></div>
        <h3>rco Mario Rossi</h3>
    </div>
</div>
*/

Request.implement({

    options: {
        initialDelay: 5000,
        delay: 5000,
        limit: 60000
    },

    startPolling: function(data){
        var fn = function(){
            if (typeOf(data) == 'function') rdata = data.apply(this, [])
                else rdata = data;
            if (!this.running) this.send({data: rdata});
        };
        this.lastDelay = this.options.initialDelay;
        this.timer = fn.delay(this.lastDelay, this);
        this.completeCheck = function(response){
            clearTimeout(this.timer);
            this.lastDelay = (response) ? this.options.delay : (this.lastDelay + this.options.delay).min(this.options.limit);
            this.timer = fn.delay(this.lastDelay, this);
        };
        return this.addEvent('complete', this.completeCheck);
    },

    stopPolling: function(){
        clearTimeout(this.timer);
        return this.removeEvent('complete', this.completeCheck);
    },

    sendNow: function(data){
        clearTimeout(this.timer);
        this.startPolling(data);
    }

});
