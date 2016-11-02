<?php
/**
 * Holds the setup command controller.
 * @package Sakura
 */

namespace Sakura\Console\Command;

use CLIFramework\Command;
use Sakura\DB;
use Sakura\Net;
use Sakura\User;

/**
 * The command that handles setting up the base data.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class SetupCommand extends Command
{
    /**
     * A quick description of this command.
     * @return string.
     */
    public function brief()
    {
        return 'Adds the required data to the tables, only needed once after the initial migration.';
    }

    /**
     * Adds data to the database required to get everything running.
     */
    public function execute()
    {
        // Check if the users table has user with id 1
        $userCheck = DB::table('users')->where('user_id', 1)->count();

        // If positive, stop
        if ($userCheck > 0) {
            $this->getLogger()->writeln("It appears that you've already done the setup already!");
            $this->getLogger()->writeln("If this isn't the case, make sure your tables are empty.");
            return;
        }

        // Rank data (uses column names)
        $ranks = [
            [
                'rank_hierarchy' => 0,
                'rank_name' => 'Inactive',
                'rank_hidden' => 1,
                'rank_colour' => '#555',
                'rank_description' => 'Users that are yet to be activated or have deactivated their account.',
                'rank_title' => 'Inactive',
            ],
            [
                'rank_hierarchy' => 1,
                'rank_name' => 'Normal user',
                'rank_multiple' => 's',
                'rank_description' => 'Regular users with regular permissions.',
                'rank_title' => 'Member',
            ],
            [
                'rank_hierarchy' => 3,
                'rank_name' => 'Moderator',
                'rank_multiple' => 's',
                'rank_colour' => '#fa3703',
                'rank_description' => 'Users with special permissions to keep the community at peace.',
                'rank_title' => 'Moderator',
            ],
            [
                'rank_hierarchy' => 4,
                'rank_name' => 'Administrator',
                'rank_multiple' => 's',
                'rank_colour' => '#824ca0',
                'rank_description' => 'Users that manage the and everything around that.',
                'rank_title' => 'Administrator',
            ],
            [
                'rank_hierarchy' => 1,
                'rank_name' => 'Bot',
                'rank_multiple' => 's',
                'rank_hidden' => 1,
                'rank_colour' => '#9e8da7',
                'rank_description' => 'Reserved accounts for services.',
                'rank_title' => 'Bot',
            ],
            [
                'rank_hierarchy' => 2,
                'rank_name' => 'Premium',
                'rank_colour' => '#ee9400',
                'rank_description' => 'Users that purchased premium to help us keep the site and its service alive.',
                'rank_title' => 'Premium',
            ],
            [
                'rank_hierarchy' => 1,
                'rank_name' => 'Alumni',
                'rank_colour' => '#ff69b4',
                'rank_description' => 'Users who made big contributions to the site but have since moved on.',
                'rank_title' => 'Alumni',
            ],
            [
                'rank_hierarchy' => 0,
                'rank_name' => 'Banned',
                'rank_colour' => '#666',
                'rank_description' => 'Banned users.',
                'rank_title' => 'Banned',
            ],
        ];

        // Insert all the ranks into the database
        foreach ($ranks as $rank) {
            DB::table('ranks')->insert($rank);
        }

        // Permission data
        $perms = [
            [
                'rank_id' => config('rank.regular'),
                'perm_change_profile' => true,
                'perm_change_avatar' => true,
                'perm_change_userpage' => true,
                'perm_change_signature' => true,
                'perm_deactivate_account' => true,
                'perm_view_user_links' => true,
                'perm_manage_ranks' => true,
                'perm_manage_friends' => true,
                'perm_comments_create' => true,
                'perm_comments_edit' => true,
                'perm_comments_delete' => true,
                'perm_comments_vote' => true,
            ],
            [
                'rank_id' => config('rank.mod'),
                'perm_change_background' => true,
                'perm_change_header' => true,
                'perm_change_username' => true,
                'perm_change_user_title' => true,
                'perm_view_user_details' => true,
                'perm_is_mod' => true,
                'perm_can_restrict' => true,
                'perm_manage_profile_images' => true,
            ],
            [
                'rank_id' => config('rank.admin'),
                'perm_change_background' => true,
                'perm_change_header' => true,
                'perm_change_username' => true,
                'perm_change_user_title' => true,
                'perm_view_user_details' => true,
                'perm_is_mod' => true,
                'perm_is_admin' => true,
                'perm_can_restrict' => true,
                'perm_manage_profile_images' => true,
            ],
            [
                'rank_id' => config('rank.premium'),
                'perm_change_background' => true,
                'perm_change_header' => true,
                'perm_change_username' => true,
                'perm_change_user_title' => true,
            ],
            [
                'rank_id' => config('rank.banned'),
                'perm_change_profile' => false,
                'perm_change_avatar' => false,
                'perm_change_background' => false,
                'perm_change_header' => false,
                'perm_change_userpage' => false,
                'perm_change_signature' => false,
                'perm_change_username' => false,
                'perm_change_user_title' => false,
                'perm_deactivate_account' => false,
                'perm_view_user_links' => false,
                'perm_view_user_details' => false,
                'perm_manage_ranks' => false,
                'perm_manage_friends' => false,
                'perm_comments_create' => false,
                'perm_comments_edit' => false,
                'perm_comments_delete' => false,
                'perm_comments_vote' => false,
                'perm_is_mod' => false,
                'perm_is_admin' => false,
                'perm_can_restrict' => false,
                'perm_manage_profile_images' => false,
            ],
        ];

        // Insert all the permissions into the database
        foreach ($perms as $perm) {
            DB::table('perms')->insert($perm);
        }

        // Forum data
        $forums = [
            [
                'forum_order' => 1,
                'forum_name' => 'Your first category',
                'forum_type' => 1,
            ],
            [
                'forum_order' => 1,
                'forum_name' => 'Your first playpen',
                'forum_desc' => 'Description of your first forum.',
                'forum_category' => 1,
                'forum_icon' => 'fa-smile-o',
            ],
            [
                'forum_order' => 2,
                'forum_name' => 'Private',
                'forum_type' => 1,
            ],
            [
                'forum_order' => 1,
                'forum_name' => 'Trash',
                'forum_desc' => 'Where the deleted topics go before being permanently removed.',
                'forum_category' => 3,
                'forum_icon' => 'fa-trash',
            ],
        ];

        // Insert all the forums into the database
        foreach ($forums as $forum) {
            DB::table('forums')->insert($forum);
        }

        // Forum permission data
        $forum_perms = [
            [
                'forum_id' => 1,
                'rank_id' => config('rank.inactive'),
                'perm_view' => true,
            ],
            [
                'forum_id' => 3,
                'rank_id' => config('rank.inactive'),
                'perm_view' => false,
            ],
            [
                'forum_id' => 1,
                'rank_id' => config('rank.regular'),
                'perm_view' => true,
                'perm_reply' => true,
                'perm_topic_create' => true,
                'perm_edit' => true,
                'perm_delete' => true,
            ],
            [
                'forum_id' => 3,
                'rank_id' => config('rank.regular'),
                'perm_view' => false,
            ],
            [
                'forum_id' => 1,
                'rank_id' => config('rank.mod'),
                'perm_topic_delete' => true,
                'perm_topic_move' => true,
                'perm_edit_any' => true,
                'perm_delete_any' => true,
                'perm_change_type' => true,
                'perm_change_status' => true,
            ],
            [
                'forum_id' => 3,
                'rank_id' => config('rank.mod'),
                'perm_topic_delete' => true,
                'perm_topic_move' => true,
                'perm_edit_any' => true,
                'perm_delete_any' => true,
                'perm_change_type' => true,
            ],
            [
                'forum_id' => 0,
                'rank_id' => config('rank.admin'),
                'perm_view' => true,
                'perm_reply' => true,
                'perm_topic_create' => true,
                'perm_topic_delete' => true,
                'perm_topic_move' => true,
                'perm_edit' => true,
                'perm_edit_any' => true,
                'perm_delete' => true,
                'perm_delete_any' => true,
                'perm_bypass_rules' => true,
                'perm_change_type' => true,
                'perm_change_status' => true,
            ],
            [
                'forum_id' => 0,
                'rank_id' => config('rank.banned'),
                'perm_reply' => false,
                'perm_topic_create' => false,
                'perm_topic_delete' => false,
                'perm_topic_move' => false,
                'perm_edit' => false,
                'perm_edit_any' => false,
                'perm_delete' => false,
                'perm_delete_any' => false,
                'perm_bypass_rules' => false,
                'perm_change_type' => false,
                'perm_change_status' => false,
            ],
            [
                'forum_id' => 1,
                'rank_id' => config('rank.banned'),
                'perm_view' => true,
            ],
            [
                'forum_id' => 3,
                'rank_id' => config('rank.banned'),
                'perm_view' => false,
            ],
        ];

        // Insert all the forum permissions into the database
        foreach ($forum_perms as $fperm) {
            DB::table('forum_perms')->insert($fperm);
        }

        // Bot user
        $botUserId = DB::table('users')->insertGetId([
            'username' => 'Railgun',
            'username_clean' => 'railgun',
            'password' => password_hash('railgun', PASSWORD_BCRYPT),
            'email' => config('mail.contact_address'),
            'register_ip' => Net::pton('::1'),
            'last_ip' => Net::pton('::1'),
            'user_registered' => time(),
            'user_last_online' => 0,
            'user_country' => 'JP',
            'user_activated' => true,
            'user_verified' => true,
        ]);

        // Create the actual user object
        $botUser = User::construct($botUserId);

        // Add ranks to the user
        $botUser->addRanks([
            config('rank.regular'),
            config('rank.bot'),
            config('rank.admin'),
        ]);

        // Set the main rank to bot
        $botUser->setMainRank(config('rank.bot'));

        $this->getLogger()->writeln("Success! You can now start a development server use the serve command for mahou.");
        $this->getLogger()->writeln("The default username and password are both railgun.");
    }
}
