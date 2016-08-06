<?php
/**
 * Notification object.
 * @package Sakura
 */

namespace Sakura;

/**
 * Notification!
 * @package Sakura
 * @author Julian van de Groep <me@flash.moe>
 */
class Notification
{
    /**
     * The identifier.
     * @var int
     */
    public $id = 0;

    /**
     * The id of the user this notification is intended for.
     * @var int
     */
    public $user = 0;

    /**
     * The timestamp when this notification was created.
     * @var int
     */
    public $time = 0;

    /**
     * Whether the user has already read this notification.
     * @var bool
     */
    public $read = false;

    /**
     * Title of the notification.
     * @var string
     */
    public $title = "Notification";

    /**
     * The rest of the content
     * @var string
     */
    public $text = "";

    /**
     * The url this notification should link to when clicked on.
     * @var string
     */
    public $link = "";

    /**
     * The image url to display.
     * @var string
     */
    public $image = "";

    /**
     * The amount of time this notification should be displayed for
     * @var int
     */
    public $timeout = 0;

    /**
     * The constructor.
     * @param int $id
     */
    public function __construct($id = 0)
    {
        // Get notification data from the database
        $data = DB::table('notifications')
            ->where('alert_id', $id)
            ->first();

        // Check if anything was returned and assign data
        if ($data) {
            $this->id = intval($data->alert_id);
            $this->user = intval($data->user_id);
            $this->time = intval($data->alert_timestamp);
            $this->read = intval($data->alert_read) !== 0;
            $this->title = $data->alert_title;
            $this->text = $data->alert_text;
            $this->link = $data->alert_link;
            $this->image = $data->alert_img;
            $this->timeout = intval($data->alert_timeout);
        }
    }

    /**
     * Saving changes to this notification.
     */
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

    /**
     * Toggle the read status
     */
    public function toggleRead()
    {
        // Set read to the negative value of itself
        $this->read = !$this->read;
    }

    /**
     * Get the user object.
     * @return User
     */
    public function userData()
    {
        return User::construct($this->user);
    }
}
