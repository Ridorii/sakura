{% extends 'global/master.tpl' %}

{% block title %}System Information{% endblock %}

{% block content %}
    <div class="main">
        <h1 class="page-header">System information <small>Stuff about the server the site runs on</small></h1>
        <dl class="dl-horizontal">
            <dt>Operating System</dt>
            <dd>{{ uname.osn }}</dd>
            <dt>Version Info</dt>
            <dd>{{ uname.osv }}</dd>
            <dt>System Hostname</dt>
            <dd>{{ uname.host }}</dd>
            <dt>Architecture</dt>
            <dd>{{ uname.arch }}</dd>
            <dt>Uptime</dt>
            <dd>{{ uname.arch }}</dd>
        </dl>
    </div>
{% endblock %}
