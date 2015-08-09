{% include 'global/header.tpl' %}
    <div class="content settings">
        <div class="content-right content-column">
            {% include 'elements/settingsNav.tpl' %}
        </div>
        <div class="content-left content-column">
            <div class="head">
                {{ page.title }}
            </div>
            <div class="settings-explanation">
                <div>Manage your friends.</div>
            </div>
            {% if friends|length %}
                <div class="friends-list">
                    {% for friend in friends[page.currentPage] %}
                        <div class="friend-container" id="friend-{{ friend.user.id }}">
                            <a class="friends-list-data clean" href="/u/{{ friend.user.id }}">
                                <img src="/a/{{ friend.user.id }}" alt="{{ friend.user.username }}" class="friends-list-avatar default-avatar-setting" style="width: 150px; height: 150px;" />
                                <div class="friends-list-name" style="color: {% if friend.user.name_colour %}{{ friend.user.name_colour }}{% else %}{{ friend.rank.colour }}{% endif %};">{{ friend.user.username }}</div>
                            </a>
                            <div class="friends-list-actions">
                                <a class="remove fill fa fa-remove" title="Remove friend" href="/friends?remove={{ friend.user.id }}&amp;session={{ php.sessionid }}&amp;time={{ php.time }}&amp;redirect=/settings/friends&amp;direct=true"></a>
                                <div class="clear"></div>
                            </div>
                        </div>
                    {% endfor %}
                    <div class="clear"></div>
                </div>
                {% if friends|length > 1 %}
                    <div>
                        <div class="pagination" style="float: right;">
                            {% if page.currentPage > 0 %}
                                <a href="/settings/friends/p{{ page.currentPage }}"><span class="fa fa-step-backward"></span></a>
                            {% endif %}
                            {% for id,npage in friends %}
                                <a href="/settings/friends/p{{ id + 1 }}"{% if id == page.currentPage %} class="current"{% endif %}>{{ id + 1 }}</a>
                            {% endfor %}
                            {% if page.currentPage + 1 < friends|length %}
                                <a href="/settings/friends/p{{ page.currentPage + 2 }}"><span class="fa fa-step-forward"></span></a>
                            {% endif %}
                        </div>
                        <div class="clear"></div>
                    </div>
                {% endif %}
            {% else %}
                <h1 class="stylised" style="margin: 2em auto; text-align: center;">You don't have any friends yet!</h1>
            {% endif %}
        </div>
        <div class="clear"></div>
    </div>
{% include 'global/footer.tpl' %}
