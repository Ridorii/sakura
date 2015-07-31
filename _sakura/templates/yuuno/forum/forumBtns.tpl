<div class="buttonRow pagination">
    <div class="leftSide">
        <a href="/forum/{% if board.viewtopic %}{{ topic.forum_id }}/{% endif %}" class="forumbtn"><span class="fa fa-backward"></span> Back</a>
        {% if board.viewtopic %}
        <a href="/forum/thread/{{ topic.topic_id }}/reply" class="forumbtn"><span class="fa fa-reply-all"></span> Reply</a>
        {% endif %}
        {% if board.viewforum %}
        <a href="/forum/{{ board.forums[0].forum.forum_id }}/new" class="forumbtn"><span class="fa fa-pencil-square-o"></span> New Thread</a>
        {% endif %}
    </div>
    <div class="rightSide">
        <a href="#" class="forumbtn"><span class="fa fa-step-backward"></span></a>
        <a href="#" class="forumbtn">1</a>
        <a href="#" class="forumbtn">2</a>
        <a href="#" class="forumbtn">3</a>
        ...
        <a href="#" class="forumbtn">10</a>
        <a href="#" class="forumbtn"><span class="fa fa-step-forward"></span></a>
    </div>
    <div class="clear"></div>
</div>
