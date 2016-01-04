{% set eligible = user.getUsernameHistory ? (php.time - user.getUsernameHistory()[0].change_time) > 2592000 : true %}

<form enctype="multipart/form-data" method="post" action="{{ sakura.currentPage }}" id="changeUsernameForm">
    <input type="hidden" name="sessid" value="{{ php.sessionid }}" />
    <input type="hidden" name="timestamp" value="{{ php.time }}" />
    <input type="hidden" name="mode" value="username" />
    <h1 class="stylised" style="text-align: center; margin-top: 10px;{% if not eligible %} color: #c44;{% endif %}">You are {% if not eligible %}not {% endif %}eligible for a name change.</h1>
    <h3 style="text-align: center;">{% if user.getUsernameHistory %}Your last name change was {{ difference }}.{% else %}This is your first username change.{% endif %}</h3>
    {% if eligible %}
        <div class="profile-field">
            <div><h2>Username</h2></div>
            <div><input type="text" name="username" placeholder="Enter your new username (at least {{ sakura.minUsernameLength }} and at most {{ sakura.maxUsernameLength }} characters!)" class="inputStyling" /></div>
        </div>
        <div class="profile-save">
            <input type="submit" value="Save" name="submit" class="inputStyling" />
            <input type="reset" value="Reset" name="reset" class="inputStyling" />
        </div>
    {% endif %}
</form>
<script type="text/javascript">
window.addEventListener("load", function() {
    prepareAjaxForm('changeUsernameForm', 'Changing username...');
});
</script>
