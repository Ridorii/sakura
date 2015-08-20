{% include 'global/header.tpl' %}
<div class="content">
    <div class="content-column forum posting">
        <form method="post" action="{{ sakura.currentPage }}">
            <div class="head">Forum / Posting</div>
            <div class="posting-subject">
                <input type="text" class="inputStyling" name="subject" placeholder="Subject" />
            </div>
            <hr class="default" />
            <div class="posting-bbcodes">
                {% for bbcode in posting.bbcodes %}
                    {% if bbcode.on_posting %}
                        <button type="button" class="inputStyling small">{{ bbcode.title }}</button>
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
                    <select>
                        <option>None</option>
                        <option selected="selected">BBCodes</option>
                        <option>Markdown</option>
                    </select>
                </div>
                <div class="clear"></div>
            </div>
            <hr class="default" />
            <div class="posting-buttons">
                <input class="inputStyling" type="submit" name="preview" value="Preview" />
                <input class="inputStyling" type="submit" name="post" value="Post" />
                <input class="inputStyling" type="submit" name="cancel" value="Cancel" />
            </div>
        </form>
    </div>
</div>
{% include 'global/footer.tpl' %}
