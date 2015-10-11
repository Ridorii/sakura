{% extends 'global/master.tpl' %}

{% block title %}Information{% endblock %}

{% block content %}
    <h1 class="sectionHead">Information</h1>
    {{ page.message }}
    {% if page.redirect %}<br /><a href="{{ page.redirect }}" class="default">Click here if you aren't being redirected.</a>{% endif %}
{% endblock %}
