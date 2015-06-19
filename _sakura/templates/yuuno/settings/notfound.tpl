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
                This is an error.
            </div>
            <h1 class="stylised" style="margin: 2em auto; text-align: center;">Could not find what you were looking for.</h1>
        </div>
        <div class="clear"></div>
    </div>
{% include 'global/footer.tpl' %}
