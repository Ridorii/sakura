{% if not page.view_post %}<a href="{{ urls.format('SITE_NEWS_POST', [newsPost.id]) }}" class="news-head" id="{{ newsPost.category}}_{{ newsPost.id }}">{{ newsPost.title }}</a>{% endif %}
<div class="news-body">
    <a class="no-underline" href="{{ urls.format('USER_PROFILE', [newsPost.uid]) }}">
        <div class="news-poster">
            <img src="{{ urls.format('IMAGE_AVATAR', [newsPost.uid]) }}" alt="{{ newsPost.udata.username }}" class="default-avatar-setting" />
            <h1 style="color: {{ newsPost.rdata.colour }}; text-shadow: 0 0 7px {% if newsPost.rdata.colour != 'inherit' %}{{ newsPost.rdata.colour }}{% else %}#222{% endif %};; padding: 0 0 10px;">{{ newsPost.udata.username }}</h1>
        </div>
    </a>
    <div class="markdown">
        {{ newsPost.parsed|raw }}
    </div>
</div>
<div class="clear"></div>
<div class="news-post-time">
    Posted on {{ newsPost.date|date(sakura.dateFormat) }}{% if not page.view_post %} <a class="default" href="{{ urls.format('SITE_NEWS_POST', [newsPost.id]) }}">X comments</a>{% endif %}
</div>
