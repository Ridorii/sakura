{% if not viewPost %}<a href="{{ urls.format('SITE_NEWS_POST', [post.id]) }}" class="news-head" id="{{ post.category }}_{{ post.id }}">{{ post.title }}</a>{% endif %}
<div class="news-body">
    <a class="no-underline" href="{{ urls.format('USER_PROFILE', [post.poster.data.id]) }}">
        <div class="news-poster">
            <img src="{{ urls.format('IMAGE_AVATAR', [post.poster.data.id]) }}" alt="{{ post.poster.data.username }}" class="default-avatar-setting" />
            <h1 style="color: {{ post.poster.colour }}; text-shadow: 0 0 7px {% if post.poster.colour != 'inherit' %}{{ post.poster.colour }}{% else %}#222{% endif %}; padding: 0 0 10px;">{{ post.poster.data.username }}</h1>
        </div>
    </a>
    <div class="markdown">
        {{ post.content_parsed|raw }}
    </div>
</div>
<div class="clear"></div>
<div class="news-post-time">
    Posted on {{ post.date|date(sakura.dateFormat) }}{% if not viewPost %} <a class="default" href="{{ urls.format('SITE_NEWS_POST', [post.id]) }}">X comments</a>{% endif %}
</div>
