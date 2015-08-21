<div class="head">
    Navigation
</div>
<div class="right-menu-nav">
    <div>General</div>
    <a href="/settings/">Home</a>
    <a href="/settings/profile">Edit Profile</a>
    <a href="/settings/options">Site Options</a>
    <a href="/settings/groups">Groups</a>
    <div>Friends</div>
    <a href="/settings/friendlisting">List</a>
    <a href="/settings/friendrequests">Requests</a>
    <div>Messages</div>
    <a href="/messages/inbox">Inbox</a>
    <a href="/messages/sent">Sent</a>
    <a href="/messages/compose">Compose</a>
    <div>Notifications</div>
    <a href="/settings/notifications">History</a>
    {% if ((user.data.userData.userPage is defined and user.checkPermission('SITE', 'CHANGE_USERPAGE')) or user.checkPermission('SITE', 'CREATE_USERPAGE')) or user.checkPermission('SITE', 'CHANGE_AVATAR') or ((user.data.userData.userPage is defined and user.checkPermission('SITE', 'CHANGE_USERPAGE')) or user.checkPermission('SITE', 'CREATE_USERPAGE')) %}
    <div>Aesthetics</div>
        {% if user.checkPermission('SITE', 'CHANGE_AVATAR') %}
            <a href="/settings/avatar">Avatar</a>
        {% endif %}
        {% if (user.data.userData.profileBackground is defined and user.checkPermission('SITE', 'CHANGE_BACKGROUND')) or user.checkPermission('SITE', 'CREATE_BACKGROUND') %}
            <a href="/settings/background">Background</a>
        {% endif %}
        {% if (user.data.userData.userPage is defined and user.checkPermission('SITE', 'CHANGE_USERPAGE')) or user.checkPermission('SITE', 'CREATE_USERPAGE') %}
            <a href="/settings/userpage">Userpage</a>
        {% endif %}
    {% endif %}
    <div>Account</div>
    <a href="/settings/email">E-mail Address</a>
    <a href="/settings/username">Username</a>
    <a href="/settings/usertitle">User Title</a>
    <a href="/settings/password">Password</a>
    <a href="/settings/ranks">Ranks</a>
    <div>Danger zone</div>
    <a href="/settings/sessions">Sessions</a>
    <a href="/settings/regkeys">Registration Keys</a>
    <a href="/settings/deactivate">Deactivate Account</a>
</div>
