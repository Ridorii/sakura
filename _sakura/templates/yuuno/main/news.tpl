{% extends 'global/master.tpl' %}

{% set newsPosts = viewPost and postExists ? [news.getPost(postExists)] : news.getPosts(postsPerPage)[currentPage] %}

{% set pagination = {'page': currentPage, 'pages': news.getPosts(postsPerPage), 'urlPattern': 'SITE_NEWS_PAGE'} %}

{% set title %}
{% if not (viewPost ? postExists : newsPosts|length) %}Post does not exist!{% elseif viewPost and postExists %}{{ newsPosts[0].title }}{% else %}News{% endif %}
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
            <div class="head">{{ title }}{% if not (viewPost and postExists) %}<a href="{{ urls.format('SITE_NEWS_RSS') }}" class="fa fa-rss news-rss default"></a>{% endif %}</div>
            {% if (viewPost ? postExists : newsPosts|length) %}
                {% for post in newsPosts %}
                    {% include 'elements/newsPost.tpl' %}
                {% endfor %}
                {% if not (viewPost and postExists) and news.getPosts(postsPerPage)|length > 1 %}
                    <div>
                        {% include 'elements/pagination.tpl' %}
                        <div class="clear"></div>
                    </div>
                {% endif %}
                {% if viewPost and postExists %}
                    {% include 'elements/comments.tpl' %}
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
