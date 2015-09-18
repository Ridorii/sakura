            <li id="comment-{{ comment.comment_id }}">
                <div class="comment">
                    <a class="comment-avatar clean" href="{{ urls.format('USER_PROFILE', [comment.comment_poster.data.id]) }}" style="background-image: url('{{ urls.format('IMAGE_AVATAR', [comment.comment_poster.data.id]) }}');"><span style="color: {{ comment.comment_poster.colour }};">{{ comment.comment_poster.data.username }}</span></a>
                    <div class="comment-pointer"></div>
                    <div class="comment-content">
                        <div class="comment-controls">
                            <ul>
                                <li><a href="{{ urls.format('USER_REPORT', [comment.comment_poster.data.id]) }}" class="underline">Report</a></li>
                                <li><a href="{{ urls.format('COMMENT_DELETE', [comment.comment_id, php.sessionid])}}" class="underline">Delete</a></li>
                                <li><a href="javascript:void(0);" onclick="commentReply({{ comment.comment_id }});" class="underline">Reply</a></li>
                                <li class="shown voting like"><a href="{{ urls.format('COMMENT_LIKE', [comment.comment_id, php.sessionid])}}" class="clean"><span class="fa fa-thumbs-up"></span> {{ comment.comment_likes }}</a></li>
                                <li class="shown voting dislike"><a href="{{ urls.format('COMMENT_DISLIKE', [comment.comment_id, php.sessionid])}}" class="clean"><span class="fa fa-thumbs-down"></span> {{ comment.comment_dislikes }}</a></li>
                            </ul>
                            <div class="clear"></div>
                        </div>
                        <div class="comment-text">
                            {{ comment.comment_text|nl2br }}
                        </div>
                    </div>
                </div>
                {% if comment.comment_replies %}
                    <ul>
                        {% for comment in comment.comment_replies %}
                            {% include 'elements/comment.tpl' %}
                        {% endfor %}
                    </ul>
                {% endif %}
            </li>
