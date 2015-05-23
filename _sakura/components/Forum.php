<?php
/*
 * Discussion Board
 */

namespace Sakura;

class Forum {

    // Getting the board list
    public static function getBoardList() {

        // Get the content from the database
        $forums = Database::fetch('forums');

        // Create return array
        $return = [
            0 => [
                'data' => [
                    'forum_id'              => 0,
                    'forum_name'            => 'Forum',
                    'forum_desc'            => '',
                    'forum_link'            => '',
                    'forum_category'        => 0,
                    'forum_type'            => 1,
                    'forum_posts'           => 0,
                    'forum_topics'          => 0,
                    'forum_last_post_id'    => 0,
                    'forum_last_poster_id'  => 0
                ],
                'forums' => []
            ]
        ];

        // Resort the forums
        foreach($forums as $forum) {

            // If the forum type is a category create a new one
            if($forum['forum_type'] == 1)
                $return[$forum['forum_id']]['data'] = $forum;
            else {

                // For link and reg. forum add it to the category
                $return[$forum['forum_category']]['forums'][$forum['forum_id']] = $forum;

                // Add last poster data and the details about the post as well
                $return[$forum['forum_category']]['forums'][$forum['forum_id']]['last_poster_data'] = ($_LAST_POSTER = Users::getUser($forum['forum_last_poster_id']));
                $return[$forum['forum_category']]['forums'][$forum['forum_id']]['last_poster_rank'] = Users::getRank($_LAST_POSTER['rank_main']);

            }

        }

        // Return the resorted data
        return $return;

    }

    // Creating a new post
    public static function createPost($subject, $text, $enableMD, $enableSig, $forum, $topic = 0, $type = 0, $status = 0) {

        // Check if this post is OP
        if(!$topic) {

            // If so create a new topic
            Database::insert('topics', [
                'forum_id'              => $forum,
                'topic_hidden'          => 0,
                'topic_title'           => $subject,
                'topic_time'            => time(),
                'topic_time_limit'      => 0,
                'topic_last_reply'      => 0,
                'topic_views'           => 0,
                'topic_replies'         => 0,
                'topic_status'          => $status,
                'topic_status_change'   => 0,
                'topic_type'            => $type,
                'topic_first_post_id'   => 0,
                'topic_first_poster_id' => Session::$userId
            ]);

        }

    }

}
