{% if options.fields %}
    <form enctype="multipart/form-data" method="post" action="{{ sakura.currentPage }}" id="optionsForm">
        <input type="hidden" name="sessid" value="{{ php.sessionid }}" />
        <input type="hidden" name="timestamp" value="{{ php.time }}" />
        <input type="hidden" name="mode" value="options" />
        {% for field in options.fields %}
            <div class="profile-field">
                <div>
                    <h2>{{ field.option_name }}</h2>
                    <div style="font-size: .8em; line-height: 110%;">
                        {{ field.option_description }}
                    </div>
                </div>
                <div style="padding: 8px 0;">
                    <input type="{{ field.option_type }}" name="option_{{ field.option_id }}" class="inputStyling"{% if user.optionFields[field.option_id] %}{% if field.option_type == 'checkbox' and user.optionFields[field.option_id] %} checked="checked" value="option_{{ field.option_id }}"{% else %} value="{{ user.optionFields[field.option_id] }}"{% endif %}{% endif %} />
                </div>
            </div>
        {% endfor %}
        <div class="profile-save">
            <input type="submit" value="Save" name="submit" class="inputStyling" />
            <input type="reset" value="Reset" name="reset" class="inputStyling" />
        </div>
    </form>
    <script type="text/javascript">
    window.addEventListener("load", function() {
        prepareAjaxForm('optionsForm', 'Changing Options...');
    });
    </script>
{% else %}
    <h1 class="stylised" style="margin: 2em auto; text-align: center;">There are currently no changeable options.</h1>
{% endif %}
