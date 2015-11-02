{% extends 'global/master.tpl' %}

{% block content %}
    <div class="homepage">
        {% include 'elements/statsHeader.tpl' %}
        <div class="frontNews platform">
            {% for post in news.getPosts(0, newsCount) %}
                {% include 'elements/newsPost.tpl' %}
            {% endfor %}
        </div>
        <div class="clear"></div>
    </div>
{% endblock %}
