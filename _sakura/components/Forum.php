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

        // Get the topics from the database
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

    // Get posts of a thread
    public static function getTopic($id) {

        // Get the topic data from the database
        $topicInfo = Database::fetch('topics', false, [
            'topic_id' => [$id, '=']
        ]);

        // Check if there actually is anything
        if(empty($topicInfo))
            return false;

        // Get the posts from the database
        $rawPosts = Database::fetch('posts', true, [
            'topic_id' => [$id, '=']
        ]);

        // Create storage array
        $topic = [];

        // Add forum data
        $topic['forum'] = self::getForum($topicInfo['forum_id']);

        // Store the topic info
        $topic['topic'] = $topicInfo;

        // Get the data of the first poster
        $topic['topic']['first_poster'] = [
            'user' => ($_FIRST_POSTER = Users::getUser($topic['topic']['topic_first_poster_id'])),
            'rank' => Users::getRank($_FIRST_POSTER['rank_main'])
        ];

        // Get the data of the last poster
        $topic['topic']['last_poster'] = [
            'user' => ($_LAST_POSTER = Users::getUser($topic['topic']['topic_last_poster_id'])),
            'rank' => Users::getRank($_LAST_POSTER['rank_main'])
        ];

        // Create space for posts
        $topic['posts'] = [];

        // Parse the data of every post
        foreach($rawPosts as $post) {

            // Add post and metadata to the global storage array
            $topic['posts'][$post['post_id']] = array_merge($post, [
                'is_op'         => ($post['poster_id'] == $topic['topic']['topic_first_poster_id'] ? '1' : '0'),
                'user'          => ($_POSTER = Users::getUser($post['poster_id'])),
                'rank'          => Users::getRank($_POSTER['rank_main']),
                'country'       => Main::getCountryName($_POSTER['country']),
                'is_tenshi'     => Users::checkUserTenshi($_POSTER['id']),
                'is_online'     => Users::checkUserOnline($_POSTER['id']),
                'is_friend'     => Users::checkFriend($_POSTER['id']),
                'parsed_post'   => self::parseMarkUp($post['post_text'], $post['parse_mode']),
                'signature'     => empty($_POSTER['userData']['signature']) ? '' : self::parseMarkUp($_POSTER['userData']['signature']['text'], $_POSTER['userData']['signature']['mode'])
            ]);

            // Just in case
            unset($_POSTER);

        }

        // Return the compiled topic data
        return $topic;

    }

    // Get a topic ID from a post ID
    public static function getTopicIdFromPostId($id) {

        // Get the post
        $post = Database::fetch('posts', false, [
            'post_id' => [$id, '=']
        ]);

        // Return false if nothing was returned
        if(empty($post))
            return false;

        // Return the topic id
        return $post['topic_id'];

    }

    // Parse different markup flavours
    public static function parseMarkUp($text, $mode) {

        // Clean string
        $text = Main::cleanString($text);

        // Switch between modes
        switch($mode) {

            case 1:
                return Main::bbParse($text);
 
            case 2:
                return Main::mdParse($text);

            case 0:
            default:
                return $text;

        }

    }

    // Creating a new post
    public static function createPost($subject, $text, $enableMD, $enableSig, $forum, $type = 0, $status = 0, $topic = 0) {

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
