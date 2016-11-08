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

        $schema->drop('forum_permissions');
        $schema->drop('permissions');

        $schema->create('perms', function (Blueprint $table) {
            $table->integer('user_id')->default(0);
            $table->integer('rank_id')->default(0);

            $table->boolean('perm_change_profile')->nullable()->default(null);
            $table->boolean('perm_change_avatar')->nullable()->default(null);
            $table->boolean('perm_change_background')->nullable()->default(null);
            $table->boolean('perm_change_header')->nullable()->default(null);
            $table->boolean('perm_change_userpage')->nullable()->default(null);
            $table->boolean('perm_change_signature')->nullable()->default(null);
            $table->boolean('perm_change_username')->nullable()->default(null);
            $table->boolean('perm_change_user_title')->nullable()->default(null);

            $table->boolean('perm_deactivate_account')->nullable()->default(null);

            $table->boolean('perm_view_user_links')->nullable()->default(null);
            $table->boolean('perm_view_user_details')->nullable()->default(null);

            $table->boolean('perm_manage_ranks')->nullable()->default(null);
            $table->boolean('perm_manage_friends')->nullable()->default(null);

            $table->boolean('perm_comments_create')->nullable()->default(null);
            $table->boolean('perm_comments_edit')->nullable()->default(null);
            $table->boolean('perm_comments_delete')->nullable()->default(null);
            $table->boolean('perm_comments_vote')->nullable()->default(null);

            $table->boolean('perm_is_mod')->nullable()->default(null);
            $table->boolean('perm_is_admin')->nullable()->default(null);
            $table->boolean('perm_can_restrict')->nullable()->default(null);
            $table->boolean('perm_manage_profile_images')->nullable()->default(null);
        });

        $schema->create('forum_perms', function (Blueprint $table) {
            $table->integer('forum_id')->default(0);
            $table->integer('user_id')->default(0);
            $table->integer('rank_id')->default(0);

            $table->boolean('perm_view')->nullable()->default(null);
            $table->boolean('perm_reply')->nullable()->default(null);

            $table->boolean('perm_topic_create')->nullable()->default(null);
            $table->boolean('perm_topic_delete')->nullable()->default(null);
            $table->boolean('perm_topic_move')->nullable()->default(null);

            $table->boolean('perm_edit')->nullable()->default(null);
            $table->boolean('perm_edit_any')->nullable()->default(null);

            $table->boolean('perm_delete')->nullable()->default(null);
            $table->boolean('perm_delete_any')->nullable()->default(null);

            $table->boolean('perm_bypass_rules')->nullable()->default(null);

            $table->boolean('perm_change_type')->nullable()->default(null);
            $table->boolean('perm_change_status')->nullable()->default(null);
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

        $schema->table('users', function (Blueprint $table) {
            $table->dropColumn([
                'user_activated',
                'user_verified',
                'user_restricted',
            ]);
        });
    }
}
