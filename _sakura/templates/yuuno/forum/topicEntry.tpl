<tr>
    <td class="topicIcon read">
        <div class="fa fa-2x fa-{% if topic.topic_status == 1 %}lock{% elseif topic.topic_type == 2 %}exclamation{% elseif topic.topic_type == 1 %}thumb-tack{% else %}navicon{% endif %}"></div>
    </td>
    <td class="topicTitle">
        <a href="/forum/thread/{{ topic.topic_id }}" class="default">{{ topic.topic_title }}</a>
    </td>
    <td class="topicAuthor">
        {% if topic.first_poster.user.id %}
            <a href="/u/{{ topic.first_poster.user.id }}" class="default" style="color: {% if topic.first_poster.user.name_colour %}{{ topic.first_poster.user.name_colour }}{% else %}{{ topic.first_poster.rank.colour }}{% endif %};">{{ topic.first_poster.user.username }}</a>
        {% else %}
            [deleted user]
        {% endif %}
    </td>
    <td class="topicCounts">
        <div class="replies" title="Amount of replies to this topic.">{{ topic.reply_count }}</div>
        <div class="views" title="Amount of times this topic has been viewed.">{{ topic.topic_views }}</div>
    </td>
    <td class="topicLast">
        {% if topic.last_poster.user.id %}
            <a href="/u/{{ topic.last_poster.user.id }}" class="default" style="color: {% if topic.last_poster.user.name_colour %}{{ topic.last_poster.user.name_colour }}{% else %}{{ topic.last_poster.rank.colour }}{% endif %};">{{ topic.last_poster.user.username }}</a>
        {% else %}
            [deleted user]
        {% endif %} <a href="/forum/post/{{ topic.last_poster.post.post_id }}#p{{ topic.last_poster.post.post_id }}" class="default fa fa-tag"></a><br />
        <span title="{{ topic.last_poster.post.post_time|date("r") }}">{{ topic.last_poster.elap }}</span>
    </td>
</tr>
