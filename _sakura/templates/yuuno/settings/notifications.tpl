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
                {% for notif in notifs %}
                <div id="notif-hist-{{ notif.id }}" class="{% if notif.notif_read %}read{% endif %}">
                    <div class="notif-hist-icon">
                    {% if 'FONT:' in notif.notif_img %}
                        <div class="font-icon fa {{ notif.notif_img|replace({'FONT:': ''}) }} fa-4x"></div>
                    {% else %}
                        <img src="{{ notif.notif_img }}" alt="Notification" />
                    {% endif %}
                    </div>
                    <div class="notif-hist-content">
                        <div class="notif-hist-time">
                            {{ notif.timestamp|date("r") }}
                        </div>
                        <div class="notif-hist-inside">
                            <div class="notif-hist-title">
                                {{ notif.notif_title }}
                            </div>
                            <div class="notif-hist-text">
                                {{ notif.notif_text }}
                                {% if notif.notif_link %}
                                | <a href="{{ notif.notif_link }}" class="default">Go</a>
                                {% endif %}
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
                {% endfor %}
            </div>
        </div>
        <div class="clear"></div>
    </div>
{% include 'global/footer.tpl' %}
