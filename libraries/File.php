<?php
/**
 * Holds the file server.
 * 
 * @package Sakura
 */

namespace Sakura;

use finfo;

/**
 * Used for storing files served through Sakura.
 * 
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
     * 
     * @var User
     */
    public $user = null;

    /**
     * Data of the file.
     * 
     * @var string
     */
    public $data = null;

    /**
     * Original filename of the file.
     * 
     * @var string
     */
    public $name = null;

    /**
     * Mime type of the file.
     * 
     * @var string
     */
    public $mime = null;

    /**
     * The UNIX timestamp of when this file was created.
     * 
     * @var int
     */
    public $time = 0;

    /**
     * The UNIX timestamp of when this file should automatically remove itself (currently unused).
     * 
     * @var int
     */
    public $expire = 0;

    /**
     * Create a new file.
     * 
     * @param string $data Contents of the file.
     * @param string $name Name of the file.
     * @param User $user User instance of the user creating this file.
     * @param int $expire UNIX timestamp of when this file should automatically remove itself.
     * 
     * @return File The created file instance for the file.
     */
    public static function create($data, $name, User $user, $expire = 0)
    {
        // Get the mimetype
        $mime = (new finfo(FILEINFO_MIME_TYPE))->buffer($data);

        // Insert it into the database
        DB::prepare('INSERT INTO `{prefix}uploads` (`user_id`, `file_data`, `file_name`, `file_mime`, `file_time`, `file_expire`) VALUES (:id, :data, :name, :mime, :time, :expire)')
            ->execute([
            'id' => $user->id,
            'data' => $data,
            'name' => $name,
            'mime' => $mime,
            'time' => time(),
            'expire' => $expire,
        ]);

        // Get the last insert id
        $id = (int) DB::lastID();

        // Return a new File object
        return new File($id);
    }

    /**
     * Constructor.
     * 
     * @param int $fileId ID of the file that should be constructed.
     */
    public function __construct($fileId)
    {
        // Attempt to get the database row
        $fr = DB::prepare('SELECT * FROM `{prefix}uploads` WHERE `file_id` = :id');
        $fr->execute([
            'id' => $fileId,
        ]);
        $fileRow = $fr->fetch();

        // If anything was returned populate the variables
        if ($fileRow) {
            $this->id = $fileRow->file_id;
            $this->user = User::construct($fileRow->user_id);
            $this->data = $fileRow->file_data;
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
        DB::prepare('DELETE FROM `{prefix}uploads` WHERE `file_id` = :id')
            ->execute([
            'id' => $this->id,
        ]);
    }
}
