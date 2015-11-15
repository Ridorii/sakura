<tr>
    <td class="topicIcon read">
        <div class="fa fa-2x fa-{% if thread.status == 1 %}lock{% elseif thread.type == 2 %}exclamation{% elseif thread.type == 1 %}thumb-tack{% else %}navicon{% endif %}"></div>
    </td>
    <td class="topicTitle">
        <a href="{{ urls.format('FORUM_THREAD', [thread.id]) }}" class="default">{{ thread.title }}</a>
    </td>
    <td class="topicAuthor">
        {% if thread.firstPost.poster.id %}
            <a href="{{ urls.format('USER_PROFILE', [thread.firstPost.poster.id]) }}" class="default" style="color: {{ thread.firstPost.poster.colour }}; text-shadow: 0 0 5px {% if thread.firstPost.poster.colour != 'inherit' %}{{ thread.firstPost.poster.colour }}{% else %}#222{% endif %};">{{ thread.firstPost.poster.username }}</a>
        {% else %}
            [deleted user]
        {% endif %}
    </td>
    <td class="topicCounts">
        <div class="replies" title="Amount of replies to this thread.">{{ thread.replyCount }}</div>
        <div class="views" title="Amount of times this thread has been viewed.">{{ thread.views }}</div>
    </td>
    <td class="topicLast">
        {% if thread.lastPost.poster.id %}
            <a href="{{ urls.format('USER_PROFILE', [thread.lastPost.poster.id]) }}" class="default" style="color: {{ thread.lastPost.poster.colour }}; text-shadow: 0 0 5px {% if thread.lastPost.poster.colour != 'inherit' %}{{ thread.lastPost.poster.colour }}{% else %}#222{% endif %};">{{ thread.lastPost.poster.username }}</a>
        {% else %}
            [deleted user]
        {% endif %} <a href="{{ urls.format('FORUM_POST', [thread.lastPost.id]) }}#p{{ thread.lastPost.id }}" class="default fa fa-tag"></a><br />
        <span title="{{ thread.lastPost.time|date(sakura.dateFormat) }}">{{ thread.lastPost.timeElapsed }}</span>
    </td>
</tr>
