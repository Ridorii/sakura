            <li id="comment-{{ comment.comment_id }}">
                <div class="comment">
                    <a class="comment-avatar clean" href="{{ urls.format('USER_PROFILE', [comment.comment_poster.data.user_id]) }}" style="background-image: url('{{ urls.format('IMAGE_AVATAR', [comment.comment_poster.data.user_id]) }}');"><span style="color: {{ comment.comment_poster.colour }};">{{ comment.comment_poster.data.username }}</span></a>
                    <div class="comment-pointer"></div>
                    <div class="comment-content">
                        <div class="comment-controls">
                            <ul>
                                {% if comment.comment_poster.data.user_id == user.data.user_id %}
                                    <li><a href="{{ urls.format('COMMENT_DELETE', [comment.comment_id, comment.comment_category, php.sessionid])}}" class="clean fa fa-trash-o comment-deletion-link" title="Delete" id="comment-action-delete-{{ comment.comment_id }}"></a></li>
                                {% else %}
                                    <li><a href="{{ urls.format('USER_REPORT', [comment.comment_poster.data.user_id]) }}" class="clean fa fa-exclamation-circle" title="Report" id="comment-action-report-{{ comment.comment_id }}"></a></li>
                                {% endif %}
                                <li><a href="javascript:void(0);" onclick="commentReply({{ comment.comment_id }}, '{{ php.sessionid }}', '{{ comment.comment_category }}', '{{ urls.format('COMMENT_POST') }}', '{{ urls.format('IMAGE_AVATAR', [user.data.user_id]) }}');" class="clean fa fa-reply" title="Reply" id="comment-action-reply-{{ comment.comment_id }}"></a></li>
                                <li class="shown voting like"><a href="{{ urls.format('COMMENT_VOTE', [comment.comment_id, 1, comment.comment_category, php.sessionid])}}" class="clean comment-like-link" id="comment-action-like-{{ comment.comment_id }}"><span class="fa fa-thumbs-up"></span> <span id="comment-{{ comment.comment_id }}-likes">{{ comment.comment_likes }}</span></a></li>
                                <li class="shown voting dislike"><a id="comment-action-dislike-{{ comment.comment_id }}" href="{{ urls.format('COMMENT_VOTE', [comment.comment_id, 0, comment.comment_category, php.sessionid])}}" class="clean comment-dislike-link"><span class="fa fa-thumbs-down"></span> <span id="comment-{{ comment.comment_id }}-dislikes">{{ comment.comment_dislikes }}</span></a></li>
                            </ul>
                            <div class="clear"></div>
                        </div>
                        <div class="comment-text">
                            {{ comment.comment_text|raw|nl2br }}
                        </div>
                    </div>
                </div>
                <ul>
                    {% for comment in comment.comment_replies %}
                        {% include 'elements/comment.tpl' %}
                    {% endfor %}
                </ul>
            </li>
