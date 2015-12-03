{% extends 'global/master.tpl' %}

{% block title %}Posting{% endblock %}

{% block content %}
<div class="content">
    <div class="content-column forum posting">
        <form id="forumPostingForm" method="post" action="{{ sakura.currentPage }}">
            <div class="head">Forum / Posting</div>
            <div class="posting-subject">
                <input type="text" class="inputStyling" name="subject" placeholder="Subject" value="{{ posting.subject }}" />
            </div>
            <hr class="default" />
            <div class="posting-bbcodes">
                <button onclick="insertBBcode('postingText', 'b');" type="button" title="Bold" class="inputStyling fa fa-bold" style="min-width: 0;"></button>
                <button onclick="insertBBcode('postingText', 'i');" type="button" title="Italic" class="inputStyling fa fa-italic" style="min-width: 0;"></button>
                <button onclick="insertBBcode('postingText', 'u');" type="button" title="Underline" class="inputStyling fa fa-underline" style="min-width: 0;"></button>
                <button onclick="insertBBcode('postingText', 's');" type="button" title="Strikethrough" class="inputStyling fa fa-strikethrough" style="min-width: 0;"></button>
                <button onclick="insertBBcode('postingText', 'header');" type="button" title="Header" class="inputStyling fa fa-header" style="min-width: 0;"></button>
                <button onclick="insertBBcode('postingText', 'url');" type="button" title="Link" class="inputStyling fa fa-chain" style="min-width: 0;"></button>
                <button onclick="insertBBcode('postingText', 'spoiler');" type="button" title="Spoiler text" class="inputStyling fa fa-minus" style="min-width: 0;"></button>
                <button onclick="insertBBcode('postingText', 'box', true);" type="button" title="Spoiler box" class="inputStyling fa fa-square-o" style="min-width: 0;"></button>
                <button onclick="insertBBcode('postingText', 'list');" type="button" title="List (use [*] for entries)" class="inputStyling fa fa-list" style="min-width: 0;"></button>
                <button onclick="insertBBcode('postingText', 'img');" type="button" title="Image" class="inputStyling fa fa-picture-o" style="min-width: 0;"></button>
                <button onclick="insertBBcode('postingText', 'youtube');" type="button" title="YouTube video" class="inputStyling fa fa-youtube-play" style="min-width: 0;"></button>
            </div>
            <hr class="default" />
            <div class="posting-text">
                <textarea class="inputStyling" name="text" id="postingText">{{ posting.text }}</textarea>
            </div>
            <hr class="default" />
            <div class="posting-emotes">
                {% for emoticon in posting.emoticons %}
                    <img src="{{ emoticon.emote_path }}" alt="{{ emoticon.emote_string }}" title="{{ emoticon.emote_string }}" onclick="insertText('postingText', '{{ emoticon.emote_string }}')" />
                {% endfor %}
            </div>
            <hr class="default" />
            <div class="posting-buttons">
                <input class="inputStyling" type="submit" name="post" value="Post" />
                <input class="inputStyling" type="button" onclick="history.go(-1);" value="Cancel" />
            </div>
            {% if posting.id %}
                <input type="hidden" name="id" value="posting.id" />
            {% endif %}
            <input type="hidden" name="sessionid" value="{{ php.sessionid }}" />
            <input type="hidden" name="timestamp" value="{{ php.time }}" />
        </form>
    </div>
</div>
<script type="text/javascript">
    window.addEventListener("load", function() {
        prepareAjaxForm('forumPostingForm', 'Making post...');
    });
</script>
{% endblock %}
