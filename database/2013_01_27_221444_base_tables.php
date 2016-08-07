<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Sakura\DB;

class BaseTables extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        $schema = DB::getSchemaBuilder();

        $schema->create('actioncodes', function (Blueprint $table) {
            $table->string('code_action', 255);

            $table->integer('user_id')
                ->unsigned();

            $table->string('action_code', 255);
        });

        $schema->create('comment_votes', function (Blueprint $table) {
            $table->integer('vote_comment')
                ->unsigned();

            $table->integer('vote_user')
                ->unsigned();

            $table->tinyInteger('vote_state')
                ->unsigned();
        });

        $schema->create('comments', function (Blueprint $table) {
            $table->increments('comment_id');

            $table->string('comment_category', 32);

            $table->integer('comment_timestamp')
                ->unsigned();

            $table->integer('comment_poster')
                ->unsigned();

            $table->integer('comment_reply_to')
                ->unsigned()
                ->default(0);

            $table->text('comment_text', 255);
        });

        $schema->create('emoticons', function (Blueprint $table) {
            $table->string('emote_string', 255);

            $table->string('emote_path', 255);
        });

        $schema->create('error_log', function (Blueprint $table) {
            $table->string('error_id', 32);

            $table->string('error_timestamp', 128);

            $table->integer('error_revision')
                ->unsigned();

            $table->integer('error_type')
                ->unsigned();

            $table->integer('error_line')
                ->unsigned();

            $table->string('error_string', 512);

            $table->string('error_file', 512);

            $table->text('error_backtrace');
        });

        $schema->create('faq', function (Blueprint $table) {
            $table->increments('faq_id');

            $table->string('faq_shorthand', 255);

            $table->string('faq_question', 255);

            $table->text('faq_answer');
        });

        $schema->create('forum_permissions', function (Blueprint $table) {
            $table->integer('forum_id')
                ->unsigned();

            $table->integer('rank_id')
                ->unsigned()
                ->default(0);

            $table->integer('user_id')
                ->unsigned()
                ->default(0);

            $table->string('forum_perms', 255);
        });

        $schema->create('forums', function (Blueprint $table) {
            $table->increments('forum_id');

            $table->integer('forum_order')
                ->unsigned();

            $table->string('forum_name', 255);

            $table->text('forum_desc')
                ->nullable()
                ->default(null);

            $table->string('forum_link', 255)
                ->nullable()
                ->default(null);

            $table->integer('forum_category')
                ->unsigned()
                ->default(0);

            $table->tinyInteger('forum_type')
                ->unsigned()
                ->default(0);

            $table->string('forum_icon', 255)
                ->nullable()
                ->default(null);
        });

        $schema->create('friends', function (Blueprint $table) {
            $table->integer('user_id')
                ->unsigned();

            $table->integer('friend_id')
                ->unsigned();

            $table->integer('friend_timestamp', 11)
                ->unsigned();
        });

        $schema->create('login_attempts', function (Blueprint $table) {
            $table->increments('attempt_id');

            $table->tinyInteger('attempt_success')
                ->unsigned();

            $table->integer('attempt_timestamp')
                ->unsigned();

            $table->binary('attempt_ip');

            $table->integer('user_id')
                ->unsigned();
        });

        $schema->create('news', function (Blueprint $table) {
            $table->increments('news_id');

            $table->string('news_category', 255);

            $table->integer('user_id')
                ->unsigned();

            $table->integer('news_timestamp')
                ->unsigned();

            $table->string('news_title', 255);

            $table->text('news_content');
        });

        $schema->create('notifications', function (Blueprint $table) {
            $table->increments('alert_id');

            $table->integer('user_id')
                ->unsigned()
                ->default(0);

            $table->integer('alert_timestamp')
                ->unsigned()
                ->default(0);

            $table->tinyInteger('alert_read')
                ->unsigned()
                ->default(0);

            $table->tinyInteger('alert_sound')
                ->unsigned()
                ->default(0);

            $table->string('alert_title', 255);

            $table->string('alert_text', 255);

            $table->string('alert_link', 255);

            $table->string('alert_img', 255);

            $table->integer('alert_timeout')
                ->unsigned()
                ->default(0);
        });

        $schema->create('optionfields', function (Blueprint $table) {
            $table->string('option_id', 255)
                ->unique();

            $table->string('option_name', 255);

            $table->string('option_description', 255);

            $table->string('option_type', 255);

            $table->string('option_permission', 255);
        });

        $schema->create('permissions', function (Blueprint $table) {
            $table->integer('rank_id')
                ->unsigned()
                ->default(0);

            $table->integer('user_id')
                ->unsigned()
                ->default(0);

            $table->string('permissions_site', 255)
                ->default(0);

            $table->string('permissions_manage', 255)
                ->default(0);
        });

        $schema->create('posts', function (Blueprint $table) {
            $table->increments('post_id');

            $table->integer('topic_id')
                ->unsigned()
                ->default(0);

            $table->integer('forum_id')
                ->unsigned()
                ->default(0);

            $table->integer('poster_id')
                ->unsigned()
                ->default(0);

            $table->binary('poster_ip');

            $table->integer('post_time')
                ->unsigned()
                ->default(0);

            $table->string('post_subject', 255);

            $table->text('post_text');

            $table->integer('post_edit_time')
                ->unsigned()
                ->default(0);

            $table->string('post_edit_reason', 255)
                ->nullable()
                ->default(null);

            $table->integer('post_edit_user')
                ->unsigned()
                ->default(0);
        });

        $schema->create('premium', function (Blueprint $table) {
            $table->integer('user_id')
                ->unsigned()
                ->unique();

            $table->integer('premium_start')
                ->unsigned();

            $table->integer('premium_expire')
                ->unsigned();
        });

        $schema->create('profilefields', function (Blueprint $table) {
            $table->increments('field_id')
                ->unsigned();

            $table->string('field_name', 255);

            $table->string('field_type', 255);

            $table->tinyInteger('field_link')
                ->unsigned();

            $table->string('field_linkformat', 255);

            $table->string('field_description', 255);

            $table->string('field_additional', 255);
        });

        $schema->create('ranks', function (Blueprint $table) {
            $table->increments('rank_id');

            $table->integer('rank_hierarchy')
                ->unsigned();

            $table->string('rank_name', 255);

            $table->string('rank_multiple', 10)
                ->nullable()
                ->default(null);

            $table->tinyInteger('rank_hidden')
                ->unsigned()
                ->default(0);

            $table->string('rank_colour', 255)
                ->nullable()
                ->default(null);

            $table->text('rank_description');

            $table->string('rank_title', 64);
        });

        $schema->create('sessions', function (Blueprint $table) {
            $table->increments('session_id');

            $table->integer('user_id')
                ->unsigned();

            $table->binary('user_ip');

            $table->string('user_agent', 255)
                ->nullable()
                ->default(null);

            $table->string('session_key', 255);

            $table->integer('session_start')
                ->unsigned();

            $table->integer('session_expire')
                ->unsigned();

            $table->tinyInteger('session_remember')
                ->unsigned()
                ->default(0);
        });

        $schema->create('topics', function (Blueprint $table) {
            $table->increments('topic_id');

            $table->integer('forum_id')
                ->unsigned()
                ->default(0);

            $table->tinyInteger('topic_hidden')
                ->unsigned()
                ->default(0);

            $table->string('topic_title', 255);

            $table->integer('topic_time')
                ->unsigned()
                ->default(0);

            $table->integer('topic_time_limit')
                ->unsigned()
                ->default(0);

            $table->integer('topic_views')
                ->unsigned()
                ->default(0);

            $table->tinyInteger('topic_status')
                ->unsigned()
                ->default(0);

            $table->integer('topic_status_change')
                ->unsigned()
                ->default(0);

            $table->tinyInteger('topic_type')
                ->unsigned()
                ->default(0);

            $table->integer('topic_last_reply')
                ->unsigned()
                ->default(0);

            $table->integer('topic_old_forum')
                ->unsigned()
                ->default(0);
        });

        $schema->create('topics_track', function (Blueprint $table) {
            $table->integer('user_id')
                ->unsigned();

            $table->integer('topic_id')
                ->unsigned();

            $table->integer('forum_id')
                ->unsigned();

            $table->integer('mark_time')
                ->unsigned()
                ->default(0);
        });

        $schema->create('uploads', function (Blueprint $table) {
            $table->increments('file_id');

            $table->integer('user_id')
                ->unsigned();

            // this one's actually longblob
            $table->binary('file_data');

            $table->string('file_name', 255);

            $table->string('file_mime', 255);

            $table->integer('file_time')
                ->unsigned();

            $table->integer('file_expire')
                ->unsigned();
        });

        $schema->create('user_optionfields', function (Blueprint $table) {
            $table->integer('user_id')
                ->unsigned();

            $table->string('field_name', 255);

            $table->string('field_value', 255);
        });

        $schema->create('user_profilefields', function (Blueprint $table) {
            $table->integer('user_id')
                ->unsigned();

            $table->string('field_name', 255);

            $table->string('field_value', 255);
        });

        $schema->create('user_ranks', function (Blueprint $table) {
            $table->integer('user_id')
                ->unsigned();

            $table->integer('rank_id')
                ->unsigned();
        });

        $schema->create('username_history', function (Blueprint $table) {
            $table->increments('change_id');

            $table->integer('change_time')
                ->unsigned();

            $table->integer('user_id')
                ->unsigned();

            $table->string('username_new', 255);

            $table->string('username_new_clean', 255);

            $table->string('username_old', 255);

            $table->string('username_old_clean', 255);
        });

        $schema->create('users', function (Blueprint $table) {
            $table->increments('user_id');

            $table->string('username', 255);

            $table->string('username_clean', 255)
                ->unique();

            $table->string('password', 60)
                ->nullable()
                ->default(null);

            $table->integer('password_chan')
                ->unsigned()
                ->default(0);

            $table->string('email', 255);

            $table->integer('rank_main')
                ->unsigned()
                ->default(0);

            $table->string('user_colour', 255)
                ->nullable()
                ->default(null);

            $table->binary('register_ip');

            $table->binary('last_ip');

            $table->string('user_title', 64)
                ->nullable()
                ->default(null);

            $table->integer('user_registered')
                ->unsigned()
                ->default(0);

            $table->integer('user_last_online')
                ->unsigned()
                ->default(0);

            $table->date('user_birthday')
                ->nullable()
                ->default(null);

            $table->char('user_country', 2)
                ->default('XX');

            $table->integer('user_avatar')
                ->unsigned()
                ->default(0);

            $table->integer('user_background')
                ->unsigned()
                ->default(0);

            $table->integer('user_header')
                ->unsigned()
                ->default(0);

            $table->longText('user_page')
                ->nullable();

            $table->text('user_signature')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        $schema = DB::getSchemaBuilder();

        $schema->drop('actioncodes');
        $schema->drop('comment_votes');
        $schema->drop('comments');
        $schema->drop('emoticons');
        $schema->drop('error_log');
        $schema->drop('faq');
        $schema->drop('forum_permissions');
        $schema->drop('forums');
        $schema->drop('friends');
        $schema->drop('login_attempts');
        $schema->drop('news');
        $schema->drop('notifications');
        $schema->drop('optionfields');
        $schema->drop('permissions');
        $schema->drop('posts');
        $schema->drop('premium');
        $schema->drop('profilefields');
        $schema->drop('ranks');
        $schema->drop('sessions');
        $schema->drop('topics');
        $schema->drop('topics_track');
        $schema->drop('uploads');
        $schema->drop('user_optionfields');
        $schema->drop('user_profilefields');
        $schema->drop('user_ranks');
        $schema->drop('username_history');
        $schema->drop('users');
    }
}
