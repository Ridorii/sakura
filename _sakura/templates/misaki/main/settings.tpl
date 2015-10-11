{% extends 'global/master.tpl' %}

{% block title %}{{ page.category }} / {{ page.mode }}{% endblock %}

{% block content %}
    <div class="content settings messages">
        <div class="content-right content-column">
            {% include 'elements/settingsNavigation.tpl' %}
        </div>
        <div class="content-left content-column">
            <h1 class="sectionHead">
                {{ page.category }} / {{ page.mode }}
            </h1>
            <div class="settings-explanation">
            {% for descline in page.description %}
                <div>{{ include(template_from_string(descline)) }}</div>
            {% endfor %}
            </div>
            {% include 'settings/' ~ current ~ '.tpl' %}
        </div>
        <div class="clear"></div>
    </div>
{% endblock %}
