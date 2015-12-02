{% set comments = profile.profileComments.comments %}
{% set commentsCategory = 'profile-' ~ profile.id %}
{% include 'elements/comments.tpl' %}
