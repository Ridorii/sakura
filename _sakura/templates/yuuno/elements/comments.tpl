<div class="comments">
    <div class="comment-input-section">
        {% if session.checkLogin %}
            <form action="" method="post">
                <input type="hidden" name="sessid" value="{{ php.sessionid }}" />
                <input type="hidden" name="timestamp" value="{{ php.time }}" />
                <input type="hidden" name="mode" value="comment" />
                <div class="comment">
                    <div class="comment-avatar" style="background-image: url('{{ urls.format('IMAGE_AVATAR', [user.data.id]) }}');"></div>
                    <div class="comment-pointer"></div>
                    <textarea class="comment-text" class="comment" placeholder="Join the conversation..."></textarea>
                    <input class="comment-submit" name="submit" type="submit" value="&#xf1d8;" />
                </div>
            </form>
        {% else %}
            <h1 class="stylised" style="text-align: center;">Log in to comment!</h1>
        {% endif %}
    </div>
    <div class="comments-discussion">
        <ul class="comments-list">
            <li>
                <div class="comment">
                    <div class="comment-avatar" style="background-image: url('{{ urls.format('IMAGE_AVATAR', [user.data.id]) }}');"></div>
                    <div class="comment-pointer"></div>
                    <div class="comment-text">
                        aaaaaaaaaa
                    </div>
                </div>
                <ul>
                    <li>
                        <div class="comment">
                            <div class="comment-avatar" style="background-image: url('{{ urls.format('IMAGE_AVATAR', [1]) }}');"></div>
                            <div class="comment-pointer"></div>
                            <div class="comment-text">
                                aaaaaaaaaa
                            </div>
                        </div>
                        <ul>
                            <li>
                                <div class="comment">
                                    <div class="comment-avatar" style="background-image: url('{{ urls.format('IMAGE_AVATAR', [user.data.id]) }}');"></div>
                                    <div class="comment-pointer"></div>
                                    <div class="comment-text">
                                        aaaaaaaaaa
                                    </div>
                                </div>
                                <ul>
                                    <li>
                                        <div class="comment">
                                            <div class="comment-avatar" style="background-image: url('{{ urls.format('IMAGE_AVATAR', [3]) }}');"></div>
                                            <div class="comment-pointer"></div>
                                            <div class="comment-text">
                                                aaaaaaaaaa
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <div class="comment">
                                    <div class="comment-avatar" style="background-image: url('{{ urls.format('IMAGE_AVATAR', [1]) }}');"></div>
                                    <div class="comment-pointer"></div>
                                    <div class="comment-text">
                                        aaaaaaaaaa
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <div class="comment">
                            <div class="comment-avatar" style="background-image: url('{{ urls.format('IMAGE_AVATAR', [2]) }}');"></div>
                            <div class="comment-pointer"></div>
                            <div class="comment-text">
                                aaaaaaaaaa
                            </div>
                        </div>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</div>
