{% include 'global/header.tpl' %}
    <div class="content support">
        <div class="head">Donation Tracker</div>
        <h1 class="stylised" style="margin: 1em auto; text-align: center;">Our current overall balance is &#8364;{{ page.premiumData.balance|number_format(2) }}</h1>
        <div class="sectionHeader">
            Donation Log
        </div>
        <table>
            <thead>
                <tr>
                    <th>
                        Supporter
                    </th>
                    <th>
                        Amount
                    </th>
                    <th>
                        Action
                    </th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>
                        Supporter
                    </th>
                    <th>
                        Amount
                    </th>
                    <th>
                        Action
                    </th>
                </tr>
            </tfoot>
            <tbody>
                {% for supporter in page.premiumTable[page.currentPage] %}
                    <tr>
                        <td>
                            {{ page.premiumData.users[supporter.uid].data.username }}
                        </td>
                        <td style="color: {% if supporter.amount > 0 %}#0A0{% else %}#A00{% endif %};">
                            &#8364;{{ supporter.amount|number_format(2) }}
                        </td>
                        <td>
                            {{ supporter.comment }}
                        </td>
                    </tr>
                {% endfor %}
            </tbody>
        </table>
        {% if page.premiumTable|length > 1 %}
        <div class="pagination" style="float: right;">
        {% if page.currentPage > 0 %}
            <a href="/support/tracker/{{ page.currentPage }}"><span class="fa fa-step-backward"></span></a>
        {% endif %}
        {% for count,navpage in page.premiumTable %}
            <a href="/support/tracker/{{ count + 1 }}"{% if count == page.currentPage %} class="current"{% endif %}>{{ count + 1 }}</a>
        {% endfor %}
        {% if page.currentPage + 1 < page.premiumTable|length %}
            <a href="/support/tracker/{{ page.currentPage + 2 }}"><span class="fa fa-step-forward"></span></a>
        {% endif %}
        </div>
        <div class="clear"></div>
        {% endif %}
    </div>
{% include 'global/footer.tpl' %}
