<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

// this is based on what is in the live flashii table at the
// moment this migration was created to avoid merge conflicts.

class BaseTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('actioncodes', function (Blueprint $table) {
            $table->string('code_action', 255)
                ->comment('Action identifier so the backend knows what to do.');

            $table->bigInteger('user_id', 255)
                ->unsigned()
                ->comment('ID of the user that would be affected by this action');

            $table->string('action_code', 255)
                ->comment('The URL key for using this code.');
        });

        Schema::create('apikeys', function (Blueprint $table) {
            $table->bigIncrements('id', 128)
                ->unsigned()
                ->comment('Automatically generated ID by MySQL for management.');

            $table->bigInteger('owner', 128)
                ->unsigned()
                ->comment('ID of user that owns this API key.');

            $table->string('apikey', 32)
                ->comment('The API key.');
        });

        Schema::create('bans', function (Blueprint $table) {
            $table->bigIncrements('ban_id', 255)
                ->unsigned()
                ->comment('Automatically generated ID by MySQL for management.');

            $table->bigInteger('user_id', 255)
                ->unsigned()
                ->comment('ID of user that was banned, 0 for just an IP ban.');

            $table->integer('ban_begin', 11)
                ->unsigned()
                ->comment('Timestamp when the user was banned.');

            $table->integer('ban_end', 11)
                ->unsigned()
                ->comment('Timestamp when the user should regain access to the site.');

            $table->string('ban_reason', 512)
                ->nullable()
                ->default(null)
                ->comment('Reason given for the ban.');

            $table->bigInteger('ban_moderator', 255)
                ->unsigned()
                ->comment('ID of moderator that banned this user,');
        });

        Schema::create('comment_votes', function (Blueprint $table) {
            $table->bigInteger('vote_comment', 255)
                ->unsigned()
                ->comment('ID of the comment that was voted on.');

            $table->bigInteger('vote_user', 255)
                ->unsigned()
                ->comment('ID of the voter.');

            $table->tinyInteger('vote_state', 1)
                ->unsigned()
                ->comment('0 = dislike, 1 = like.');
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->bigIncrements('comment_id', 255)
                ->unsigned()
                ->comment('MySQL Generated ID used for sorting.');

            $table->string('comment_category', 32)
                ->comment('Comment category.');

            $table->integer('comment_timestamp', 11)
                ->unsigned()
                ->comment('Timestamp of when this comment was posted.');

            $table->bigInteger('comment_poster', 255)
                ->unsigned()
                ->comment('User ID of the poster.');

            $table->bigInteger('comment_reply_to', 255)
                ->unsigned()
                ->default(0)
                ->comment('ID of the comment this comment is a reply to');

            $table->text('comment_text', 255)
                ->comment('Content of the comment.');
        });

        Schema::create('config', function (Blueprint $table) {
            $table->string('config_name', 255)
                ->unique()
                ->comment('Array key for configuration value');

            $table->string('config_value', 255)
                ->comment('The value, obviously.');
        });

        Schema::create('emoticons', function (Blueprint $table) {
            $table->string('emote_string', 255)
                ->comment('String to catch and replace');

            $table->string('emote_path', 255)
                ->comment('Path to the image file relative to the content domain.');
        });

        Schema::create('error_log', function (Blueprint $table) {
            $table->string('error_id', 32)
                ->comment('An ID that is created when an error occurs.');

            $table->string('error_timestamp', 128)
                ->comment('A datestring from when the error occurred.');

            $table->integer('error_revision', 16)
                ->unsigned()
                ->comment('Sakura Revision number.');

            $table->integer('error_type', 16)
                ->unsigned()
                ->comment('The PHP error type of this error.');

            $table->integer('error_line', 32)
                ->unsigned()
                ->comment('The line that caused this error.');

            $table->string('error_string', 512)
                ->comment("PHP's description of this error.");

            $table->string('error_file', 512)
                ->comment('The file in which this error occurred.');

            $table->text('error_backtrace')
                ->comment('A full base64 and json encoded backtrace containing all environment data.');
        });

        Schema::create('faq', function (Blueprint $table) {
            $table->bigIncrements('faq_id', 128)
                ->unsigned()
                ->comment('MySQL Generated ID used for sorting.');

            $table->string('faq_shorthand', 255)
                ->comment('Used for linking directly to a question.');

            $table->string('faq_question', 255)
                ->comment('The question.');

            $table->text('faq_answer')
                ->comment('The answer.');
        });

        Schema::create('forum_permissions', function (Blueprint $table) {
            $table->bigInteger('forum_id', 255)
                ->unsigned()
                ->comment('Forum ID');

            $table->bigInteger('rank_id', 128)
                ->unsigned()
                ->comment('Rank ID, leave 0 for a user');

            $table->bigInteger('user_id', 255)
                ->unsigned()
                ->comment('User ID, leave 0 for a rank');

            $table->string('forum_perms', 255)
                ->comment('Forum action permission string');
        });

        Schema::create('forums', function (Blueprint $table) {
            $table->bigIncrements('forum_id', 255)
                ->unsigned()
                ->comment('MySQL Generated ID used for sorting.');

            $table->bigInteger('forum_order', 255)
                ->unsigned()
                ->comment('Forum sorting order.');

            $table->string('forum_name', 255)
                ->comment('Display name of the forum.');

            $table->text('forum_desc')
                ->comment('Description of the forum.');

            $table->string('forum_link', 255)
                ->comment('If set forum will display as a link.');

            $table->bigInteger('forum_category', 255)
                ->unsigned()
                ->default(0)
                ->comment('ID of the category this forum falls under.');

            $table->tinyInteger('forum_type', 4)
                ->unsigned()
                ->default(0)
                ->comment('Forum type, 0 for regular board, 1 for category and 2 for link.');

            $table->string('forum_icon', 255)
                ->comment('Display icon for the forum.');
        });

        Schema::create('friends', function (Blueprint $table) {
            $table->bigInteger('user_id', 255)
                ->unsigned()
                ->comment('ID of the user that added the friend.');

            $table->bigInteger('friend_id', 255)
                ->unsigned()
                ->comment('ID of the user that was added as a friend.');

            $table->integer('friend_timestamp', 11)
                ->unsigned()
                ->comment('Timestamp of action.');
        });

        Schema::create('infopages', function (Blueprint $table) {
            $table->string('page_shorthand', 255)
                ->comment('Name used for calling this page up in the /r/URL');

            $table->string('page_title', 255)
                ->comment('Title displayed on the top of the page');

            $table->text('page_content')
                ->comment('Content of the page');
        });

        Schema::create('login_attempts', function (Blueprint $table) {
            $table->bigIncrements('attempt_id', 255)
                ->unsigned()
                ->comment('MySQL Generated ID used for sorting.');

            $table->tinyInteger('attempt_success', 1)
                ->unsigned()
                ->comment('Success boolean.');

            $table->integer('attempt_timestamp', 11)
                ->unsigned()
                ->comment('Unix timestamp of the event.');

            $table->string('attempt_ip', 255)
                ->comment('IP that made this attempt.');

            $table->bigInteger('user_id', 255)
                ->unsigned()
                ->comment('ID of the user that was attempted to log in to.');
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->bigIncrements('id', 128)
                ->unsigned()
                ->comment('Automatically generated ID by MySQL for management.');

            $table->bigInteger('from_user', 255)
                ->unsigned()
                ->comment('ID of the user that sent this message.');

            $table->bigInteger('to_user', 255)
                ->unsigned()
                ->comment('ID of user that should receive this message.');

            $table->string('read', 255)
                ->comment('IDs of users who read this message.');

            $table->string('deleted', 255)
                ->comment('Indicator if one of the parties deleted the message, if it is already 1 the script will remove this row.');

            $table->integer('timestamp', 11)
                ->unsigned()
                ->comment('Timestamp of the time this message was sent');

            $table->string('subject', 255)
                ->comment('Title of the message');

            $table->text('content')
                ->comment('Contents of the message.');
        });

        Schema::create('news', function (Blueprint $table) {
            $table->bigIncrements('news_id', 255)
                ->unsigned()
                ->comment('Automatically generated ID by MySQL for management.');

            $table->string('news_category', 255)
                ->comment('Category ID.');

            $table->bigInteger('user_id', 255)
                ->unsigned()
                ->comment('ID of user who posted this news message.');

            $table->integer('news_timestamp', 11)
                ->unsigned()
                ->comment('News post timestamp.');

            $table->string('news_title', 255)
                ->comment('Title of the post.');

            $table->text('news_content')
                ->comment('Contents of the post');
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->bigIncrements('alert_id', 255)
                ->unsigned()
                ->comment('Automatically generated ID by MySQL for management.');

            $table->bigInteger('user_id', 255)
                ->unsigned()
                ->default(0)
                ->comment('User ID this notification is intended for.');

            $table->integer('alert_timestamp', 11)
                ->unsigned()
                ->default(0)
                ->comment('Timestamp when this notification was created.');

            $table->tinyInteger('alert_read', 1)
                ->unsigned()
                ->default(0)
                ->comment('Toggle for unread and read.');

            $table->tinyInteger('alert_sound', 1)
                ->unsigned()
                ->default(0)
                ->comment('Toggle if a sound should be played upon receiving the notification.');

            $table->string('alert_title', 255)
                ->comment('Title displayed on the notification.');

            $table->string('alert_text', 255)
                ->comment('Text displayed.');

            $table->string('alert_link', 255)
                ->comment('Link (empty for no link).');

            $table->string('alert_img', 255)
                ->comment('Image path, prefix with font: to use a font class instead of an image.');

            $table->integer('alert_timeout', 16)
                ->unsigned()
                ->default(0)
                ->comment('How long the notification should stay on screen in milliseconds, 0 for forever.');
        });

        Schema::create('optionfields', function (Blueprint $table) {
            $table->string('option_id', 255)
                ->unique()
                ->comment('Unique identifier for accessing this option.');

            $table->string('option_name', 255)
                ->comment('Description of the field in a proper way.');

            $table->string('option_description', 255)
                ->comment('Longer description of the option.');

            $table->string('option_type', 255)
                ->comment('Type attribute in the input element.');

            $table->string('option_permission', 255)
                ->comment('The minimum permission level this option requires.');
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->bigInteger('rank_id', 255)
                ->unsigned()
                ->default(0)
                ->comment('ID of the rank this permissions set is used for.');

            $table->bigInteger('user_id', 255)
                ->unsigned()
                ->default(0)
                ->comment('ID of the user this permissions set is used for.');

            $table->string('permissions_site', 255)
                ->default(0)
                ->comment('Site permissions.');

            $table->string('permissions_manage', 255)
                ->default(0)
                ->comment('Site management permissions');
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->bigIncrements('post_id', 255)
                ->unsigned()
                ->comment('MySQL Generated ID used for sorting.');

            $table->bigInteger('topic_id', 255)
                ->unsigned()
                ->default(0)
                ->comment('ID of topic this post is a part of.');

            $table->bigInteger('forum_id', 255)
                ->unsigned()
                ->default(0)
                ->comment('ID of forum this was posted in.');

            $table->bigInteger('poster_id', 255)
                ->unsigned()
                ->default(0)
                ->comment('ID of poster of this post.');

            $table->string('poster_ip', 40)
                ->comment('IP of poster.');

            $table->integer('post_time', 11)
                ->unsigned()
                ->default(0)
                ->comment('Time this post was made.');

            $table->string('post_subject', 255)
                ->comment('Subject of the post.');

            $table->text('post_text')
                ->comment('Contents of the post.');

            $table->integer('post_edit_time', 11)
                ->unsigned()
                ->default(0)
                ->comment('Time this post was last edited.');

            $table->string('post_edit_reason', 255)
                ->comment('Reason this was edited.');

            // yup, integer, despite being bigint every else >.>
            $table->integer('post_edit_user', 255)
                ->unsigned()
                ->default(0)
                ->comment('ID of user that edited.');
        });

        Schema::create('premium', function (Blueprint $table) {
            $table->bigInteger('user_id', 255)
                ->unsigned()
                ->unique()
                ->comment('ID of the user that purchased Tenshi.');

            $table->integer('premium_start', 11)
                ->unsigned()
                ->comment('Timestamp of first purchase.');

            $table->integer('premium_expire', 11)
                ->unsigned()
                ->comment('Expiration timestamp.');
        });

        Schema::create('premium_log', function (Blueprint $table) {
            $table->increments('transaction_id', 16)
                ->unsigned()
                ->comment('MySQL Generated ID used for sorting.');

            $table->bigInteger('user_id', 255)
                ->unsigned()
                ->comment('User ID of purchaser');

            $table->float('transaction_amount')
                ->comment('Amount that was transferred.');

            $table->integer('transaction_date', 11)
                ->unsigned()
                ->comment('Date when the purchase was made.');

            $table->string('transaction_comment', 255)
                ->comment('A short description of the action taken.');
        });

        Schema::create('profilefields', function (Blueprint $table) {
            $table->increments('field_id', 64)
                ->unsigned()
                ->comment('ID used for ordering on the userpage.');

            $table->string('field_name', 255)
                ->comment('Name of the field.');

            $table->string('field_type', 255)
                ->comment('Type attribute in the input element.');

            $table->tinyInteger('field_link', 1)
                ->unsigned()
                ->comment('Set if this value should be put in a href.');

            $table->string('field_linkformat', 255)
                ->comment('If the form is a link how should it be formatted? {{ VAL }} gets replace with the value.');

            $table->string('field_description', 255)
                ->comment('Description of the field displayed in the control panel.');

            $table->string('field_additional', 255)
                ->comment('Undocumented JSON array containing special options if needed (probably only going to be used for the YouTube field).');
        });

        Schema::create('ranks', function (Blueprint $table) {
            $table->bigIncrements('rank_id', 128)
                ->unsigned()
                ->comment('Automatically generated ID by MySQL for management.');

            $table->integer('rank_hierarchy', 11)
                ->unsigned()
                ->comment('Rank hierarchy.');

            $table->string('rank_name', 255)
                ->comment('Display name of the rank.');

            $table->string('rank_multiple', 10)
                ->nullable()
                ->default(null)
                ->comment('Used when addressing this rank as a multiple');

            $table->tinyInteger('rank_hidden', 1)
                ->unsigned()
                ->default(0)
                ->comment("Don't show any public links to this rank.");

            $table->string('rank_colour', 255)
                ->comment('Colour used for the username of a member of this rank.');

            $table->text('rank_description')
                ->comment('A description of what a user of this rank can do/is supposed to do.');

            $table->string('rank_title', 64)
                ->comment('Default user title if user has none set.');
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->bigIncrements('id', 255)
                ->unsigned()
                ->comment('MySQL Generated ID used for sorting.');

            $table->integer('type', 32)
                ->unsigned()
                ->comment('Report type, entirely handled on the script side.');

            $table->bigInteger('issuer', 255)
                ->unsigned()
                ->comment('ID of the person who issued this report.');

            // what the fuck
            $table->bigInteger('subject', 255)
                ->unsigned()
                ->comment("ID pointing out what was reported (a more accurate description isn't possible due to the type column).");

            $table->string('title', 255)
                ->comment('A quick description of this report.');

            $table->text('description')
                ->comment('And a detailed description.');

            $table->bigInteger('reviewed', 255)
                ->unsigned()
                ->default(0)
                ->comment('ID of the moderator that reviewed this report.');
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->bigIncrements('session_id', 255)
                ->unsigned()
                ->comment('Automatically generated ID by MySQL for management. ');

            $table->bigInteger('user_id', 255)
                ->unsigned()
                ->comment('ID of the user this session is spawned for. ');

            $table->string('user_ip', 255)
                ->comment('IP of the user this session is spawned for.');

            $table->string('user_agent', 255)
                ->nullable()
                ->default(null)
                ->comment('User agent of the user this session is spawned for.');

            $table->string('session_key', 255)
                ->comment("Session key, allow direct access to the user's account. ");

            $table->integer('session_start', 16)
                ->unsigned()
                ->comment('The timestamp for when the session was started. ');

            $table->integer('session_expire', 16)
                ->unsigned()
                ->comment('The timestamp for when this session should end, -1 for permanent. ');

            $table->tinyInteger('session_remember', 1)
                ->unsigned()
                ->default(0)
                ->comment('If set to 1 session will be extended each time a page is loaded.');
        });

        Schema::create('topics', function (Blueprint $table) {
            $table->bigIncrements('topic_id', 255)
                ->unsigned()
                ->comment('ID of forum this topic was created in.');

            $table->bigInteger('forum_id', 255)
                ->unsigned()
                ->default(0)
                ->comment('ID of forum this topic was created in.');

            $table->tinyInteger('topic_hidden', 1)
                ->unsigned()
                ->default(0)
                ->comment('Boolean to set the topic as hidden.');

            $table->string('topic_title', 255)
                ->comment('Title of the topic.');

            $table->integer('topic_time', 11)
                ->unsigned()
                ->default(0)
                ->comment('Timestamp when the topic was created.');

            $table->integer('topic_time_limit', 11)
                ->unsigned()
                ->default(0)
                ->comment('After how long a topic should be locked.');

            $table->bigInteger('topic_views', 255)
                ->unsigned()
                ->default(0)
                ->comment('Amount of times the topic has been viewed.');

            $table->tinyInteger('topic_status', 3)
                ->unsigned()
                ->default(0)
                ->comment('Status of topic.');

            $table->integer('topic_status_change', 11)
                ->unsigned()
                ->default(0)
                ->comment('Date the topic status was changed (used for deletion cooldown as well).');

            $table->tinyInteger('topic_type', 3)
                ->unsigned()
                ->default(0)
                ->comment('Type of the topic.');

            $table->integer('topic_last_reply', 11)
                ->unsigned()
                ->default(0)
                ->comment('Timestamp of when the last reply made to this thread.');

            $table->bigInteger('topic_old_forum', 255)
                ->unsigned()
                ->default(0)
                ->comment('Pre-move forum id.');
        });

        Schema::create('topics_track', function (Blueprint $table) {
            $table->bigInteger('user_id', 255)
                ->unsigned()
                ->comment('ID of the user this row applies to.');

            $table->bigInteger('topic_id', 255)
                ->unsigned()
                ->comment('ID of the thread in question.');

            $table->bigInteger('forum_id', 255)
                ->unsigned()
                ->comment('ID of the forum in question.');

            $table->integer('mark_time', 11)
                ->unsigned()
                ->default(0)
                ->comment('Timestamp of the event.');
        });

        Schema::create('uploads', function (Blueprint $table) {
            $table->bigIncrements('file_id', 255)
                ->unsigned()
                ->comment('Automatically generated value for management');

            $table->bigInteger('user_id', 255)
                ->unsigned()
                ->comment('ID of the user that uploaded the file');

            // this one's actually longblob
            $table->binary('file_data')
                ->comment('Contents of the file');

            $table->string('file_name', 255)
                ->comment('Name of the file');

            $table->string('file_mime', 255)
                ->comment('Static mime type of the file');

            $table->integer('file_time', 11)
                ->unsigned()
                ->comment('Timestamp of when the file was uploaded');

            $table->integer('file_expire', 11)
                ->unsigned()
                ->comment('When should the file be removed, 0 for never');
        });

        Schema::create('user_optionfields', function (Blueprint $table) {
            $table->bigInteger('user_id', 255)
                ->unsigned()
                ->comment('User this field applies to');

            $table->string('field_name', 255)
                ->comment('Identifier of the field');

            $table->string('field_value', 255)
                ->comment('Value of the field');
        });

        Schema::create('user_profilefields', function (Blueprint $table) {
            $table->bigInteger('user_id', 255)
                ->unsigned()
                ->comment('User this field applies to');

            $table->string('field_name', 255)
                ->comment('Identifier of the field');

            $table->string('field_value', 255)
                ->comment('Value of the field');
        });

        Schema::create('user_ranks', function (Blueprint $table) {
            $table->bigInteger('user_id', 255)
                ->unsigned();

            $table->bigInteger('rank_id', 128)
                ->unsigned();
        });

        Schema::create('username_history', function (Blueprint $table) {
            $table->increments('change_id', 11)
                ->unsigned()
                ->comment('Identifier');

            $table->integer('change_time', 11)
                ->unsigned()
                ->comment('Timestamp of change');

            $table->bigInteger('user_id', 255)
                ->unsigned()
                ->comment('User ID');

            $table->string('username_new', 255)
                ->comment('New username');

            $table->string('username_new_clean', 255)
                ->comment('Clean new username');

            $table->string('username_old', 255)
                ->comment('Old username');

            $table->string('username_old_clean', 255)
                ->comment('Clean old username');
        });

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id', 255)
                ->unsigned()
                ->comment('Automatically generated ID by MySQL for management. ');

            $table->string('username', 255)
                ->comment('Username set at registration.');

            $table->string('username_clean', 255)
                ->unique()
                ->comment('A more cleaned up version of the username for backend usage.');

            $table->string('password_hash', 255)
                ->comment('Hashing algo used for the password hash.');

            $table->string('password_salt', 255)
                ->comment('Salt used for the password hash.');

            $table->string('password_algo', 255)
                ->comment('Algorithm used for the password hash.');

            $table->integer('password_iter', 11)
                ->unsigned()
                ->comment('Password hash iterations.');

            $table->integer('password_chan', 11)
                ->unsigned()
                ->default(0)
                ->comment('Last time the user changed their password.');

            $table->string('email', 255)
                ->comment('E-mail of the user for password restoring etc.');

            $table->mediumInteger('rank_main', 4)
                ->unsigned()
                ->default(0)
                ->comment('Main rank of the user.');

            $table->string('user_colour', 255)
                ->nullable()
                ->default(null)
                ->comment('Additional name colour, when empty colour defaults to group colour.');

            $table->string('register_ip', 255)
                ->comment('IP used for the creation of this account.');

            $table->string('last_ip', 255)
                ->comment('Last IP that was used to log into this account.');

            $table->string('user_title', 64)
                ->nullable()
                ->default(null)
                ->comment('Custom user title of the user, when empty reverts to their derault group name.');

            $table->integer('user_register', 11)
                ->unsigned()
                ->default(0)
                ->comment('Timestamp of account creation.');

            $table->integer('user_last_online', 11)
                ->unsigned()
                ->default(0)
                ->comment('Last time anything was done on this account.');

            $table->date('user_birthday')
                ->default('0000-00-00')
                ->comment('Birthdate of the user.');

            $table->char('user_country', 2)
                ->default('XX')
                ->comment("Contains ISO 3166 country code of user's registration location.");

            $table->bigInteger('user_avatar', 255)
                ->unsigned()
                ->default(0)
                ->comment('ID of the avatar in the uploads table.');

            $table->bigInteger('user_background', 255)
                ->unsigned()
                ->default(0)
                ->comment('ID of the background in the uploads table.');

            $table->bigInteger('user_header', 255)
                ->unsigned()
                ->default(0)
                ->comment('ID of the profile header in the uploads table.');

            $table->longText('user_page')
                ->comment('Contents of the userpage.');

            $table->text('user_signature')
                ->comment('Signature displayed below forum posts.');

            $table->string('password', 255)
                ->nullable()
                ->default(null);
        });

        Schema::create('warnings', function (Blueprint $table) {
            $table->bigIncrements('warning_id', 255)
                ->unsigned()
                ->comment('Automatically generated ID by MySQL for management.');

            $table->bigInteger('user_id', 255)
                ->unsigned()
                ->comment('ID of user that was warned.');

            $table->bigInteger('moderator_id', 255)
                ->unsigned()
                ->comment('ID of the user that issued the warning.');

            $table->integer('warning_issued', 16)
                ->unsigned()
                ->comment('Timestamp of the date the warning was issued.');

            $table->integer('warning_expires', 16)
                ->unsigned()
                ->comment('Timstamp when the warning should expire, 0 for a permanent warning.');

            $table->tinyInteger('warning_action', 1)
                ->unsigned()
                ->nullable()
                ->default(null)
                ->comment('Action taken.');

            $table->string('warning_reason', 512)
                ->nullable()
                ->default(null)
                ->comment('Reason for the warning.');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('actioncodes');
        Schema::drop('apikeys');
        Schema::drop('bans');
        Schema::drop('comment_votes');
        Schema::drop('comments');
        Schema::drop('config');
        Schema::drop('emoticons');
        Schema::drop('faq');
        Schema::drop('forum_permissions');
        Schema::drop('forums');
        Schema::drop('friends');
        Schema::drop('infopages');
        Schema::drop('login_attempts');
        Schema::drop('messages');
        Schema::drop('news');
        Schema::drop('notifications');
        Schema::drop('optionfields');
        Schema::drop('permissions');
        Schema::drop('posts');
        Schema::drop('premium');
        Schema::drop('premium_log');
        Schema::drop('profilefields');
        Schema::drop('ranks');
        Schema::drop('reports');
        Schema::drop('sessions');
        Schema::drop('topics');
        Schema::drop('topics_track');
        Schema::drop('uploads');
        Schema::drop('user_optionfields');
        Schema::drop('user_profilefields');
        Schema::drop('user_ranks');
        Schema::drop('username_history');
        Schema::drop('users');
        Schema::drop('warnings');
    }
}
