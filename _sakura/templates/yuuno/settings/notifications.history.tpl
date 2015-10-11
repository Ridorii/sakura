{% if alerts %}
    <div class="notification-history">
        {% for alert in alerts[page.currentPage] %}
        <a id="notif-hist-{{ alert.id }}" class="clean {% if alert.alert_read %}read{% endif %}"{% if alert.alert_link %} href="{{ alert.alert_link }}"{% endif %}>
            <div class="notif-hist-icon">
            {% if 'FONT:' in alert.alert_img %}
                <div class="font-icon fa {{ alert.alert_img|replace({'FONT:': ''}) }} fa-4x"></div>
            {% else %}
                <img src="{{ alert.alert_img }}" alt="Notification" />
            {% endif %}
            </div>
            <div class="notif-hist-content">
                <div class="notif-hist-inside">
                    <div class="notif-hist-title">
                        {{ alert.alert_title }}
                    </div>
                    <div class="notif-hist-text">
                        {{ alert.alert_text }}
                    </div>
                </div>
                <div class="notif-hist-time">
                    {{ alert.alert_timestamp|date(sakura.dateFormat) }}
                </div>
            </div>
            <div class="clear"></div>
        </a>
        {% endfor %}
    </div>
    {% if alerts|length > 1 %}
        <div>
            <div class="pagination" style="float: right;">
                {% if page.currentPage > 0 %}
                    <a href="{{ urls.format('SETTING_PAGE', ['notifications', 'history', page.currentPage]) }}"><span class="fa fa-step-backward"></span></a>
                {% endif %}
                {% for id,npage in alerts %}
                    <a href="{{ urls.format('SETTING_PAGE', ['notifications', 'history', id + 1]) }}"{% if id == page.currentPage %} class="current"{% endif %}>{{ id + 1 }}</a>
                {% endfor %}
                {% if page.currentPage + 1 < alerts|length %}
                    <a href="{{ urls.format('SETTING_PAGE', ['notifications', 'history', page.currentPage + 2]) }}"><span class="fa fa-step-forward"></span></a>
                {% endif %}
            </div>
            <div class="clear"></div>
        </div>
    {% endif %}
{% else %}
    <h1 class="stylised" style="margin: 2em auto; text-align: center;">You don't have any notifications in your history!</h1>
{% endif %}
