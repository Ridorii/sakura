<div id="comments">
    <div class="comment-input-section">
        {% if session.checkLogin %}
            <form action="{{ urls.format('COMMENT_POST') }}" method="post" id="commentsForm">
                <input type="hidden" name="session" value="{{ php.sessionid }}" />
                <input type="hidden" name="category" value="" />
                <input type="hidden" name="replyto" value="0" />
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
            {% if post.comments.comments %}
                {% for comment in post.comments.comments %}
                    {% include 'elements/comment.tpl' %}
                {% endfor %}
            {% else %}
                <h1 class="stylised" style="text-align: center; padding: 10px 0">There are no comments yet!</h1>
            {% endif %}
        </ul>
    </div>
</div>
<script type="text/javascript">
window.addEventListener("load", function() {

    prepareAjaxForm('commentsForm', 'Posting comment...');

});
</script>
