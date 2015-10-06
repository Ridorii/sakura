{% set comments = profile.profileComments.comments %}
{% set commentsCategory = 'profile-' ~ profile.data.id %}
{% include 'elements/comments.tpl' %}
