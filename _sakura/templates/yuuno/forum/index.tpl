{% extends 'global/master.tpl' %}

{% set title = 'Forums' %}

{% block title %}{{ title }}{% endblock %}

{% set forumMarkRead %}{{ urls.format('FORUM_MARK_READ', [forum.id, php.sessionid]) }}{% endset %}

{% block content %}
    <div class="content homepage forum">
        <div class="content-right content-column">
            {% include 'elements/indexPanel.tpl' %}
        </div>
        <div class="content-left content-column">
            {% include 'forum/forum.tpl' %}
            {% include 'forum/forumBtns.tpl' %}
        </div>
        <div class="clear"></div>
    </div>
{% endblock %}

{% block js %}
<script type="text/javascript" src="{{ sakura.resources }}/js/ybabstat.js"></script>
{% endblock %}
