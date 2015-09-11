<div class="pagination">
    {% if pagination.page > 0 %}
        <a href="{{ urls.format(pagination.urlPattern, [pagination.page]) }}"><span class="fa fa-step-backward"></span></a>
    {% endif %}
    {% for id,page in pagination.pages %}
        <a href="{{ urls.format(pagination.urlPattern, [(id + 1)]) }}"{% if id == pagination.page %} class="current"{% endif %}>{{ id + 1 }}</a>
    {% endfor %}
    {% if pagination.page + 1 < pagination.pages|length %}
        <a href="{{ urls.format(pagination.urlPattern, [(pagination.page + 2)]) }}"><span class="fa fa-step-forward"></span></a>
    {% endif %}
</div>
