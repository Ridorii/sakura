{% extends 'global/master.tpl' %}

{% block title %}Forum Listing{% endblock %}

{% block content %}
    <div class="homepage forums">
        {% include 'elements/statsHeader.tpl' %}
        {% include 'forum/forum.tpl' %}
    </div>
{% endblock %}
