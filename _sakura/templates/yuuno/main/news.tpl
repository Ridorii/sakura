{% extends 'global/master.tpl' %}

{% set newsPosts = viewPost and postExists ? [news.posts[postExists]] : news.posts|batch(postsPerPage)[get.page|default(1) - 1] %}

{% if viewPost and postExists %}
    {% set commentsCategory = 'news-' ~ newsPosts[0].news_category ~ '-' ~ newsPosts[0].news_id %}
    {% set comments = newsPosts[0].news_comments.comments %}
{% else %}
    {% set paginationPages = news.posts|batch(postsPerPage) %}
    {% set paginationUrl %}{{ urls.format('SITE_NEWS') }}{% endset %}
{% endif %}

{% set title %}
{% if not (viewPost ? postExists : newsPosts|length) %}Post does not exist!{% elseif viewPost and postExists %}{{ newsPosts[0].news_title }}{% else %}News{% endif %}
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
            <div class="head">{{ title }}{% if not (viewPost and postExists) %}<div class="links"><a href="{{ urls.format('SITE_NEWS_RSS') }}" class="fa fa-rss news-rss default"></a></div>{% endif %}</div>
            {% if (viewPost ? postExists : newsPosts|length) %}
                {% for post in newsPosts %}
                    {% include 'elements/newsPost.tpl' %}
                    {% if viewPost and postExists %}
                        {% include 'elements/comments.tpl' %}
                    {% endif %}
                {% endfor %}
                {% if not (viewPost and postExists) and news.posts|batch(postsPerPage)|length > 1 %}
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
