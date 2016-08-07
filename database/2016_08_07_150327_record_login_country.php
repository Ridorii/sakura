<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Sakura\DB;

class RecordLoginCountry extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        $schema = DB::getSchemaBuilder();

        $schema->table('sessions', function (Blueprint $table) {
            $table->char('session_country', 2)
                ->default('XX');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        $schema = DB::getSchemaBuilder();

        $schema->table('sessions', function (Blueprint $table) {
            $table->dropColumn('session_country');
        });
    }
}
