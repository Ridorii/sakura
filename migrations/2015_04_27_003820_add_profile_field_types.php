<?php
use Sakura\Migration\IMigration;
use Sakura\DB;

class AddProfileFieldTypes implements IMigration
{
    public function up()
    {
        // Add islink
        DB::prepare("ALTER TABLE `{prefix}profilefields` ADD `islink` tinyint(1) unsigned NOT NULL")
            ->execute();

        // Add linkformat
        DB::prepare("ALTER TABLE `{prefix}profilefields` ADD `linkformat` varchar(255) COLLATE utf8_bin NOT NULL")
            ->execute();
    }

    public function down()
    {
        // Drop islink
        DB::prepare("ALTER TABLE `{prefix}profilefields` DROP COLUMN `islink`")
            ->execute();

        // Drop linkformat
        DB::prepare("ALTER TABLE `{prefix}profilefields` DROP COLUMN `linkformat`")
            ->execute();
    }
}
