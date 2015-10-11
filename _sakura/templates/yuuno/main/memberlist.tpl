{% extends 'global/master.tpl' %}

{% set rankTitle %}
{% if page.notfound %}Not found{% else %}{% if not page.active %}All members{% else %}{{ page.ranks[page.active].rank_name }}{{ page.ranks[page.active].rank_multiple }}{% endif %}{% endif %}
{% endset %}

{% set rankDescription %}
{% if page.notfound %}The requested rank could not be found!{% else %}{% if not page.active %}The entire user list.{% else %}{{ page.ranks[page.active].rank_description }}{% endif %}{% endif %}
{% endset %}

{% block title %}{{ rankTitle }}{% endblock %}

{% block content %}
    <div class="headerNotify" style="margin-bottom: 1px;">
        <h1 style="text-shadow: 0px 0px 5px #555;{% if page.active %} color: {{ page.ranks[page.active].rank_colour }};{% endif %}">{{ rankTitle }}</h1>
        <h3>{{ rankDescription }}</h3>
    </div>
    <div class="membersPage" style="min-height: 500px;">
        <div class="dropDown" style="margin: 0px auto; font-size: 1.5em; line-height: 1.5em; height: 30px;">
            <div class="dropDownInner" style="float: left; color: #FFF;">
                <a class="dropDownDesc">Rank:</a>
                <a href="{% if page.page and page.sort %}{{ urls.format('MEMBERLIST_SORT_PAGE', [page.sort, (page.page + 1)]) }}{% elseif page.sort %}{{ urls.format('MEMBERLIST_SORT', [page.sort]) }}{% elseif page.page %}{{ urls.format('MEMBERLIST_PAGE', [(page.page + 1)]) }}{% else %}{{ urls.format('MEMBERLIST_INDEX') }}{% endif %}"{% if not page.active %} class="dropDownSelected"{% endif %}>All members</a>
                {% for rank in page.ranks %}
                    {% if not rank.rank_hidden or (rank.rank_hidden and page.active == rank.rank_id) %}
                        <a href="{% if page.sort %}{{ urls.format('MEMBERLIST_SORT_RANK', [page.sort, rank.rank_id]) }}{% else %}{{ urls.format('MEMBERLIST_RANK', [rank.rank_id]) }}{% endif %}" style="color: {{ rank.rank_colour }};"{% if page.active == rank.rank_id %} class="dropDownSelected"{% endif %}>{{ rank.rank_name }}{{ rank.rank_multiple }}</a>
                    {% endif %}
                {% endfor %}
            </div>
            <div class="dropDownInner" style="float: left;">
                <a class="dropDownDesc">View:</a>
                {% for sort in page.sorts %}
                <a href="{% if page.active and page.page %}{{ urls.format('MEMBERLIST_ALL', [sort, page.active, (page.page + 1)]) }}{% elseif page.active %}{{ urls.format('MEMBERLIST_SORT_RANK', [sort, page.active]) }}{% elseif page.page %}{{ urls.format('MEMBERLIST_SORT_PAGE', [sort, (page.page + 1)]) }}{% else %}{{ urls.format('MEMBERLIST_SORT', [sort]) }}{% endif %}"{% if page.sort == sort %} class="dropDownSelected"{% endif %}>{{ sort|capitalize }}</a>
                {% endfor %}
            </div>
        </div>
        {% if not page.users|length %}
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
                    {% for count,user in page.users[page.page] %}
                    <tbody>
                        <tr>
                            <td>
                                #{{ page.active ? count + 1 : count }}
                            </td>
                            <td>
                                <a href="{{ urls.format('USER_PROFILE', [user.user_id]) }}" class="default" style="font-weight: bold; color: {{ page.ranks[user.rank_main].rank_colour }};">{{ user.username }}</a>
                            </td>
                            <td>
                                {{ user.regdate|date(sakura.dateFormat) }}
                            </td>
                            <td>
                                {% if user.user_last_online == 0 %}<i>Never logged in.</i>{% else %}{{ user.user_last_online|date(sakura.dateFormat) }}{% endif %}
                            </td>
                            <td>
                                {% if not user.user_title %}<i>{{ page.ranks[user.rank_main].rank_title }}</i>{% else %}{{ user.user_title }}{% endif %}
                            </td>
                            <td>
                                <img src="{{ sakura.contentPath }}/images/flags/{{ user.user_country|lower }}.png" alt="{% if user.user_country|lower == 'eu' %}?{% else %}{{ user.user_country }}{% endif %}" />
                            </td>
                        </tr>
                    </tbody>
                    {% endfor %}
                </table>
                {% else %}
                    {% for user in page.users[page.page] %}
                        <a href="{{ urls.format('USER_PROFILE', [user.user_id]) }}">{# These comment tags are here to prevent the link extending too far
                            #}<div class="userBox" id="u{{ user.user_id }}">{#
                                #}<img src="{{ sakura.contentPath }}/pixel.png" alt="{{ user.username }}"  style="background: url('{{ urls.format('IMAGE_AVATAR', [user.user_id]) }}') no-repeat center / contain;" />{#
                                #}<span class="userBoxUserName"{% if page.sort == page.sorts[1] %} style="color: {{ page.ranks[user.rank_main].rank_colour }};"{% endif %}>{#
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
                    <a href="{% if page.sort and page.active %}{{ urls.format('MEMBERLIST_ALL', [page.sort, page.active, page.page]) }}{% elseif page.sort %}{{ urls.format('MEMBERLIST_SORT_PAGE', [page.sort, page.page]) }}{% elseif page.active %}{{ urls.format('MEMBERLIST_RANK_PAGE', [page.active, page.page]) }}{% else %}{{ urls.format('MEMBERLIST_PAGE', [page.page]) }}{% endif %}"><span class="fa fa-step-backward"></span></a>
                {% endif %}
                {% for count,navpage in page.users %}
                    <a href="{% if page.sort and page.active %}{{ urls.format('MEMBERLIST_ALL', [page.sort, page.active, (count + 1)]) }}{% elseif page.sort %}{{ urls.format('MEMBERLIST_SORT_PAGE', [page.sort, (count + 1)]) }}{% elseif page.active %}{{ urls.format('MEMBERLIST_RANK_PAGE', [page.active, (count + 1)]) }}{% else %}{{ urls.format('MEMBERLIST_PAGE', [(count + 1)]) }}{% endif %}"{% if count == page.page %} class="current"{% endif %}>{{ count + 1 }}</a>
                {% endfor %}
                {% if page.page + 1 < page.users|length %}
                    <a href="{% if page.sort and page.active %}{{ urls.format('MEMBERLIST_ALL', [page.sort, page.active, (page.page + 2)]) }}{% elseif page.sort %}{{ urls.format('MEMBERLIST_SORT_PAGE', [page.sort, (page.page + 2)]) }}{% elseif page.active %}{{ urls.format('MEMBERLIST_RANK_PAGE', [page.active, (page.page + 2)]) }}{% else %}{{ urls.format('MEMBERLIST_PAGE', [(page.page + 2)]) }}{% endif %}"><span class="fa fa-step-forward"></span></a>
                {% endif %}
            </div>
        {% endif %}
    </div>
{% endblock %}
