{% extends 'global/master.tpl' %}

{% block title %}{{ page.category }} / {{ page.mode }}{% endblock %}

{% block content %}
    <div class="box">
        <h1 class="sectionHead">
            {{ page.category }} <div class="fa">&#xf105;</div> {{ page.mode }}
        </h1>
        <h3>
        {% for descline in page.description %}
            {{ include(template_from_string(descline)) }}
        {% endfor %}
        </h3>
    </div>
    {% include 'settings/' ~ current ~ '.tpl' %}
{% endblock %}
