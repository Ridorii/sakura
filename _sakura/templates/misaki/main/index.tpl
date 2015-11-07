{% extends 'global/master.tpl' %}

{% block content %}
    <div class="homepage">
        {% include 'elements/statsHeader.tpl' %}
        <div class="onlineUsers platform">
            {% if stats.onlineUsers %}
                {% for amount,onlineUser in stats.onlineUsers %}
                    <a href="{{ urls.format('USER_PROFILE', [onlineUser.id]) }}" style="background-color: {{ onlineUser.colour }};">{{ onlineUser.username }}</a>
                {% endfor %}
            {% else %}
                <div>There were no online users in the past 5 minutes.</div>
            {% endif %}
        </div>
        <div class="frontNews platform">
            {% for post in news.getPosts(0, newsCount) %}
                {% include 'elements/newsPost.tpl' %}
            {% endfor %}
        </div>
    </div>
{% endblock %}
