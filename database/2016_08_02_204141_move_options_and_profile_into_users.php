<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Sakura\DB;

class MoveOptionsAndProfileIntoUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $schema = DB::getSchemaBuilder();

        $schema->drop('optionfields');
        $schema->drop('profilefields');
        $schema->drop('user_optionfields');
        $schema->drop('user_profilefields');

        $schema->table('users', function (Blueprint $table) {
            $table->tinyInteger('user_background_sitewide')
                ->default(0);

            $table->string('user_website', 255)
                ->nullable()
                ->default(null);

            $table->string('user_twitter', 255)
                ->nullable()
                ->default(null);

            $table->string('user_github', 255)
                ->nullable()
                ->default(null);

            $table->string('user_skype', 255)
                ->nullable()
                ->default(null);

            $table->string('user_discord', 255)
                ->nullable()
                ->default(null);

            $table->string('user_youtube', 255)
                ->nullable()
                ->default(null);

            $table->tinyInteger('user_youtube_type')
                ->default(0);

            $table->string('user_steam', 255)
                ->nullable()
                ->default(null);

            $table->string('user_osu', 255)
                ->nullable()
                ->default(null);

            $table->string('user_lastfm', 255)
                ->nullable()
                ->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $schema = DB::getSchemaBuilder();

        $schema->table('users', function (Blueprint $table) {
            $table->dropColumn([
                'user_background_sitewide',
                'user_website',
                'user_twitter',
                'user_github',
                'user_skype',
                'user_discord',
                'user_youtube',
                'user_youtube_type',
                'user_steam',
                'user_osu',
                'user_lastfm',
            ]);
        });

        $schema->create('optionfields', function (Blueprint $table) {
            $table->string('option_id', 255)
                ->unique();

            $table->string('option_name', 255);

            $table->string('option_description', 255);

            $table->string('option_type', 255);

            $table->string('option_permission', 255);
        });

        $schema->create('profilefields', function (Blueprint $table) {
            $table->increments('field_id')
                ->unsigned();

            $table->string('field_name', 255);

            $table->string('field_type', 255);

            $table->tinyInteger('field_link')
                ->unsigned();

            $table->string('field_linkformat', 255);

            $table->string('field_description', 255);

            $table->string('field_additional', 255);
        });

        $schema->create('user_optionfields', function (Blueprint $table) {
            $table->integer('user_id')
                ->unsigned();

            $table->string('field_name', 255);

            $table->string('field_value', 255);
        });

        $schema->create('user_profilefields', function (Blueprint $table) {
            $table->integer('user_id')
                ->unsigned();

            $table->string('field_name', 255);

            $table->string('field_value', 255);
        });
    }
}
