{% extends 'global/master.tpl' %}

{% block content %}
    <div class="content homepage forum">
        <div class="content-right content-column">
            {% include 'elements/indexPanel.tpl' %}
        </div>
        <div class="content-left content-column">
            {% include 'forum/forum.tpl' %}
        </div>
        <div class="clear"></div>
    </div>
    <script type="text/javascript" src="{{ sakura.resources }}/js/ybabstat.js"></script>
{% endblock %}
