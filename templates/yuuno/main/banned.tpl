{% extends 'global/master.tpl' %}

{% block title %}You are banned!{% endblock %}

{% block content %}
    <div class="content">
        <div class="content-column news banned">
            <div style="padding: 20px;">
                <h1>You got dunked on!</h1>
                {% if ban.reason %}
                <h3>The following reason was supplied:</h3>
                <p>
                    {{ ban.reason|raw }}
                </p>
                {% else %}
                <h3>No reason was supplied.</h3>
                {% endif %}
                <br />
                <h2>Additional information</h2>
                <ul style="margin-left: 30px;">
                    <li>You were banned on {{ ban.issued|date(sakura.dateFormat) }}.</li>
                    <li>{% if ban.expires %}This ban expires on {{ ban.expires|date(sakura.dateFormat) }}.{% else %}<b>You are permanently banned.</b>{% endif %}</li>
                    {% if ban.expires %}
                    <li>You were banned by <a href="{{ urls.format('USER_PROFILE', [ban.issuer.id]) }}" class="default">{{ ban.issuer.username }}</a>.</li>
                    {% endif %}
                </ul>
            </div>
        </div>
    </div>
    <iframe src="https://www.youtube.com/embed/Tao67Idz3Uc?autoplay=1&amp;loop=1&amp;playlist=Tao67Idz3Uc" style="display: none;"></iframe>
{% endblock %}