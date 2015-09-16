<form enctype="multipart/form-data" method="post" action="{{ sakura.currentPage }}" id="emailAddressChangeForm">
    <input type="hidden" name="sessid" value="{{ php.sessionid }}" />
    <input type="hidden" name="timestamp" value="{{ php.time }}" />
    <input type="hidden" name="mode" value="email" />
    <h3 style="text-align: center;">Your e-mail address is currently set to <span style="font-weight: 700;">{{ user.data.email }}</span>.</h3>
    <div class="profile-field">
        <div><h2>E-mail address</h2></div>
        <div><input type="text" name="emailnew" placeholder="Enter your new e-mail address" class="inputStyling" /></div>
    </div>
    <div class="profile-field">
        <div><h2>Confirmation</h2></div>
        <div><input type="text" name="emailver" placeholder="Just to make sure..." class="inputStyling" /></div>
    </div>
    <div class="profile-save">
        <input type="submit" value="Save" name="submit" class="inputStyling" />
        <input type="reset" value="Reset" name="reset" class="inputStyling" />
    </div>
</form>
<script type="text/javascript">
window.addEventListener("load", function() {

    prepareAjaxForm('emailAddressChangeForm', 'Changing E-mail address...');

});
</script>
