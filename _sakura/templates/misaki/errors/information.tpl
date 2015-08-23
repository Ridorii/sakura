{% include 'global/header.tpl' %}
    <div class="content standalone">
        <h1 class="sectionHeader">Information</h1>
        <hr class="default" />
        {{ page.message }}
        {% if page.redirect %}<br /><a href="{{ page.redirect }}" class="default">Click here if you aren't being redirected.</a>{% endif %}
    </div>
{% include 'global/footer.tpl' %}
