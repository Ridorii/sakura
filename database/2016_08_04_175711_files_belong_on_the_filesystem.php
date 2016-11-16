<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Sakura\DB;

class FilesBelongOnTheFilesystem extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        $schema = DB::getSchemaBuilder();

        $schema->table('uploads', function (Blueprint $table) {
            $table->dropColumn('file_data');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        $schema = DB::getSchemaBuilder();

        $schema->table('uploads', function (Blueprint $table) {
            $table->binary('file_data')
                ->nullable();
        });
    }
}
