{% if not page.view_post %}<a href="/news/{{ newsPost.id }}" class="news-head" id="n{{ newsPost.id }}">{{ newsPost.title }}</a>{% endif %}
<div class="news-body">
    <a class="no-underline" href="/u/{{ newsPost.uid }}">
        <div class="news-poster">
            <img src="/a/{{ newsPost.uid }}" alt="{{ newsPost.udata.username }}" class="default-avatar-setting" />
            <h1 style="color: {{ newsPost.rdata.colour }} !important; text-shadow: 0 0 7px #888; padding: 0 0 10px;">{{ newsPost.udata.username }}</h1>
        </div>
    </a>
    <div class="markdown">
        {{ newsPost.parsed|raw }}
    </div>
</div>
<div class="clear"></div>
<div class="news-post-time">
    Posted on {{ newsPost.date|date(sakura.dateFormat) }}{% if not page.view_post %} <a class="default" href="/news/{{ newsPost.id }}">View comments</a>{% endif %}
</div>
