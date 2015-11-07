{% if not (viewPost and postExists) %}<a href="{{ urls.format('SITE_NEWS_POST', [post.news_id]) }}" class="news-head" id="{{ post.news_category }}_{{ post.news_id }}">{{ post.news_title }}</a>{% endif %}
<div class="news-body">
    <a class="no-underline" href="{{ urls.format('USER_PROFILE', [post.news_poster.id]) }}">
        <div class="news-poster">
            <img src="{{ urls.format('IMAGE_AVATAR', [post.news_poster.id]) }}" alt="{{ post.news_poster.username }}" class="default-avatar-setting" />
            <h1 style="color: {{ post.news_poster.colour }}; text-shadow: 0 0 7px {% if post.news_poster.colour != 'inherit' %}{{ post.news_poster.colour }}{% else %}#222{% endif %}; padding: 0 0 10px;">{{ post.news_poster.username }}</h1>
        </div>
    </a>
    <div class="markdown">
        {{ post.news_content_parsed|raw }}
    </div>
</div>
<div class="clear"></div>
<div class="news-post-time">
    Posted on {{ post.news_timestamp|date(sakura.dateFormat) }}{% if not (viewPost and postExists) %} <a class="default" href="{{ urls.format('SITE_NEWS_POST', [post.news_id]) }}#comments">{{ post.news_comments.count }} comment{% if post.news_comments.count != 1 %}s{% endif %}</a>{% endif %}
</div>
