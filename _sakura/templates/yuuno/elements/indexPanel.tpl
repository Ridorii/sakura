{% if session.checkLogin %}
    <div class="head">Hi, {{ user.data.username }}!</div>
    <a href="/settings/appearance/avatar/"><img src="/a/{{ user.data.id }}" class="default-avatar-setting homepage-menu-avatar" /></a>
    <ul class="panelQuickLinks">
        <li><a href="/settings/friends/requests/" title="Pending friend requests"><span class="fa fa-user-plus"></span><span class="count">{{ page.friend_req|length }}</span></a></li>
        <li><a href="/messages" title="View private messages"><span class="fa fa-envelope"></span><span class="count">0</span></a></li>
    </ul>
    <div class="clear"></div>
{% else %}
    {% if sakura.lockAuth %}
        <div class="head">Whoops!</div>
        You caught the site at the wrong moment! Right now registration <i>and</i> logging in is disabled for unspecified reasons. Sorry for the inconvenience but please try again later!
        <div class="indexSidePanelLinks">
            <a class="fa fa-lock" href="#" title="Authentication is locked"></a>
        </div>
    {% else %}
        <div class="head">Welcome!</div>
        Welcome to Flashii! This is a site for a bunch of friends to hang out, nothing special. Anyone is pretty much welcome to register so why not have a go?
        <div class="indexSidePanelLinks">
            <a class="fa fa-magic" href="/register" title="Register" id="indexSidePanelRegister"></a>
            <a class="fa fa-sign-in" href="/login" title="Login" id="indexSidePanelLogin"></a>
        </div>
    {% endif %}
{% endif %}
<div class="head">Stats</div>
We have <b>{{ stats.userCount }} user{% if stats.userCount != 1 %}s{% endif %}</b>,
<b><a href="/u/{{ stats.newestUser.data.id }}" class="default">{{ stats.newestUser.data.username }}</a></b> is the newest user,
it has been <b>{{ stats.lastRegDate }}</b> since the last user registered and the forum has <b>{{ stats.topicCount }} thread{% if stats.topicCount != 1 %}s{% endif %}</b> and <b>{{ stats.postCount }} post{% if stats.postCount != 1 %}s{% endif %}</b>.
<div class="head">Online Users</div>
{% if stats.onlineUsers %}
    All active users in the past 5 minutes:<br />
    {% for amount,onlineUser in stats.onlineUsers %}
        <a href="/u/{{ onlineUser.id }}" style="font-weight: bold;" class="default">{{ onlineUser.username }}</a>{% if amount != (stats.onlineUsers|length - 1) %}, {% endif %}
    {% endfor %}
{% else %}
    There were no online users in the past 5 minutes.
{% endif %}
