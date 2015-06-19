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
                <div>Probably the biggest part of your identity on a site.</div>
                <div style="font-weight: bold;">You can only change this once every 30 days so choose wisely.</div>
            </div>
            
        </div>
        <div class="clear"></div>
    </div>
{% include 'global/footer.tpl' %}
