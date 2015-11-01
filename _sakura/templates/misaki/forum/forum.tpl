<div class="forumListing">
    {% for category in board.forums %}
        {% include 'forum/forumEntry.tpl' %}
    {% endfor %}
</div>
