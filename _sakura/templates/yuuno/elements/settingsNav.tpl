<div class="head">
    Navigation
</div>
<div class="right-menu-nav">
    {% for catname,category in pages %}
        <div>{{ category.title }}</div>
        {% for mname,mode in category.modes %}
            {% if mode.access and mode.menu %}
                <a href="{{ urls.format('SETTING_MODE', [catname, mname]) }}">{{ mode.title }}</a>
            {% endif %}
        {% endfor %}
    {% endfor %}
</div>
