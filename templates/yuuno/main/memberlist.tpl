{% extends 'global/master.tpl' %}

{% set rankTitle %}
{% if page.notfound %}Not found{% else %}{% if not page.active %}All members{% else %}{{ page.ranks[page.active].name(true) }}{% endif %}{% endif %}
{% endset %}

{% set rankDescription %}
{% if page.notfound %}The requested rank could not be found!{% else %}{% if not page.active %}The entire user list.{% else %}{{ page.ranks[page.active].description }}{% endif %}{% endif %}
{% endset %}

{% set users = users|batch(membersPerPage) %}

{% set currPage = get.page|default(1) - 1 %}

{% set paginationPages = users %}
{% set paginationUrl %}{% if page.sort and page.active %}{{ urls.format('MEMBERLIST_ALL', [page.sort, page.active]) }}{% elseif page.sort %}{{ urls.format('MEMBERLIST_SORT', [page.sort]) }}{% elseif page.active %}{{ urls.format('MEMBERLIST_RANK', [page.active]) }}{% else %}{{ urls.format('MEMBERLIST_INDEX') }}{% endif %}{% endset %}

{% block title %}{{ rankTitle }}{% endblock %}

{% block content %}
    <div class="headerNotify" style="margin-bottom: 1px;">
        <h1 style="{% if page.active %}text-shadow: 0 0 5px {{ page.ranks[page.active].colour }}; color: {{ page.ranks[page.active].colour }};{% else %}text-shadow: 0 0 5px #555;{% endif %}">{{ rankTitle }}</h1>
        <h3>{{ rankDescription }}</h3>
    </div>
    <div class="membersPage" style="min-height: 500px;">
        <div class="dropDown" style="margin: 0 auto; font-size: 1.5em; line-height: 1.5em; height: 30px;">
            <div class="dropDownInner" style="float: left; color: #FFF;">
                <a class="dropDownDesc">Rank:</a>
                <a href="{% if page.sort %}{{ urls.format('MEMBERLIST_SORT', [page.sort]) }}{% else %}{{ urls.format('MEMBERLIST_INDEX') }}{% endif %}"{% if not page.active %} class="dropDownSelected"{% endif %}>All members</a>
                {% for rank in page.ranks %}
                    {% if not rank.hidden or (rank.hidden and page.active == rank.id) %}
                        <a href="{% if page.sort %}{{ urls.format('MEMBERLIST_ALL', [page.sort, rank.id]) }}{% else %}{{ urls.format('MEMBERLIST_RANK', [rank.id]) }}{% endif %}" style="color: {{ rank.colour }};"{% if page.active == rank.id %} class="dropDownSelected"{% endif %}>{{ rank.name(true) }}</a>
                    {% endif %}
                {% endfor %}
            </div>
            <div class="dropDownInner" style="float: left;">
                <a class="dropDownDesc">View:</a>
                {% for sort in page.sorts %}
                <a href="{% if page.active %}{{ urls.format('MEMBERLIST_ALL', [sort, page.active]) }}{% else %}{{ urls.format('MEMBERLIST_SORT', [sort]) }}{% endif %}"{% if page.sort == sort %} class="dropDownSelected"{% endif %}>{{ sort|capitalize }}</a>
                {% endfor %}
            </div>
        </div>
        {% if not users|length %}
            <h1 class="stylised" style="margin: 2em 0;">This rank has no members!</h1>
        {% elseif not page.notfound %}
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
                    {% for count,user in users[currPage] %}
                    <tbody>
                        <tr>
                            <td>
                                #{{ page.active ? count + 1 : count }}
                            </td>
                            <td>
                                <a href="{{ urls.format('USER_PROFILE', [user.id]) }}" class="default" style="font-weight: bold; color: {{ user.colour }}; text-shadow: 0 0 5px {{ user.colour }};">{{ user.username }}</a>
                            </td>
                            <td title="{{ user.dates.joined|date(sakura.dateFormat) }}">
                                {{ user.elapsed.joined }}
                            </td>
                            <td title="{% if user.dates.lastOnline == 0 %}Never logged in.{% else %}{{ user.dates.lastOnline|date(sakura.dateFormat) }}{% endif %}">
                                {% if user.dates.lastOnline == 0 %}<i>Never logged in.</i>{% else %}{{ user.elapsed.lastOnline }}{% endif %}
                            </td>
                            <td>
                                {{ user.userTitle }}
                            </td>
                            <td>
                                <img src="{{ sakura.contentPath }}/images/flags/{{ user.country.short|lower }}.png" alt="{% if user.country.short|lower == 'xx' %}?{% else %}{{ user.country.long }}{% endif %}" title="{% if user.country.short|lower == 'xx' %}Unknown{% else %}{{ user.country.long }}{% endif %}" />
                            </td>
                        </tr>
                    </tbody>
                    {% endfor %}
                </table>
                {% else %}
                    {% for user in users[currPage] %}
                        <a href="{{ urls.format('USER_PROFILE', [user.id]) }}">{# These comment tags are here to prevent the link extending too far
                            #}<div class="userBox" id="u{{ user.id }}">{#
                                #}<img src="{{ sakura.contentPath }}/pixel.png" alt="{{ user.username }}"  style="background: url('{{ urls.format('IMAGE_AVATAR', [user.id]) }}') no-repeat center / contain;" />{#
                                #}<span class="userBoxUserName" style="color: {{ user.colour }};">{#
                                    #}{{ user.username }}{#
                                #}</span>{#
                            #}</div>{#
                        #}</a>
                    {% endfor %}
                {% endif %}
            </div>
        {% endif %}
        {% if users|length > 1 %}
            {% include 'elements/pagination.tpl' %}
        {% endif %}
    </div>
{% endblock %}
