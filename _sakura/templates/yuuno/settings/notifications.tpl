{% include 'global/header.tpl' %}
    <div class="content settings messages">
        <div class="content-right content-column">
            {% include 'elements/settingsNav.tpl' %}
        </div>
        <div class="content-left content-column">
            <div class="head">
                {{ page.title }}
            </div>
            <div class="settings-explanation">
                This is the history of notifications that have been sent to you.
            </div>
            <div class="notification-history">
                {% for notif in notifs[page.currentPage] %}
                <a id="notif-hist-{{ notif.id }}" class="clean {% if notif.notif_read %}read{% endif %}"{% if notif.notif_link %} href="{{ notif.notif_link }}"{% endif %}>
                    <div class="notif-hist-icon">
                    {% if 'FONT:' in notif.notif_img %}
                        <div class="font-icon fa {{ notif.notif_img|replace({'FONT:': ''}) }} fa-4x"></div>
                    {% else %}
                        <img src="{{ notif.notif_img }}" alt="Notification" />
                    {% endif %}
                    </div>
                    <div class="notif-hist-content">
                        <div class="notif-hist-inside">
                            <div class="notif-hist-title">
                                {{ notif.notif_title }}
                            </div>
                            <div class="notif-hist-text">
                                {{ notif.notif_text }}
                            </div>
                        </div>
                        <div class="notif-hist-time">
                            {{ notif.timestamp|date(sakura.date_format) }}
                        </div>
                    </div>
                    <div class="clear"></div>
                </a>
                {% endfor %}
            </div>
            {% if notifs|length > 1 %}
                <div>
                    <div class="pagination" style="float: right;">
                        {% if page.currentPage > 0 %}
                            <a href="/settings/notifications/p{{ page.currentPage }}"><span class="fa fa-step-backward"></span></a>
                        {% endif %}
                        {% for id,npage in notifs %}
                            <a href="/settings/notifications/p{{ id + 1 }}"{% if id == page.currentPage %} class="current"{% endif %}>{{ id + 1 }}</a>
                        {% endfor %}
                        {% if page.currentPage + 1 < notifs|length %}
                            <a href="/settings/notifications/p{{ page.currentPage + 2 }}"><span class="fa fa-step-forward"></span></a>
                        {% endif %}
                    </div>
                    <div class="clear"></div>
                </div>
            {% endif %}
        </div>
        <div class="clear"></div>
    </div>
{% include 'global/footer.tpl' %}
