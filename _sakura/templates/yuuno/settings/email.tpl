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
                You e-mail address is used for password recovery and stuff like that, we won't spam you ;).
            </div>
            
        </div>
        <div class="clear"></div>
    </div>
{% include 'global/footer.tpl' %}
