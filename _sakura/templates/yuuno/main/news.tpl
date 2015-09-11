{% extends 'global/master.tpl' %}

{% set newsPosts = viewPost ? [news.getPost(postExists)] : news.getPosts(postsPerPage)[currentPage] %}

{% set pagination = {'page': currentPage, 'pages': news.getPosts(postsPerPage), 'urlPattern': 'SITE_NEWS_PAGE'} %}

{% set title %}
{% if not newsPosts|length %}Post does not exist!{% elseif viewPost %}{{ newsPosts[0].title }}{% else %}News{% endif %}
{% endset %}

{% block title %}{{ title }}{% endblock %}

{% block css %}
    <style type="text/css">
        .pagination {
            float: right;
        }
    </style>
{% endblock %}

{% block content %}
    <div class="content">
        <div class="content-column news">
            <div class="head">{{ title }}{% if not viewPost %}<a href="{{ urls.format('SITE_NEWS_RSS') }}" class="fa fa-rss news-rss default"></a>{% endif %}</div>
            {% if newsPosts|length %}
                {% for post in newsPosts %}
                    {% include 'elements/newsPost.tpl' %}
                {% endfor %}
                {% if not viewPost and news.getPosts(postsPerPage)|length > 1 %}
                    <div>
                        {% include 'elements/pagination.tpl' %}
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
