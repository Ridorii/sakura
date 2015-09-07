{% extends 'global/master.tpl' %}

{% block title %}{% if page.view_post %}{{ newsPosts[0].title }}{% elseif newsPosts|length < 1 %}Post does not exist!{% else %}News{% endif %}{% endblock %}

{% block content %}
    <div class="content">
        <div class="content-column news">
            <div class="head">{% if page.view_post %}{{ newsPosts[0].title }}{% elseif newsPosts|length < 1 %}Post does not exist!{% else %}News <a href="{{ urls.format('SITE_NEWS_RSS') }}" class="fa fa-rss news-rss default"></a>{% endif %}</div>
            {% if newsPosts|length >= 1 %}
                {% if page.view_post %}
                    {% for newsPost in newsPosts %}
                        {% include 'elements/newsPost.tpl' %}
                    {% endfor %}
                {% else %}
                    {% for newsPost in newsPosts[page.currentPage] %}
                        {% include 'elements/newsPost.tpl' %}
                    {% endfor %}
                {% endif %}
                {% if not page.view_post and newsPosts|length > 1 %}
                    <div>
                        <div class="pagination" style="float: right;">
                            {% if page.currentPage > 0 %}
                                <a href="{{ urls.format('SITE_NEWS_PAGE', [page.currentPage]) }}"><span class="fa fa-step-backward"></span></a>
                            {% endif %}
                            {% for id,npage in newsPosts %}
                                <a href="{{ urls.format('SITE_NEWS_PAGE', [(id + 1)]) }}"{% if id == page.currentPage %} class="current"{% endif %}>{{ id + 1 }}</a>
                            {% endfor %}
                            {% if page.currentPage + 1 < newsPosts|length %}
                                <a href="{{ urls.format('SITE_NEWS_PAGE', [(page.currentPage + 2)]) }}"><span class="fa fa-step-forward"></span></a>
                            {% endif %}
                        </div>
                        <div class="clear"></div>
                    </div>
                {% endif %}
            {% else %}
                <div style="padding: 20px;">
                    <h1>The requested news post does not exist!</h1>
                    There are a few possible reasons for this;
                    <ul style="margin-left: 30px;">
                        <li>The post may have been deleted due to irrelevancy.</li>
                        <li>The post never existed.</li>
                    </ul>
                </div>
            {% endif %}
        </div>
    </div>
{% endblock %}
