{% if options.fields %}
    <form enctype="multipart/form-data" method="post" action="{{ sakura.currentPage }}" id="optionsForm">
        <input type="hidden" name="sessid" value="{{ php.sessionid }}" />
        <input type="hidden" name="timestamp" value="{{ php.time }}" />
        <input type="hidden" name="mode" value="options" />
        {% for field in options.fields %}
            <div class="profile-field">
                <div>
                    <h2>{{ field.name }}</h2>
                    <div style="font-size: .8em; line-height: 110%;">
                        {{ field.description }}
                    </div>
                </div>
                <div style="padding: 8px 0;">
                    <input type="{{ field.formtype }}" name="option_{{ field.id }}" class="inputStyling"{% if options.user[field.id] %}{% if field.formtype == 'checkbox' and options.user[field.id] %} checked="checked" value="option_{{ field.id }}"{% else %} value="{{ options.user[field.id] }}"{% endif %}{% endif %} />
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
        var optionsForm = document.getElementById('optionsForm');
        var createInput = document.createElement('input');
        var submit      = optionsForm.querySelector('[type="submit"]');

        createInput.setAttribute('name', 'ajax');
        createInput.setAttribute('value', 'true');
        createInput.setAttribute('type', 'hidden');
        optionsForm.appendChild(createInput);

        submit.setAttribute('type', 'button');
        submit.setAttribute('onclick', 'submitPost(\''+ optionsForm.action +'\', formToObject(\'optionsForm\'), true, \'Changing Options...\');');
    });
    </script>
{% else %}
    <h1 class="stylised" style="margin: 2em auto; text-align: center;">There are currently no changeable options.</h1>
{% endif %}
