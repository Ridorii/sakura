{% include 'global/header.tpl' %}
    <div class="content settings messages">
        <div class="content-right content-column">
            {% include 'elements/settingsNav.tpl' %}
        </div>
        <div class="content-left content-column">
            <div class="head">
                {{ page.title }}
            </div>
            <div class="settings-explanation">
            {% for descline in page.description %}
                <div>{{ include(template_from_string(descline)) }}</div>
            {% endfor %}
            </div>
            {% include 'settings/' ~ current ~ '.tpl' %}
        </div>
        <div class="clear"></div>
    </div>
{% include 'global/footer.tpl' %}
