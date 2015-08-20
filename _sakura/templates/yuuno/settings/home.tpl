<div style="margin: 5px;">
<h1 class="stylised">Common Tasks</h1>
<h2>Profile</h2>
<ul>
    <li><a href="/settings/avatar" class="default">Change Avatar</a></li>
    <li><a href="/settings/userpage" class="default">Change Userpage</a></li>
    <li><a href="/settings/signature" class="default">Change Signature</a></li>
    <li><a href="/settings/profile" class="default">Change Profile Details</a></li>
</ul>
<h2>Messaging</h2>
<ul>
    <li><a href="/messages/index" class="default">View Inbox</a></li>
    <li><a href="/messages/compose" class="default">Send PM</a></li>
</ul>
<h2>Account</h2>
<ul>
    <li><a href="/settings/sessions" class="default">Manage Active Sessions</a></li>
    <li><a href="/settings/password" class="default">Change Password</a></li>
</ul>
<br />
<h1 class="stylised">Personal Statistics</h1>
<ul>
    <li>You joined on <b>{{ user.data.regdate|date(sakura.dateFormat) }}</b>.</li>
    <li>You have made <b>{{ settings.forum_stats.posts }} forum post{% if settings.forum_stats.posts != 1 %}s{% endif %}</b> and started <b>{{ settings.forum_stats.topics }} forum thread{% if settings.forum_stats.topics != 1 %}s{% endif %}</b>.</li>
    <li>You have <b>x</b> warnings.</li>
    <li>You have <b>{{ settings.friends|length - (settings.friends.online ? 1 : 0) - (settings.friends.offline ? 1 : 0) }} friend{% if settings.friends|length - (settings.friends.online ? 1 : 0) - (settings.friends.offline ? 1 : 0) != 1 %}s{% endif %}</b>.</li>
</ul>
<br />
<h1 class="stylised"><a class="clean" href="/settings/friendlisting">Friends</a></h1>
<h2 style="color: #080;">Online</h2>
{% if settings.friends.online %}
    {% for key,friend in settings.friends.online %}
        <a href="/u/{{ friend.user.username }}" class="default" style="color: {% if friend.user.name_colour %}{{ friend.user.name_colour }}{% else %}{{ friend.rank.colour }}{% endif %}">{{ friend.user.username }}</a>{% if key + 1 != settings.friends.online|length %},{% endif %}
    {% endfor %}
{% else %}
    <h4>No friends are online.</h4>
{% endif %}
<h2 style="color: #800;">Offline</h2>
{% if settings.friends.offline %}
    {% for key,friend in settings.friends.offline %}
        <a href="/u/{{ friend.user.username }}" class="default" style="color: {% if friend.user.name_colour %}{{ friend.user.name_colour }}{% else %}{{ friend.rank.colour }}{% endif %}">{{ friend.user.username }}</a>{% if key + 1 != settings.friends.offline|length %},{% endif %}
    {% endfor %}
{% else %}
    <h4>No friends are offline.</h4>
{% endif %}
</div>
