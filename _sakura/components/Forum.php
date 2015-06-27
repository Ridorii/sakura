<?php
/*
 * Discussion Board
 */

namespace Sakura;

class Forum {

    // Empty forum template
    public static $emptyForum = [
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
    ];

    // Getting the forum list
    public static function getForumList() {

        // Get the content from the database
        $forums = Database::fetch('forums');

        // Create return array
        $return = [
            0 => [
                'forum' => self::$emptyForum,
                'forums' => []
            ]
        ];

        // Resort the forums
        foreach($forums as $forum) {

            // If the forum type is a category create a new one
            if($forum['forum_type'] == 1) {

                $return[$forum['forum_id']]['forum'] = $forum;

            } else {

                // For link and reg. forum add it to the category
                $return[$forum['forum_category']]['forums'][$forum['forum_id']] = $forum;

                // Add last poster data and the details about the post as well
                $return[$forum['forum_category']]['forums'][$forum['forum_id']]['last_poster'] = [
                    'user' => ($_LAST_POSTER = Users::getUser($forum['forum_last_poster_id'])),
                    'rank' => Users::getRank($_LAST_POSTER['rank_main'])
                ];

            }

        }

        // Return the resorted data
        return $return;

    }

    // Get a forum or category
    public static function getForum($id) {

        // Get the forumlist from the database
        $forums = Database::fetch('forums');

        // Sneak the template in the array
        $forums['fb'] = self::$emptyForum;

        // Create an array to store the forum once we found it
        $forum = [];

        // Try to find the requested forum
        foreach($forums as $list) {

            // Once found set $forum to $list and break the loop
            if($list['forum_id'] == $id) {

                $forum['forum'] = $list;
                break;

            }

        }

        // If $forum is still empty after the foreach return false
        if(empty($forum))
            return false;

        // Create conditions for fetching the forums
        $conditions['forum_category'] = [$id, '='];

        // If the current category is 0 (the built in fallback) prevent getting categories
        if($id == 0)
            $conditions['forum_type'] = ['1', '!='];

        // Check if this forum/category has any subforums
        $forum['forums'] = Database::fetch('forums', true, $conditions);

        // Get the userdata related to last posts
        foreach($forum['forums'] as $key => $sub) {

            $forum['forums'][$key]['last_poster'] = [
                'user' => ($_LAST_POSTER = Users::getUser($sub['forum_last_poster_id'])),
                'rank' => Users::getRank($_LAST_POSTER['rank_main'])
            ];

        }

        // Lastly grab the topics for this forum
        $forum['topics'] = Database::fetch('topics', true, [
            'forum_id' => [$id, '=']
        ]);

        // Get the userdata related to first and last posts
        foreach($forum['topics'] as $key => $topic) {

            $forum['topics'][$key]['first_poster'] = [
                'user' => ($_FIRST_POSTER = Users::getUser($topic['topic_first_poster_id'])),
                'rank' => Users::getRank($_FIRST_POSTER['rank_main'])
            ];

            $forum['topics'][$key]['last_poster'] = [
                'user' => ($_LAST_POSTER = Users::getUser($topic['topic_last_poster_id'])),
                'rank' => Users::getRank($_LAST_POSTER['rank_main'])
            ];

        }

        // Return the forum/category
        return $forum;

    }

    // Getting all topics from a forum
    public static function getTopics($id) {

        $topics = Database::fetch('topics', true, [
            'forum_id' => [$id, '=']
        ]);

        // Get the userdata related to last posts
        foreach($topics as $key => $topic) {

            $topics[$key]['first_poster'] = [
                'user' => ($_FIRST_POSTER = Users::getUser($topic['topic_first_poster_id'])),
                'rank' => Users::getRank($_FIRST_POSTER['rank_main'])
            ];

            $topics[$key]['last_poster'] = [
                'user' => ($_LAST_POSTER = Users::getUser($topic['topic_last_poster_id'])),
                'rank' => Users::getRank($_LAST_POSTER['rank_main'])
            ];

        }

        return $topics;

    }

    // Creating a new post
    public static function createPost($subject, $text, $enableMD, $enableSig, $forum, $type = 0, $status = 0) {

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
