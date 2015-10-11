{% extends 'global/master.tpl' %}

{% block title %}{% if page.title %}{{ page.title }}{% else %}Not found!{% endif %}{% endblock %}

{% block content %}
    <div class="markdown">
        <div>
            {{ page.content|raw }}
        </div>
    </div>
{% endblock %}
