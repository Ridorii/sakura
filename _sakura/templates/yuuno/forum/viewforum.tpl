{% extends 'global/master.tpl' %}

{% set title %}Forums / {{ forum.name }}{% endset %}

{% block title %}{{ title }}{% endblock %}

{% block content %}
    <div class="content homepage forum viewforum">
        <div class="content-column">
            {% include 'forum/forum.tpl' %}
        </div>
    </div>
{% endblock %}
