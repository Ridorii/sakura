{% block content %}
<div id="chat">
    <div id="chatAccessButtons"></div>
    <div id="chatOnlineUsers">
        <div class="chatOnlineListTitle">
            Online Users
        </div>
        <div id="chatUserList">
            <div>
                <div class="avatar" style="background-image: url('/a/1');"></div>
                <div class="options">
                    <div class="username" style="color: #2B3F84;">Hanyuu</div>
                    <div class="actions">Display actions</div>
                </div>
            </div>
        </div>
    </div>
</div>
{% endblock %}

{% block style %}
<link rel="stylesheet" type="text/css" href="{{ sakura.resources }}/css/chat.css" />
{% endblock %}

{% block js %}
<script type="text/javascript" charset="utf-8" src="{{ sakura.resources }}/js/chat.js"></script>
<script type="text/javascript">
window.addEventListener("load", function(){ Chat.connect('127.0.0.1'); });
</script>
{% endblock %}
