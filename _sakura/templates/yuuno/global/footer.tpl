                <a id="gotop" class="fa fa-angle-double-up hidden" href="#top"></a>
                {% if not sakura.versionInfo.stable %}
                    <div style="background: repeating-linear-gradient(-45deg, #000, #000 10px, #FF0 10px, #FF0 20px); text-align: center; color: #FFF; box-shadow: 0px 0px 1em #FF0;">
                        <div style="background: linear-gradient(90deg, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, .9) 10%, rgba(0, 0, 0, .9) 90%, rgba(0, 0, 0, 0) 100%); display: inline-block; padding: 0 40px;">
                            <h3><a style="color: inherit; text-decoration: none;" href="{{ urls.format('CHANGELOG') }}#r{{ sakura.versionInfo.version }}" target="_blank">Sakura Revision {{ sakura.versionInfo.version }} Development</a></h1>
                        </div>
                    </div>
                {% endif %}
            </div>
            <div class="footer">
                <div class="ftsections">
                    <div class="copycentre">&copy; 2013-2015 <a href="//flash.moe/" target="_blank">Flashwave</a>, et al.</div>
                    <ul class="ftsection">
                        <li class="fthead">General</li>
                        <li><a href="{{ urls.format('SITE_HOME') }}" title="Flashii Frontpage">Home</a></li>
                        <li><a href="{{ urls.format('SITE_NEWS') }}" title="Flashii News &amp; Updates">News</a></li>
                        <li><a href="{{ urls.format('SITE_SEARCH') }}" title="Do full-site search requests">Search</a></li>
                        <li><a href="{{ urls.format('INFO_PAGE', ['contact']) }}" title="Contact our Staff">Contact</a></li>
                        <li><a href="{{ urls.format('CHANGELOG') }}" title="All the changes made to Sakura are listed here">Changelog</a></li>
                        <li><a href="{{ urls.format('SITE_PREMIUM') }}" title="Get Tenshi and help us pay the bills">Support us</a></li>
                    </ul>
                    <ul class="ftsection">
                        <li class="fthead">Community</li>
                        <li><a href="{{ urls.format('FORUM_INDEX') }}" title="Read and post on our forums">Forums</a></li>
                        <li><a href="https://twitter.com/_flashii" target="_blank" title="Follow us on Twitter for news messages that are too short for the news page">Twitter</a></li>
                        <li><a href="https://youtube.com/user/flashiinet" target="_blank" title="Our YouTube page where stuff barely ever gets uploaded, mainly used to archive community creations">YouTube</a></li>
                        <li><a href="https://steamcommunity.com/groups/flashiinet" target="_blank" title="Our Steam group, play games with other members on the site">Steam</a></li>
                        <li><a href="https://bitbucket.org/circlestorm" target="_blank" title="Our Open Source repository thing">BitBucket</a></li>
                    </ul>
                    <ul class="ftsection">
                        <li class="fthead">Information</li>
                        <li><a href="{{ urls.format('SITE_FAQ') }}" title="Questions that get Asked Frequently but not actually">FAQ</a></li>
                        <li><a href="{{ urls.format('INFO_PAGE', ['rules']) }}" title="Some Rules and Information kind of summing up the ToS">Rules</a></li>
                        <li><a href="//fiistat.us" target="_blank" title="Check the status on our Servers and related services">Server Status</a></li>
                        <li><a href="{{ urls.format('INFO_PAGE', ['terms']) }}" title="Our Terms of Service">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </body>
</html>
