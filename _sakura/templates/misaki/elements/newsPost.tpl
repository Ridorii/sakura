<div class="news-post" id="n{{ newsPost.id }}">
    <div class="news-header">
        <a class="news-title floatLeft" href="/news/{{ newsPost.id }}">{{ newsPost.title }}</a>
        <div class="news-details floatRight">
            <div>{{ newsPost.date|date("D Y-m-d H:i:s T") }}</div>
            <div>Posted by <a style="color: {{ newsPost.rdata.colour }};" href="/u/{{ newsPost.uid }}">{{ newsPost.udata.username }}</a>{% if newsPosts|length > 1 %} / <a class="default" href="/news/{{ newsPost.id }}#disqus_thread">View comments</a>{% endif %}</div>
        </div>
        <div class="clear"></div>
    </div>
    <div class="news-content">
        <div class="news-avatar">
            <img src="/a/{{ newsPost.uid }}" alt="{{ newsPost.udata.username }}" />
        </div>
        <div class="news-text">
            {{ newsPost.parsed|raw }}
        </div>
        <div class="clear"></div>
    </div>
</div>
