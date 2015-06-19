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
                Session keys are a way of identifying yourself with the system without keeping your password in memory.
                If someone finds one of your session keys they could possibly compromise your account, if you see any sessions here that shouldn't be here hit the Kill button to kill the selected session.
                If you get logged out after clicking one you've most likely killed your current session, to make it easier to avoid this from happening your current session is highlighted.
            </div>
            
        </div>
        <div class="clear"></div>
    </div>
{% include 'global/footer.tpl' %}
