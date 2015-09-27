/*
 * On-site Sock Chat client
 */

var Chat = {

    server: null,
    chatContainer: null,
    accessButtons: null,
    onlineList: null,
    connected: false,

    connect: function(server, force) {
        // Set server
        this.server = server;

        // Set required variables
        this.chatContainer = document.getElementById('chat');
        this.accessButtons = document.getElementById('chatAccessButtons');
        this.onlineList = document.getElementById('chatOnlineUsers');

        // Check if we haven't already established a connection
        if(this.connected && !force) {
            this.accessButtons.innerHTML += '<a id="chatConnecting" class="fa fa-exclamation-triangle" title="Force a reconnect." href="javascript:void(0);" onclick="Chat.connect('+ this.server +', true);"></a>';
        }

        // Attempt to connect to the server
        this.accessButtons.innerHTML = '<a id="chatConnecting" title="Connecting to chat..."><div class="fa fa-spin fa-spinner" style="line-height: inherit;"></div></a>';

        // Grab connection indicator
        var connectionIndicator = document.getElementById('chatConnecting');

        setTimeout(function() {
            if(Chat.connected) {
                connectionIndicator.setAttribute('title', 'Connected!');
                connectionIndicator.children[0].className = 'fa fa-chain';
                var accessButtonsCont = '<a id="showOnlineUsers" class="fa fa-users" href="javascript:void(0);" onclick="Chat.toggleOnlineList();" title="Toggle online users list"></a><a id="openSiteChat" class="fa fa-comments-o" href="javascript:void(0);" title="View chat"></a>';
            } else {
                connectionIndicator.setAttribute('title', 'Failed to connect, try again later!');
                connectionIndicator.children[0].className = 'fa fa-chain-broken';
                var accessButtonsCont = '<a id="showChatTicker" class="fa fa-refresh" href="javascript:void(0);" onclick="Chat.connect('+ this.server +');"></a>';
            }
            setTimeout(function() {
                Chat.accessButtons.innerHTML = accessButtonsCont;
            }, 500);
        }, 500);
    },

    toggleOnlineList: function() {
        this.onlineList.className = this.onlineList.className != 'open' ? 'open' : '';
    },

    toggleTicker: function() {
        this.chatTicker.className = this.chatTicker.className != 'open' ? 'open' : '';
    }

};
