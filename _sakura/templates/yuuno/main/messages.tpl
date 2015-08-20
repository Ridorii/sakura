{% include 'global/header.tpl' %}
    <div class="content settings messages">
        <div class="content-right content-column">
            {% include 'elements/settingsNav.tpl' %}
        </div>
        <div class="content-left content-column">
            <div class="head">
                Messages / Inbox
            </div>
            {% if messages|length %}
            <table class="msgTable">
                <thead>
                    <tr>
                        <th>From</th>
                        <th>Subject</th>
                        <th>Sent on</th>
                    </tr>
                </thead>
                <tbody>
                    {% for message in messages %}
                    <tr>
                        <td><a href="/u/{{ message.data.from.user.id }}" class="default" style="font-weight: 700; color: {% if message.data.from.user.name_colour == null %}{{ message.data.from.rank.colour }}{% else %}{{ message.data.from.user.name_colour }}{% endif %};">{{ message.data.from.user.username }}</a></td>
                        <td><a href="/messages/read/{{ message.id }}" class="default">{{ message.subject }}</a></td>
                        <td>{{ message.time|date(sakura.dateFormat) }}</td>
                    </tr>
                    {% endfor %}
                </tbody>
            </table>
            {% else %}
            <h1 class="stylised"style="line-height: 1.8em; text-align: center;">Nothing to view!</h1>
            {% endif %}
            <h3 style="text-align: center;">Click Compose in the menu on the right side to write a new message!</h3>
        </div>
        <div class="clear"></div>
    </div>
{% include 'global/footer.tpl' %}
