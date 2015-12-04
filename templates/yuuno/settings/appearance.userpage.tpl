{% block js %}
    <script type="text/javascript" charset="utf-8" src="{{ sakura.contentPath }}/libraries/showdown.js"></script>
{% endblock %}

<div class="markdown" id="userPagePreview" style="max-height: 500px; overflow-y: auto; background: #C2AEEE; box-shadow: inset 0 0 1em 1em #D3BFFF;">
    <noscript>
        <h1 class="stylised" style="margin: 1em auto;">The preview requires JavaScript, enable it.</h1>
    </noscript>
</div>
<hr class="default" />
<form enctype="multipart/form-data" method="post" action="{{ sakura.currentPage }}" id="userPageEditorForm">
    <input type="hidden" name="sessid" value="{{ php.sessionid }}" />
    <input type="hidden" name="timestamp" value="{{ php.time }}" />
    <input type="hidden" name="mode" value="userpage" />
    <div><textarea name="userpage" id="userPageEditor" placeholder="# Welcome to my user page!" class="inputStyling" style="width: calc(100% - 12px); height: 400px;">{% if userPage %}{{ userPage }}{% else %}# Welcome to my user page!{% endif %}</textarea></div>
    <div class="profile-save">
        <input type="submit" value="Save" name="submit" class="inputStyling" />
        <input type="reset" value="Reset" name="reset" class="inputStyling" />
    </div>
</form>
<script type="text/javascript">
var converter = new showdown.Converter();

function updateUserPage() {
    document.getElementById('userPagePreview').innerHTML = converter.makeHtml(safeTagsReplace(document.getElementById('userPageEditor').value));
}

document.getElementById('userPageEditor').addEventListener('keyup', updateUserPage);

window.addEventListener('load', function() {
    prepareAjaxForm('userPageEditorForm', 'Updating user page...');
    updateUserPage();
});
</script>