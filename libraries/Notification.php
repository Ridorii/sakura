<?php
/**
 * Notification object.
 *
 * @package Sakura
 */

namespace Sakura;

/**
 * Notification!
 *
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Notification
{
    public $id = 0;
    public $user = 0;
    public $time = 0;
    public $read = false;
    public $title = "Notification";
    public $text = "";
    public $link = "";
    public $image = "";
    public $timeout = 0;

    public function __construct($id = 0)
    {
        // Get notification data from the database
        $data = DB::table('notifications')
            ->where('alert_id', $id)
            ->get();

        // Check if anything was returned and assign data
        if ($data) {
            $data = $data[0];

            $this->id = $data->alert_id;
            $this->user = $data->user_id;
            $this->time = $data->alert_timestamp;
            $this->read = intval($data->alert_read) !== 0;
            $this->title = $data->alert_title;
            $this->text = $data->alert_text;
            $this->link = $data->alert_link;
            $this->image = $data->alert_img;
            $this->timeout = $data->alert_timeout;
        }
    }

    public function save()
    {
        // Create submission data, insert and update take the same format
        $data = [
            'user_id' => $this->user,
            'alert_timestamp' => $this->time,
            'alert_read' => $this->read ? 1 : 0,
            'alert_title' => $this->title,
            'alert_text' => $this->text,
            'alert_link' => $this->link,
            'alert_img' => $this->image,
            'alert_timeout' => $this->timeout,
        ];

        // Update if id isn't 0
        if ($this->id) {
            DB::table('notifications')
                ->where('alert_id', $this->id)
                ->update($data);
        } else {
            $this->id = DB::table('notifications')
                ->insertGetId($data);
        }
    }

    public function delete()
    {
        DB::table('comments')
            ->where('comment_id', $this->id)
            ->delete();

        $this->id = 0;
    }

    public function toggleRead()
    {
        // Set read to the negative value of itself
        $this->read = !$this->read;
    }

    public function userData()
    {
        return User::construct($this->user);
    }
}
