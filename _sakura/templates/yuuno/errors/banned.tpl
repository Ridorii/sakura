{% include 'global/header.tpl' %}
    <div class="content">
        <div class="content-column news banned">
            <div style="padding: 20px;">
                <h1>You are banned!</h1>
                <h3>The following reason was supplied:</h3>
                <p>
                    {{ ban.reason }}
                </p>
                <br />
                <h2>Additional information</h2>
                <ul style="margin-left: 30px;">
                    <li>You were banned on {{ ban.issued|date(sakura.date_format) }}.</li>
                    <li>{% if ban.expires %}This ban expires on {{ ban.expire|date(sakura.date_format) }}.{% else %}<b>You are permanently banned.</b>{% endif %}</li>
                    {% if ban.expires %}
                    <li>You were banned by <a href="/u/{{ ban.issuer.id }}" class="default">{{ ban.issuer.username }}</a>.</li>
                    {% endif %}
                </ul>
            </div>
        </div>
    </div>
{% include 'global/footer.tpl' %}
