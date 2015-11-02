<div class="platform frontStats">
    <div title="We have {{ stats.userCount }} user{% if stats.userCount != 1 %}s{% endif %}">
        <div>
            <span class="fa fa-line-chart"></span>
            <span>{{ stats.userCount }}</span>
        </div>
    </div>
    <div title="Active in the last 5 minutes: {% for amount,onlineUser in stats.onlineUsers %}{{ onlineUser.username }}{% if amount != (stats.onlineUsers|length - 1) %}, {% endif %}{% endfor %}">
        <div>
            <span class="fa fa-users"></span>
            <span>{{ stats.onlineUsers|length }}</span>
        </div>
    </div>
    <div title="Our newest user is {{ stats.newestUser.data.username }}">
        <div>
            <span class="fa fa-user-plus"></span>
            <span><a href="/u/{{ stats.newestUser.data.user_id }}" style="color: {{ stats.newestUser.colour }}">{{ stats.newestUser.data.username }}</a></span>
        </div>
    </div>
    <div title="It has been {{ stats.lastRegDate }} since the last user registered">
        <div>
            <span class="fa fa-clock-o"></span>
            <span>{{ stats.lastRegDate }}</span>
        </div>
    </div>
    <div title="The forum has {{ stats.topicCount }} thread{% if stats.topicCount != 1 %}s{% endif %} consisting out of {{ stats.postCount }} post{% if stats.postCount != 1 %}s{% endif %}">
        <div>
            <span class="fa fa-list"></span>
            <span>{{ stats.topicCount }}<span style="font-size: .5em; line-height: 1em">/ {{ stats.postCount }}</span></span>
        </div>
    </div>
</div>
