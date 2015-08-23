<div class="head">
    Navigation
</div>
<div class="right-menu-nav">
    {% for catname,category in pages %}
        <div>{{ category.title }}</div>
        {% for mname,mode in category.modes %}
            {% if mode.access %}
                <a href="/{% if catname != 'messages' %}settings/{% endif %}{{ catname }}/{{ mname }}/">{{ mode.title }}</a>
            {% endif %}
        {% endfor %}
    {% endfor %}
</div>
