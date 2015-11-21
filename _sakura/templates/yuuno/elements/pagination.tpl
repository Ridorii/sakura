{% set paginationSeparator %}{% if '?' in pagination.page %}&amp;{% else %}?{% endif %}{% endset %}
{% set paginationPage = get.page|default(1) %}

<div class="pagination{% if paginationClass %} {{ paginationClass }}{% endif %}">
    {% if paginationPages|length > 1 %}
        {% if paginationPage > 1 %}
            {% if paginationPages|length > 2 %}
                <a href="{{ paginationUrl }}{{ paginationSeparator }}page=1" title="Jump to first page"><span class="fa fa-fast-backward"></span></a>
            {% endif %}
            <a href="{{ paginationUrl }}{{ paginationSeparator }}page={{ paginationPage - 1 }}" title="Previous page"><span class="fa fa-step-backward"></span></a>
        {% endif %}
        {% for id,page in paginationPages %}
            {% if (id + 1) > (paginationPage - 3) and (id + 1) < (paginationPage + 3) %}
                <a href="{{ paginationUrl }}{{ paginationSeparator }}page={{ id + 1 }}"{% if id == paginationPage - 1 %} class="current"{% endif %} title="Page {{ id + 1 }}">{{ id + 1 }}</a>
            {% endif %}
        {% endfor %}
        {% if paginationPage < paginationPages|length %}
            <a href="{{ paginationUrl }}{{ paginationSeparator }}page={{ paginationPage + 1 }}" title="Next page"><span class="fa fa-step-forward"></span></a>
            {% if paginationPages|length > 2 %}
                <a href="{{ paginationUrl }}{{ paginationSeparator }}page={{ paginationPages|length }}" title="Jump to last page"><span class="fa fa-fast-forward"></span></a>
            {% endif %}
        {% endif %}
    {% endif %}
</div>
