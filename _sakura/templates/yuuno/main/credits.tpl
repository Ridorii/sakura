{% include 'global/header.tpl' %}
    <div class="content standalone markdown">
        <h1>Credits</h1>
        <p>This is the Sakura contributor list.</p>
        <h3>People</h3>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                {% for contribname, contributor in contributors %}
                <tr>
                    <td><a href="{{ contributors[1] }}" target="_blank">{{ contribname }}</a></td>
                    <td>{{ contributor[0] }}</td>
                </tr>
                {% endfor %}
            </tbody>
        </table>
        <h3>Tools</h3>
        <table>
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                {% for thirdName, thirdData in thirdParty %}
                <tr>
                    <td><a href="{{ thirdData[1] }}" target="_blank">{{ thirdName }}</a></td>
                    <td>{{ thirdData[0] }}</td>
                </tr>
                {% endfor %}
            </tbody>
        </table>
    </div>
{% include 'global/footer.tpl' %}
