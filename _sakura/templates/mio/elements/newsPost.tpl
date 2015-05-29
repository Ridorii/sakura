{% if newsPosts|length > 1 %}<h3 class="miotitle" id="{{ newsPost.id }}">{{ newsPost.title }} by <a href="/u/{{ newsPost.uid }}" style="text-decoration: none !important; color: {{ newsPost.rdata.colour }} !important;">{{ newsPost.udata.username }}</a> - {{ newsPost.date|date("D Y-m-d H:i:s T") }}<span class="permalink"><a href="/news#{{ newsPost.id }}" title="Permalink">#</a> <a href="/news/{{ newsPost.id }}" title="Direct Link">@</a></span></h3>{% endif %}
<div class="postcontent">
    {{ newsPost.parsed|raw }}
</div>
<br />
