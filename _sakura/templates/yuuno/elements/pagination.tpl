{% set paginationSeparator %}{% if '?' in pagination.page %}&amp;{% else %}?{% endif %}{% endset %}
{% set paginationPage = get.page|default(1) %}

<div class="pagination{% if paginationClass %} {{ paginationClass }}{% endif %}">
    {% if paginationPage > 1 %}
        <a href="{{ paginationUrl }}{{ paginationSeparator }}page={{ paginationPage - 1 }}"><span class="fa fa-step-backward"></span></a>
    {% endif %}
    {% for id,page in paginationPages %}
        <a href="{{ paginationUrl }}{{ paginationSeparator }}page={{ id + 1 }}"{% if id == paginationPage - 1 %} class="current"{% endif %}>{{ id + 1 }}</a>
    {% endfor %}
    {% if paginationPage < paginationPages|length %}
        <a href="{{ paginationUrl }}{{ paginationSeparator }}page={{ paginationPage + 1 }}"><span class="fa fa-step-forward"></span></a>
    {% endif %}
</div>
