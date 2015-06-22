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
                <div>The background that is displayed on your profile.</div>
                <div>Maximum image size is 2560x1440, minimum image size is 20x20, maximum file size is 10 MB.</div>
            </div>
            
        </div>
        <div class="clear"></div>
    </div>
{% include 'global/footer.tpl' %}
