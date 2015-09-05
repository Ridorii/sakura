{% extends 'global/master.tpl' %}

{% block content %}
    <div class="content standalone">
        <div>
            <h1>Information</h1>
            <hr class="default" />
            {{ page.message }}
            {% if page.redirect %}<br /><a href="{{ page.redirect }}" class="default">Click here if you aren't being redirected.</a>{% endif %}
        </div>
    </div>
{% endblock %}
