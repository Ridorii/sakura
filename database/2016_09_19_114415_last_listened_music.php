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
            $table->text('user_last_track')
                ->nullable();

            $table->string('user_last_track_url', 255)
                ->nullable()
                ->default(null);

            $table->text('user_last_artist')
                ->nullable();

            $table->string('user_last_artist_url', 255)
                ->nullable()
                ->default(null);

            $table->string('user_last_cover', 255)
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
            $table->dropColumn([
                'user_last_track',
                'user_last_track_url',
                'user_last_artist',
                'user_last_artist_url',
                'user_last_cover',
            ]);
        });
    }
}
