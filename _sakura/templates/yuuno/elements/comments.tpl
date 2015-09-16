<div class="comments">
    <div class="comment-input-section">
        {% if session.checkLogin %}
            <form action="" method="post" id="commentsForm">
                <input type="hidden" name="sessid" value="{{ php.sessionid }}" />
                <input type="hidden" name="timestamp" value="{{ php.time }}" />
                <input type="hidden" name="mode" value="comment" />
                <div class="comment">
                    <div class="comment-avatar" style="background-image: url('{{ urls.format('IMAGE_AVATAR', [user.data.id]) }}');"></div>
                    <div class="comment-pointer"></div>
                    <textarea class="comment-content" class="comment" placeholder="Join the conversation..."></textarea>
                    <input class="comment-submit" name="submit" type="submit" value="&#xf1d8;" />
                </div>
            </form>
        {% else %}
            <h1 class="stylised" style="text-align: center; padding: 10px 0">Log in to comment!</h1>
        {% endif %}
    </div>
    <div class="comments-discussion">
        <ul class="comments-list">
            <li>
                <div class="comment">
                    <div class="comment-avatar" style="background-image: url('{{ urls.format('IMAGE_AVATAR', [user.data.id]) }}');"></div>
                    <div class="comment-pointer"></div>
                    <div class="comment-content">
                        <div class="comment-controls">
                            <ul>
                                <li>Report</li>
                                <li>Delete</li>
                                <li>Reply</li>
                                <li class="shown"><span class="fa fa-plus-circle"></span> 1</li>
                                <li class="shown"><span class="fa fa-minus-circle"></span> 1</li>
                            </ul>
                            <div class="clear"></div>
                        </div>
                        <div class="comment-text">
                            aaaaaaaaaa
                        </div>
                    </div>
                </div>
                <ul>
                    <li>
                        <div class="comment">
                            <div class="comment-avatar" style="background-image: url('{{ urls.format('IMAGE_AVATAR', [1]) }}');"></div>
                            <div class="comment-pointer"></div>
                            <div class="comment-content">
                                <div class="comment-controls">
                                    <ul>
                                        <li>Report</li>
                                        <li>Delete</li>
                                        <li>Reply</li>
                                        <li class="shown"><span class="fa fa-plus-circle"></span> 1</li>
                                        <li class="shown"><span class="fa fa-minus-circle"></span> 1</li>
                                    </ul>
                                </div>
                                <div class="comment-text">
                                    aaaaaaaaaa
                                </div>
                            </div>
                        </div>
                        <ul>
                            <li>
                                <div class="comment">
                                    <div class="comment-avatar" style="background-image: url('{{ urls.format('IMAGE_AVATAR', [user.data.id]) }}');"></div>
                                    <div class="comment-pointer"></div>
                                    <div class="comment-content">
                                        <div class="comment-controls">
                                            <ul>
                                                <li>Report</li>
                                                <li>Delete</li>
                                                <li>Reply</li>
                                                <li class="shown"><span class="fa fa-plus-circle"></span> 1</li>
                                                <li class="shown"><span class="fa fa-minus-circle"></span> 1</li>
                                            </ul>
                                        </div>
                                        <div class="comment-text">
                                            aaaaaaaaaa
                                        </div>
                                    </div>
                                </div>
                                <ul>
                                    <li>
                                        <div class="comment">
                                            <div class="comment-avatar" style="background-image: url('{{ urls.format('IMAGE_AVATAR', [3]) }}');"></div>
                                            <div class="comment-pointer"></div>
                                            <div class="comment-content">
                                                <div class="comment-controls">
                                                    <ul>
                                                        <li>Report</li>
                                                        <li>Delete</li>
                                                        <li>Reply</li>
                                                        <li class="shown"><span class="fa fa-plus-circle"></span> 1</li>
                                                        <li class="shown"><span class="fa fa-minus-circle"></span> 1</li>
                                                    </ul>
                                                </div>
                                                <div class="comment-text">
                                                    aaaaaaaaaa
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <div class="comment">
                                    <div class="comment-avatar" style="background-image: url('{{ urls.format('IMAGE_AVATAR', [1]) }}');"></div>
                                    <div class="comment-pointer"></div>
                                    <div class="comment-content">
                                        <div class="comment-controls">
                                            <ul>
                                                <li>Report</li>
                                                <li>Delete</li>
                                                <li>Reply</li>
                                                <li class="shown"><span class="fa fa-plus-circle"></span> 1</li>
                                                <li class="shown"><span class="fa fa-minus-circle"></span> 1</li>
                                            </ul>
                                        </div>
                                        <div class="comment-text">
                                            aaaaaaaaaa
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <div class="comment">
                            <div class="comment-avatar" style="background-image: url('{{ urls.format('IMAGE_AVATAR', [2]) }}');"></div>
                            <div class="comment-pointer"></div>
                            <div class="comment-content">
                                <div class="comment-controls">
                                    <ul>
                                        <li>Report</li>
                                        <li>Delete</li>
                                        <li>Reply</li>
                                        <li class="shown"><span class="fa fa-plus-circle"></span> 1</li>
                                        <li class="shown"><span class="fa fa-minus-circle"></span> 1</li>
                                    </ul>
                                </div>
                                <div class="comment-text">
                                    aaaaaaaaaa
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</div>
<script type="text/javascript">
window.addEventListener("load", function() {

    prepareAjaxForm('commentsForm', 'Posting comment...');

});
</script>
