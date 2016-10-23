<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Sakura\DB;

class RestructurePermissions extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        $schema = DB::getSchemaBuilder();

        $schema->table('users', function (Blueprint $table) {
            $table->text('user_activated')
                ->default(0);

            $table->text('user_verified')
                ->default(0);

            $table->text('user_restricted')
                ->default(0);
        });

        $schema->create('perms', function (Blueprint $table) {
            $table->integer('user_id')->default(0);
            $table->integer('rank_id')->default(0);

            $table->boolean('perm_change_profile')->default(false);
            $table->boolean('perm_change_avatar')->default(false);
            $table->boolean('perm_change_background')->default(false);
            $table->boolean('perm_change_header')->default(false);
            $table->boolean('perm_change_userpage')->default(false);
            $table->boolean('perm_change_signature')->default(false);
            $table->boolean('perm_change_username')->default(false);
            $table->boolean('perm_change_user_title')->default(false);

            $table->boolean('perm_deactivate_account')->default(false);

            $table->boolean('perm_view_user_links')->default(false);
            $table->boolean('perm_view_user_details')->default(false);

            $table->boolean('perm_manage_ranks')->default(false);
            $table->boolean('perm_manage_friends')->default(false);

            $table->boolean('perm_comments_create')->default(false);
            $table->boolean('perm_comments_edit')->default(false);
            $table->boolean('perm_comments_delete')->default(false);
            $table->boolean('perm_comments_vote')->default(false);

            $table->boolean('perm_is_mod')->default(false);
            $table->boolean('perm_is_admin')->default(false);
            $table->boolean('perm_can_restrict')->default(false);
            $table->boolean('perm_manage_profile_images')->default(false);
        });

        $schema->create('forum_perms', function (Blueprint $table) {
            $table->integer('forum_id')->default(0);
            $table->integer('user_id')->default(0);
            $table->integer('rank_id')->default(0);

            $table->boolean('perm_view')->default(false);
            $table->boolean('perm_reply')->default(false);

            $table->boolean('perm_topic_create')->default(false);
            $table->boolean('perm_topic_delete')->default(false);
            $table->boolean('perm_topic_move')->default(false);

            $table->boolean('perm_edit')->default(false);
            $table->boolean('perm_edit_any')->default(false);

            $table->boolean('perm_delete')->default(false);
            $table->boolean('perm_delete_any')->default(false);

            $table->boolean('perm_bypass_rules')->default(false);

            $table->boolean('perm_change_type')->default(false);
            $table->boolean('perm_change_status')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        $schema = DB::getSchemaBuilder();
        $schema->drop('forum_perms');
        $schema->drop('perms');
        $schema->table('users', function (Blueprint $table) {
            $table->dropColumn([
                'user_activated',
                'user_verified',
                'user_restricted',
            ]);
        });
    }
}
