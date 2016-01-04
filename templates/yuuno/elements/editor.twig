<form id="{{ editorFormId }}" method="post" action="{{ sakura.currentPage }}">
    <div class="head">Forum / Posting</div>
    <div class="posting-subject">
        <input type="text" class="inputStyling" name="subject" placeholder="Subject" value="{{ posting.subject }}" />
    </div>
    <hr class="default" />
    <div class="posting-bbcodes">
        {% for code,meta in bbcode %}
            <button onclick="insertBBcode('postingText', '{{ code }}'{% if meta[2] %}, true{% endif %});" type="button"{% if meta[0] %} title="{{ meta[0] }}"{% endif %} class="inputStyling{% if meta[1] %} fa fa-{{ meta[1] }}{% endif %}" style="min-width: 0;">{% if not meta[1] %}{{ code }}{% endif %}</button>
        {% endfor %}
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
        <input class="inputStyling" type="button" onclick="{{ cancelTarget }}" value="Cancel" />
    </div>
    {% if posting.id %}
    <input type="hidden" name="id" value="posting.id" />
    {% endif %}
    <input type="hidden" name="sessionid" value="{{ php.sessionid }}" />
    <input type="hidden" name="timestamp" value="{{ php.time }}" />
    <script type="text/javascript">
        window.addEventListener("load", function() {
            prepareAjaxForm('{{ editorFormId }}', 'Making post...');
        });
    </script>
</form>
