<tr>
    <td class="topicIcon read">
        <div class="fa fa-2x fa-{% if topic.topic_status == 1 %}lock{% elseif topic.topic_type == 2 %}exclamation{% elseif topic.topic_type == 1 %}thumb-tack{% else %}navicon{% endif %}"></div>
    </td>
    <td class="topicTitle">
        <a href="{{ urls.format('FORUM_THREAD', [topic.topic_id]) }}" class="default">{{ topic.topic_title }}</a>
    </td>
    <td class="topicAuthor">
        {% if topic.first_poster.data.id %}
            <a href="{{ urls.format('USER_PROFILE', [topic.first_poster.data.id]) }}" class="default" style="color: {{ topic.first_poster.colour }}; text-shadow: 0 0 5px {% if topic.first_poster.colour != 'inherit' %}{{ topic.first_poster.colour }}{% else %}#222{% endif %};">{{ topic.first_poster.data.username }}</a>
        {% else %}
            [deleted user]
        {% endif %}
    </td>
    <td class="topicCounts">
        <div class="replies" title="Amount of replies to this topic.">{{ topic.reply_count }}</div>
        <div class="views" title="Amount of times this topic has been viewed.">{{ topic.topic_views }}</div>
    </td>
    <td class="topicLast">
        {% if topic.last_poster.data.id %}
            <a href="{{ urls.format('USER_PROFILE', [topic.last_poster.data.id]) }}" class="default" style="color: {{ topic.last_poster.colour }}; text-shadow: 0 0 5px {% if topic.last_poster.colour != 'inherit' %}{{ topic.last_poster.colour }}{% else %}#222{% endif %};">{{ topic.last_poster.data.username }}</a>
        {% else %}
            [deleted user]
        {% endif %} <a href="{{ urls.format('FORUM_POST', [topic.last_post.post.post_id]) }}#p{{ topic.last_post.post.post_id }}" class="default fa fa-tag"></a><br />
        <span title="{{ topic.last_post.post.post_time|date(sakura.dateFormat) }}">{{ topic.last_post.elapsed }}</span>
    </td>
</tr>
