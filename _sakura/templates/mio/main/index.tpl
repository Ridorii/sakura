{% include 'global/header.tpl' %}
    <div class="mioboards">
        <h3 class="miotitle" style="margin: 0;">Welcome!</h3>
        <br />
        Welcome to Flashii! This is a site for a bunch of friends to hang out, nothing special.<br />Anyone is pretty much welcome to register so why not have a go?
        <br />
        <br />
        <a class="registerbutton" href="//{{ sakura.urls.main }}/register">Register!</a>
        <a class="loginbutton" href="//{{ sakura.urls.main }}/login">Login</a>
    </div>
    <div class="mioblog">
        <h3 class="miotitle" style="margin: 0;">Latest News Posts<span class="windowbutton-container" onclick="hidePageSection('latestnewsposts',1);"><img class="minbutton" src="//{{ sakura.urls.content }}/pixel.png" alt="_"></span></h3>
        <div class="mioboxcontent sub" style="margin: 0;">
            {% for newsPost in newsPosts %}
                {% include 'elements/newsPost.tpl' %}
            {% endfor %}
        </div>
    </div>
    <div class="mioblog">
        <h3 class="miotitle" style="margin: 0;">Statistics<span class="windowbutton-container" onclick="hidePageSection('sitestatistics',1);"><img class="minbutton" src="//{{ sakura.urls.content }}/pixel.png" alt="_"></span></h3>
        <div class="mioboxcontent sub" style="margin: 0;">
            <table class="miotable" style="text-align:center;">
                <tbody>
                    <tr>
                        <td style="width:50%;">We have {{ stats.userCount }} registered users.</td>
                        <td>Our newest member is <a href="/u/{{ stats.newestUser.id }}">{{ stats.newestUser.username }}</a>.</td>
                    </tr>
					<tr>
                        <td>It has been {{ stats.lastRegDate }} since the last user registered.</td>
                        <td>{{ stats.chatOnline }} in chat right now.</td>
                    </tr>
				</tbody>
            </table>
        </div>
    </div>
{% include 'global/footer.tpl' %}
