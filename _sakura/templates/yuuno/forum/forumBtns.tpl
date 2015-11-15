<div class="buttonRow pagination">
    <div class="leftSide">
        <a href="{% if thread %}{{ urls.format('FORUM_SUB', [forum.id]) }}{% else %}{{ urls.format('FORUM_INDEX') }}{% endif %}" class="forumbtn"><span class="fa fa-backward"></span> Back</a>
        {% if thread.id %}
        <a href="{{ urls.format('FORUM_REPLY', [thread.id]) }}" class="forumbtn"><span class="fa fa-reply-all"></span> Reply</a>
        {% endif %}
        {% if forum.id and not thread %}
        <a href="{{ urls.format('FORUM_NEW_THREAD', [forum.id]) }}" class="forumbtn"><span class="fa fa-pencil-square-o"></span> New Thread</a>
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
