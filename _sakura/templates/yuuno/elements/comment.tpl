            <li id="comment-{{ comment.comment_id }}">
                <div class="comment">
                    <a class="comment-avatar clean" href="{{ urls.format('USER_PROFILE', [comment.comment_poster.data.id]) }}" style="background-image: url('{{ urls.format('IMAGE_AVATAR', [comment.comment_poster.data.id]) }}');"><span style="color: {{ comment.comment_poster.colour }};">{{ comment.comment_poster.data.username }}</span></a>
                    <div class="comment-pointer"></div>
                    <div class="comment-content">
                        <div class="comment-controls">
                            <ul>
                                {% if comment.comment_poster.data.id == user.data.id %}
                                    <li><a href="{{ urls.format('COMMENT_DELETE', [comment.comment_id, comment.comment_category, php.sessionid])}}" class="clean fa fa-trash-o comment-deletion-link" title="Delete" id="comment-delete-{{ comment.comment_id }}"></a></li>
                                {% else %}
                                    <li><a href="{{ urls.format('USER_REPORT', [comment.comment_poster.data.id]) }}" class="clean fa fa-exclamation-circle" title="Report" id="comment-report-{{ comment.comment_id }}"></a></li>
                                {% endif %}
                                <li><a href="javascript:void(0);" onclick="commentReply({{ comment.comment_id }}, '{{ php.sessionid }}', '{{ comment.comment_category }}', '{{ urls.format('COMMENT_POST') }}', '{{ urls.format('IMAGE_AVATAR', [user.data.id]) }}');" class="clean fa fa-reply" title="Reply" id="comment-reply-{{ comment.comment_id }}"></a></li>
                                <li class="shown voting like"><a href="{{ urls.format('COMMENT_LIKE', [comment.comment_id, php.sessionid])}}" class="clean" id="comment-link-{{ comment.comment_id }}"><span class="fa fa-thumbs-up"></span> {{ comment.comment_likes }}</a></li>
                                <li class="shown voting dislike" id="comment-dislike-{{ comment.comment_id }}"><a href="{{ urls.format('COMMENT_DISLIKE', [comment.comment_id, php.sessionid])}}" class="clean"><span class="fa fa-thumbs-down"></span> {{ comment.comment_dislikes }}</a></li>
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
