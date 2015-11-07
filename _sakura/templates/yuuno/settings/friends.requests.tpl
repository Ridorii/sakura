{% block js %}
<script type="text/javascript">
window.addEventListener("load", function() {
    var friendsListActions = document.querySelectorAll('[id^="friendslist-friend-action-"]');

    for (var i in friendsListActions) {
        prepareAjaxLink(friendsListActions[i], 'submitPost', ', true, "Please wait.."');
    }
});
</script>
{% endblock %}

{% if friends|length %}
    <div class="friends-list">
        {% for friend in friends[page.currentPage] %}
            <div class="friend-container" id="friend-{{ friend.user.id }}">
                <a class="friends-list-data clean" href="/u/{{ friend.user.id }}">
                    <img src="/a/{{ friend.user.id }}" alt="{{ friend.user.username }}" class="friends-list-avatar default-avatar-setting" style="width: 150px; height: 150px;" />
                    <div class="friends-list-name" style="color: {{ friend.user.colour }};">{{ friend.user.username }}</div>
                </a>
                <div class="friends-list-actions">
                    <a class="add fa fa-check" title="Add friend" href="/friends?add={{ friend.user.id }}&amp;session={{ php.sessionid }}&amp;time={{ php.time }}" id="friendslist-friend-action-add-{{ friend.user.id }}"></a>
                    <a class="remove fa fa-remove" title="Remove friend" href="/friends?remove={{ friend.user.id }}&amp;session={{ php.sessionid }}&amp;time={{ php.time }}" id="friendslist-friend-action-remove-{{ friend.user.id }}"></a>
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
                    <a href="{{ urls.format('SETTING_PAGE', ['friends', 'requests', page.currentPage]) }}"><span class="fa fa-step-backward"></span></a>
                {% endif %}
                {% for id,npage in friends %}
                    <a href="{{ urls.format('SETTING_PAGE', ['friends', 'requests', id + 1]) }}"{% if id == page.currentPage %} class="current"{% endif %}>{{ id + 1 }}</a>
                {% endfor %}
                {% if page.currentPage + 1 < friends|length %}
                    <a href="{{ urls.format('SETTING_PAGE', ['friends', 'requests', page.currentPage + 2]) }}"><span class="fa fa-step-forward"></span></a>
                {% endif %}
            </div>
            <div class="clear"></div>
        </div>
    {% endif %}
{% else %}
    <h1 class="stylised" style="margin: 2em auto; text-align: center;">You don't have any pending requests!</h1>
{% endif %}
