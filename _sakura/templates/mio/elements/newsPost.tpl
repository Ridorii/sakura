{% if not (viewPost and postExists) %}<h3 class="miotitle" id="{{ newsPost.id }}">{{ post.title }} by <a href="{{ urls.format('USER_PROFILE', [post.poster.data.id]) }}" style="text-decoration: none !important; color: {{ post.poster.colour }} !important;">{{ post.poster.data.username }}</a> - {{ post.date|date(sakura.dateFormat) }}<span class="permalink"><a href="{{ urls.format('SITE_NEWS') }}#{{ newsPost.id }}" title="Permalink">#</a> <a href="{{ urls.format('SITE_NEWS_POST', [post.id]) }}" title="Direct Link">@</a></span></h3>{% endif %}
<div class="postcontent">
    {{ post.content_parsed|raw }}
</div>
<br />
