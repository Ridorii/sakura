{% extends 'global/master.tpl' %}

{% block content %}
    <div class="content homepage">
        <div class="content-right content-column">
            {% include 'elements/indexPanel.tpl' %}
        </div>
        <div class="content-left content-column">
            <div class="head">News</div>
            {% for post in news.posts|batch(newsCount)[0] %}
                {% include 'elements/newsPost.tpl' %}
            {% endfor %}
        </div>
        <div class="clear"></div>
    </div>
{% endblock %}

{% block js %}
    <script type="text/javascript" src="{{ sakura.resources }}/js/ybabstat.js"></script>
{% endblock %}
