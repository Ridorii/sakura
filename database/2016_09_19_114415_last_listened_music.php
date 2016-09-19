<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Sakura\DB;

class LastListenedMusic extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        $schema = DB::getSchemaBuilder();

        $schema->table('users', function (Blueprint $table) {
            $table->text('user_music_track')
                ->nullable();

            $table->text('user_music_artist')
                ->nullable();

            $table->integer('user_music_check')
                ->default(0);

            $table->boolean('user_music_listening')
                ->default(false);
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
            $table->dropColumn([
                'user_music_track',
                'user_music_artist',
                'user_music_check',
                'user_music_listening',
            ]);
        });
    }
}
