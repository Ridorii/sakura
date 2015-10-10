{% set comments = profile.profileComments.comments %}
{% set commentsCategory = 'profile-' ~ profile.data.user_id %}
{% include 'elements/comments.tpl' %}
