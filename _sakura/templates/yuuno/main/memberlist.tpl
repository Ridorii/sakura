{% include 'global/header.tpl' %}
    {% if user.checklogin %}
    <div class="membersPage" style="min-height: 500px;">
        <h1 style="text-shadow: 0px 0px 5px #555;{% if page.active %} color: {{ page.ranks[page.active].colour }};{% endif %}">{% if not page.active %}All members{% else %}{{ page.ranks[page.active].name }}{% if page.ranks[page.active].multi %}s{% endif %}{% endif %}</h1>
        <h3 style="padding: 0px 0px 10px;">{% if not page.active %}The entire user list.{% else %}{{ page.ranks[page.active].description }}{% endif %}</h3>
        <div class="dropDown" style="margin: 0px auto; font-size: 1.5em; line-height: 1.5em; height: 30px;">
            <div class="dropDownInner" style="float: left; color: #FFF;">
                <a class="dropDownDesc">Rank:</a>
                <a href="/members/"{% if not page.active %} class="dropDownSelected"{% endif %}>All members</a>
                {% for rank in page.ranks %}
                <a href="/members/{% if page.sort != page.sorts[0] %}{{ page.sort }}/{% endif %}{{ rank.id }}/" style="color: {{ rank.colour }};"{% if page.active == rank.id %} class="dropDownSelected"{% endif %}>{{ rank.name }}{% if rank.multi %}s{% endif %}</a>
                {% endfor %}
            </div>
            <div class="dropDownInner" style="float: left;">
                <a class="dropDownDesc">View:</a>
                {% for sort in page.sorts %}
                <a href="/members/{{ sort }}/{% if page.active %}{{ page.active }}/{% endif %}{% if page.page %}p{{ page.page + 1 }}/{% endif %}"{% if page.sort == sort %} class="dropDownSelected"{% endif %}>{{ sort|capitalize }}</a>
                {% endfor %}
            </div>
        </div>
        {% if page.notfound %}
        <h1 class="stylised" style="margin-top: 20px;">The requested rank was not found!</h1>
        {% else %}
        <div class="membersPageList {{ page.sort }}">
            {% if page.sort == page.sorts[2] %}
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Username</th>
                        <th>Registered</th>
                        <th>Last online</th>
                        <th>User title</th>
                        <th>Country</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th>No.</th>
                        <th>Username</th>
                        <th>Registered</th>
                        <th>Last online</th>
                        <th>User title</th>
                        <th>Country</th>
                    </tr>
                </tfoot>
                {% for count,user in page.users[page.page] %}
                <tbody>
                    <tr>
                        <td>
                            #{{ count + 1 }}
                        </td>
                        <td>
                            <a href="/u/{{ user.id }}" class="default" style="font-weight: bold; color: {{ page.ranks[user.rank_main].colour }};">{{ user.username }}</a>
                        </td>
                        <td>
                            {{ user.regdate|date(sakura.date_format) }}
                        </td>
                        <td>
                            {% if user.lastdate == 0 %}<i>Never logged in.</i>{% else %}{{ user.lastdate|date(sakura.date_format) }}{% endif %}
                        </td>
                        <td>
                            {% if not user.usertitle %}<i>{{ page.ranks[user.rank_main].title }}</i>{% else %}{{ user.usertitle }}{% endif %}
                        </td>
                        <td>
                            <img src="//{{ sakura.urls.content }}/images/flags/{% if user.country|lower == 'eu' %}europeanunion{% else %}{{ user.country|lower }}{% endif %}.png" alt="{% if user.country|lower == 'eu' %}?{% else %}{{ user.country }}{% endif %}" />
                        </td>
                    </tr>
                </tbody>
                {% endfor %}
            </table>
            {% else %}
                {% for user in page.users[page.page] %}
                    <a href="/u/{{ user.id }}">{# These comment tags are here to prevent the link extending too far
                        #}<div class="userBox" id="u{{ user.id }}">{#
                            #}<img src="//{{ sakura.urls.content }}/pixel.png" alt="{{ user.username }}"  style="background: url('/a/{{ user.id }}') no-repeat center / contain;" />{#
                            #}<span class="userBoxUserName"{% if page.sort == page.sorts[1] %} style="color: {{ page.ranks[user.rank_main].colour }};"{% endif %}>{#
                                #}{{ user.username }}{#
                            #}</span>{#
                        #}</div>{#
                    #}</a>
                {% endfor %}
            {% endif %}
        </div>
        {% endif %}
        {% if page.users|length > 1 %}
        <div class="pagination">
        {% if page.page > 0 %}
            <a href="/members/{% if page.sort != page.sorts[0] %}{{ page.sort }}/{% endif %}{% if page.active %}{{ page.active }}/{% endif %}p{{ page.page }}"><span class="fa fa-step-backward"></span></a>
        {% endif %}
        {% for count,navpage in page.users %}
            <a href="/members/{% if page.sort != page.sorts[0] %}{{ page.sort }}/{% endif %}{% if page.active %}{{ page.active }}/{% endif %}p{{ count + 1 }}"{% if count == page.page %} class="current"{% endif %}>{{ count + 1 }}</a>
        {% endfor %}
        {% if page.page + 1 < page.users|length %}
            <a href="/members/{% if page.sort != page.sorts[0] %}{{ page.sort }}/{% endif %}{% if page.active %}{{ page.active }}/{% endif %}p{{ page.page + 2 }}"><span class="fa fa-step-forward"></span></a>
        {% endif %}
        </div>
        {% endif %}
    </div>
    {% else %}
        {% include 'elements/restricted.tpl' %}
    {% endif %}
{% include 'global/footer.tpl' %}
