<?php
use Sakura\Migration\IMigration;
use Sakura\DB;

class RenameGroupsToRanks implements IMigration
{
    public function up()
    {
        // Rename groups table to ranks
        DB::prepare("RENAME TABLE `{prefix}groups` TO `{prefix}ranks`")
            ->execute();

        // Rename group* columns to rank* in the users table
        DB::prepare('ALTER TABLE `{prefix}users` CHANGE `group_main` `rank_main` mediumint(4)')
            ->execute();
        DB::prepare('ALTER TABLE `{prefix}users` CHANGE `groups` `ranks` varchar(255)')
            ->execute();
    }

    public function down()
    {
        // Rename ranks table to groups
        DB::prepare("RENAME TABLE `{prefix}ranks` TO `{prefix}groups`")
            ->execute();

        // Rename rank* columns to group* in the users table
        DB::prepare('ALTER TABLE `{prefix}users` CHANGE `rank_main` `group_main` mediumint(4)')
            ->execute();
        DB::prepare('ALTER TABLE `{prefix}users` CHANGE `ranks` `groups` varchar(255)')
            ->execute();
    }
}
