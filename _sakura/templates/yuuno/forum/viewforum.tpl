{% extends 'global/master.tpl' %}

{% block title %}Forums / {{ board.forums[0].forum.forum_name }}{% endblock %}

{% block content %}
    <div class="content homepage forum viewforum">
        <div class="content-column">
            {% include 'forum/forum.tpl' %}
        </div>
    </div>
{% endblock %}
