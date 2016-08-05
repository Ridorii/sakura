<?php
/**
 * Holds the file server.
 * @package Sakura
 */

namespace Sakura;

use finfo;

/**
 * Used for storing files served through Sakura.
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class File
{
    /**
     * ID of the file.
     * @var int
     */
    public $id = 0;

    /**
     * User instance of the user that uploaded this file.
     * @var User
     */
    public $user = null;

    /**
     * Data of the file.
     * @var string
     */
    public $data = null;

    /**
     * Original filename of the file.
     * @var string
     */
    public $name = null;

    /**
     * Mime type of the file.
     * @var string
     */
    public $mime = null;

    /**
     * The timestamp of when this file was created.
     * @var int
     */
    public $time = 0;

    /**
     * The UNIX timestamp of when this file should automatically remove itself (currently unused).
     * @var int
     */
    public $expire = 0;

    /**
     * Create a new file.
     * @param string $data
     * @param string $name
     * @param User $user
     * @param int $expire
     * @return File
     */
    public static function create($data, $name, User $user, $expire = 0)
    {
        // Get the mimetype
        $mime = (new finfo(FILEINFO_MIME_TYPE))->buffer($data);

        // Insert it into the database
        $id = DB::table('uploads')
            ->insertGetId([
                'user_id' => $user->id,
                'file_name' => $name,
                'file_mime' => $mime,
                'file_time' => time(),
                'file_expire' => $expire,
            ]);

        // Save the file data
        file_put_contents(ROOT . config('file.uploads_dir') . $id . ".bin", $data);

        // Return a new File object
        return new File($id);
    }

    /**
     * Constructor.
     * @param int $fileId
     */
    public function __construct($fileId)
    {
        // Attempt to get the database row
        $fileRow = DB::table('uploads')
            ->where('file_id', $fileId)
            ->get();

        // If anything was returned populate the variables
        if ($fileRow) {
            $fileRow = $fileRow[0];
            $this->id = $fileRow->file_id;
            $this->user = User::construct($fileRow->user_id);
            $this->data = file_get_contents(ROOT . config('file.uploads_dir') . $fileRow->file_id . ".bin");
            $this->name = $fileRow->file_name;
            $this->mime = $fileRow->file_mime;
            $this->time = $fileRow->file_time;
            $this->expire = $fileRow->file_expire;
        }
    }

    /**
     * Delete this file from the database.
     */
    public function delete()
    {
        unlink(ROOT . config('file.uploads_dir') . $this->id . ".bin");

        DB::table('uploads')
            ->where('file_id', $this->id)
            ->delete();
    }
}
