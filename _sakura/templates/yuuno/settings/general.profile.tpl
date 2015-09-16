{% set birthday = user.data.birthday|split('-') %}

<form enctype="multipart/form-data" method="post" action="{{ sakura.currentPage }}" id="editProfileForm">
    <input type="hidden" name="sessid" value="{{ php.sessionid }}" />
    <input type="hidden" name="timestamp" value="{{ php.time }}" />
    <input type="hidden" name="mode" value="profile" />
    {% for field in profile.fields %}
        <div class="profile-field">
            <div>
                <h2>{{ field.name }}</h2>
            </div>
            <div>
                <input type="{{ field.formtype }}" name="profile_{{ field.ident }}" class="inputStyling" placeholder="{{ field.description }}"{% if user.profileFields[field.ident].value %}{% if field.formtype == 'checkbox' and user.profileFields[field.ident].value == 'true' %} checked="checked" value="profile_{{ field.ident }}"{% else %} value="{{ user.profileFields[field.ident].value }}"{% endif %}{% endif %} />
            </div>
            {% if field.addit %}
                {% for id,addit in field.addit %}
                    <div>
                        <input type="{{ addit[0] }}" id="{{ id }}" name="profile_additional_{{ id }}"{% if user.profileFields[field.ident][id] %}{% if addit[0] == 'checkbox' and user.profileFields[field.ident][id] == true %} checked="checked"{% else %} value="{{ user.profileFields[field.ident][id] }}"{% endif %}{% endif %} />
                        <label for="{{ id }}" style="font-size: 10px;">{{ addit[1]|raw }}</label>
                    </div>
                {% endfor %}
            {% endif %}
        </div>
    {% endfor %}
    <div class="profile-field birthday">
        <div>
            <h2>Birthday</h2>
        </div>
        <div style="text-align: center;">
            Day: <select name="birthday_day">
                <option value="0"{% if not birthday[2] %} selected="selected"{% endif %}>--</option>
                {% for i in 1..31 %}
                    <option value="{{ i }}"{% if birthday[2] == i %} selected="selected"{% endif %}>{{ i }}</option>
                {% endfor %}
            </select>
            Month: <select name="birthday_month">
                <option value="0"{% if not birthday[1] %} selected="selected"{% endif %}>--</option>
                {% for i in 1..12 %}
                    <option value="{{ i }}"{% if birthday[1] == i %} selected="selected"{% endif %}>{{ profile.months[i] }}</option>
                {% endfor %}
            </select>
            Year: <select name="birthday_year">
                <option value="0"{% if not birthday[0] %} selected="selected"{% endif %}>----</option>
                {% for i in "now"|date('Y')..("now"|date('Y') - 100) %}
                    <option value="{{ i }}"{% if birthday[0] == i %} selected="selected"{% endif %}>{{ i }}</option>
                {% endfor %}
            </select>
        </div>
    </div>
    <div class="profile-save">
        <input type="submit" value="Save" name="submit" class="inputStyling" />
        <input type="reset" value="Reset" name="reset" class="inputStyling" />
    </div>
</form>
<script type="text/javascript">
window.addEventListener("load", function() {

    prepareAjaxForm('editProfileForm', 'Updating Profile...');

});
</script>
