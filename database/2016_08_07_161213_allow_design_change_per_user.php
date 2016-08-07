<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Sakura\DB;

class AllowDesignChangePerUser extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        $schema = DB::getSchemaBuilder();

        $schema->table('users', function (Blueprint $table) {
            $table->string('user_design')
                ->nullable()
                ->default(null);
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        $schema = DB::getSchemaBuilder();

        $schema->table('users', function (Blueprint $table) {
            $table->dropColumn('user_design');
        });
    }
}
