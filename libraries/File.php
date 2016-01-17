<?php
/*
 * File handler
 */

namespace Sakura;

use finfo;

/**
 * Class File
 * @package Sakura
 */
class File
{
    // Variables
    public $id = 0;
    public $user = null;
    public $data = null;
    public $name = null;
    public $mime = null;
    public $time = 0;
    public $expire = 0;

    // Create a new file
    public static function create($data, $name, User $user, $expire = 0)
    {
        // Get the mimetype
        $mime = (new finfo(FILEINFO_MIME_TYPE))->buffer($data);

        // Insert it into the database
        Database::insert('uploads', [
            'user_id' => $user->id,
            'file_data' => $data,
            'file_name' => $name,
            'file_mime' => $mime,
            'file_time' => time(),
            'file_expire' => $expire,
        ]);

        // Get the last insert id
        $id = Database::lastInsertID();

        // Return a new File object
        return new File($id);
    }

    // Constructor
    public function __construct($fileId)
    {
        // Attempt to get the database row
        $fileRow = Database::fetch('uploads', false, ['file_id' => [$fileId, '=']]);

        // If anything was returned populate the variables
        if ($fileRow) {
            $this->id = $fileRow['file_id'];
            $this->user = User::construct($fileRow['user_id']);
            $this->data = $fileRow['file_data'];
            $this->name = $fileRow['file_name'];
            $this->mime = $fileRow['file_mime'];
            $this->time = $fileRow['file_time'];
            $this->expire = $fileRow['file_expire'];
        }
    }

    // Delete the file
    public function delete()
    {
        Database::delete('uploads', [
            'file_id' => [$this->id, '='],
        ]);
    }
}
