{#
{% if preview %}
    <div class="markdown" style="max-height: 600px;overflow-y:auto;">
        {{ preview|raw }}
    </div>
    <hr class="default" />
{% endif %}
<form enctype="multipart/form-data" method="post" action="{{ sakura.currentpage }}">
    <input type="hidden" name="sessid" value="{{ php.sessionid }}" />
    <input type="hidden" name="timestamp" value="{{ php.time }}" />
    <input type="hidden" name="mode" value="userpage" />
    <div><textarea name="userpage" placeholder="# Welcome to my profile page!" class="inputStyling" style="width: calc(100% - 12px); height: 500px;" />{{ userPage.content }}</textarea></div>
    <div>
        <h2>Parse mode</h2>
        <input type="radio" name="parse" value="bbcode" id="bbcode"{% if userPage.parse == 1 %} checked="checked"{% endif %} /> <label for="bbcode">BBCodes</label>
        <input type="radio" name="parse" value="markdown" id="markdown"{% if userPage.parse == 2 %} checked="checked"{% endif %} /> <label for="markdown">Markdown</label>
        <input type="radio" name="parse" value="plain" id="plain"{% if userPage.parse == 0 %} checked="checked"{% endif %} /> <label for="plain">Plain Text</label>
    </div>
    <div class="profile-save">
        <input type="submit" value="Save" name="submit" class="inputStyling" />
        <input type="submit" value="Preview" name="preview" class="inputStyling" />
        <input type="reset" value="Reset" name="reset" class="inputStyling" />
    </div>
</form>
#}
<h1 class="stylised">Redoing this bc garbage.</h1>
