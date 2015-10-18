{% extends 'global/master.tpl' %}

{% block title %}Posting{% endblock %}

{% block content %}
<div class="content">
    <div class="content-column forum posting">
        <form id="forumPostingForm" method="post" action="{{ sakura.currentPage }}">
            <div class="head">Forum / Posting</div>
            <div class="posting-subject">
                <input type="text" class="inputStyling" name="subject" placeholder="Subject" />
            </div>
            <hr class="default" />
            <div class="posting-bbcodes">
                {% for bbcode in posting.bbcodes %}
                    {% if bbcode.bbcode_display %}
                        <button type="button" class="inputStyling small">{{ bbcode.bbcode_title }}</button>
                    {% endif %}
                {% endfor %}
            </div>
            <hr class="default" />
            <div class="posting-bbcode-description" id="bbcodedescr">
                Hover over a styling button to view a short description of what it does.
            </div>
            <hr class="default" />
            <div class="posting-text">
                <textarea class="inputStyling" name="text" id="postingText"></textarea>
            </div>
            <hr class="default" />
            <div class="posting-emotes">
                {% for emoticon in posting.emoticons %}
                    <img src="{{ emoticon.emote_path }}" alt="{{ emoticon.emote_string }}" title="{{ emoticon.emote_string }}" />
                {% endfor %}
            </div>
            <hr class="default" />
            <div class="posting-options">
                <div>
                    <div>
                        <input type="checkbox" id="enableSig" checked="checked" /> <label for="enableSig">Attach Signature</label>
                    </div>
                    <div>
                        <input type="checkbox" id="enableEmotes" checked="checked" /> <label for="enableEmotes">Parse emoticons</label>
                    </div>
                </div>
                <div>
                    <label for="parseMode">Parsing Mode:</label>
                    <select id="parseMode" name="parseMode">
                        <option value="0">None</option>
                        <option value="1" selected="selected">BBCode</option>
                        <option value="2">Markdown</option>
                    </select>
                </div>
                <div class="clear"></div>
            </div>
            <hr class="default" />
            <div class="posting-buttons">
                <input class="inputStyling" type="submit" name="preview" value="Preview" disabled="disabled" />
                <input class="inputStyling" type="submit" name="post" value="Post" />
                <input class="inputStyling" type="button" onclick="history.go(-1);" value="Cancel" />
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    window.addEventListener("load", function() {
        prepareAjaxForm('forumPostingForm', 'Making post...');
    });
</script>
{% endblock %}
