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
                Sometimes we activate the registration key system which means that users can only register using your "referer" keys, this means we can keep unwanted people from registering. Each user can generate 5 of these keys, bans and deactivates render these keys useless.
            </div>
            
        </div>
        <div class="clear"></div>
    </div>
{% include 'global/footer.tpl' %}
