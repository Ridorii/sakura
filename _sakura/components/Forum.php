<?php
/*
 * Discussion Board
 */

namespace Sakura;

class Forum
{
    // Empty forum template
    public static $emptyForum = [
        'forum_id' => 0,
        'forum_name' => 'Forum',
        'forum_desc' => '',
        'forum_link' => '',
        'forum_category' => 0,
        'forum_type' => 1,
        'forum_posts' => 0,
        'forum_topics' => 0,
    ];

    // Getting the forum list
    public static function getForumList()
    {

        // Get the content from the database
        $forums = Database::fetch('forums');

        // Create return array
        $return = [
            0 => [
                'forum' => self::$emptyForum,
                'forums' => [],
            ],
        ];

        // Resort the forums
        foreach ($forums as $forum) {
            // If the forum type is a category create a new one
            if ($forum['forum_type'] == 1) {
                $return[$forum['forum_id']]['forum'] = $forum;
            } else {
                // For link and reg. forum add it to the category
                $return[$forum['forum_category']]['forums'][$forum['forum_id']] = $forum;

                // Get the topic count
                $return[$forum['forum_category']]['forums'][$forum['forum_id']]['topic_count'] =
                Database::count('topics', [
                    'forum_id' => [$forum['forum_id'], '='],
                ])[0];

                // Get the post count
                $return[$forum['forum_category']]['forums'][$forum['forum_id']]['post_count'] =
                Database::count('posts', [
                    'forum_id' => [$forum['forum_id'], '='],
                ])[0];

                // Get last post in forum
                $lastPost = Database::fetch('posts', false, [
                    'forum_id' => [$forum['forum_id'], '='],
                ], ['post_id', true]);

                // Add last poster data and the details about the post as well
                $return[$forum['forum_category']]['forums'][$forum['forum_id']]['last_poster'] = [
                    'post' => $lastPost,
                    'user' => ($_LAST_POSTER = Users::getUser($lastPost['poster_id'])),
                    'rank' => Users::getRank($_LAST_POSTER['rank_main']),
                    'elap' => Main::timeElapsed($lastPost['post_time']),
                ];
            }
        }

        // Return the resorted data
        return $return;

    }

    // Get a forum or category
    public static function getForum($id)
    {

        // Get the forumlist from the database
        $forums = Database::fetch('forums');

        // Sneak the template in the array
        $forums['fb'] = self::$emptyForum;

        // Create an array to store the forum once we found it
        $forum = [];

        // Try to find the requested forum
        foreach ($forums as $list) {
            // Once found set $forum to $list and break the loop
            if ($list['forum_id'] == $id) {
                $forum['forum'] = $list;
                break;
            }
        }

        // If $forum is still empty after the foreach return false
        if (empty($forum)) {
            return false;
        }

        // Create conditions for fetching the forums
        $conditions['forum_category'] = [$id, '='];

        // If the current category is 0 (the built in fallback) prevent getting categories
        if ($id == 0) {
            $conditions['forum_type'] = ['1', '!='];
        }

        // Check if this forum/category has any subforums
        $forum['forums'] = Database::fetch('forums', true, $conditions);

        // Get the userdata related to last posts
        foreach ($forum['forums'] as $key => $sub) {
            // Get last post in forum
            $lastPost = Database::fetch('posts', false, [
                'forum_id' => [$sub['forum_id'], '='],
            ], ['post_id', true]);

            $forum['forums'][$key]['last_poster'] = [
                'post' => $lastPost,
                'user' => ($lastPoster = Users::getUser($lastPost['poster_id'])),
                'rank' => Users::getRank($lastPoster['rank_main']),
            ];
        }

        // Lastly grab the topics for this forum
        $forum['topics'] = self::getTopics($forum['forum']['forum_id']);

        // Return the forum/category
        return $forum;

    }

    // Getting all topics from a forum
    public static function getTopics($id)
    {

        // Get the topics from the database
        $topics = Database::fetch('topics', true, [
            'forum_id' => [$id, '='],
        ]);

        // Get the userdata related to last posts
        foreach ($topics as $key => $topic) {
            // Get the reply count
            $topics[$key]['reply_count'] = Database::count('posts', [
                'topic_id' => [$topic['topic_id'], '='],
            ])[0];

            // Get first post in topics
            $firstPost = Database::fetch('posts', false, [
                'topic_id' => [$topic['topic_id'], '='],
            ]);

            $topics[$key]['first_poster'] = [
                'post' => $firstPost,
                'user' => ($_FIRST_POSTER = Users::getUser($firstPost['poster_id'])),
                'rank' => Users::getRank($_FIRST_POSTER['rank_main']),
                'elap' => Main::timeElapsed($firstPost['post_time']),
            ];

            // Get last post in topics
            $lastPost = Database::fetch('posts', false, [
                'topic_id' => [$topic['topic_id'], '='],
            ], ['post_id', true]);

            $topics[$key]['last_poster'] = [
                'post' => $lastPost,
                'user' => ($_LAST_POSTER = Users::getUser($lastPost['poster_id'])),
                'rank' => Users::getRank($_LAST_POSTER['rank_main']),
                'elap' => Main::timeElapsed($lastPost['post_time']),
            ];
        }

        return $topics;

    }

    // Get posts of a thread
    public static function getTopic($id, $ignoreView = false)
    {

        // Get the topic data from the database
        $topicInfo = Database::fetch('topics', false, [
            'topic_id' => [$id, '='],
        ]);

        // Check if there actually is anything
        if (empty($topicInfo)) {
            return false;
        }

        // Up the view count
        if (!$ignoreView) {
            // Get the new count
            $topicInfo['topic_views'] = $topicInfo['topic_views'] + 1;

            // Update the count
            Database::update('topics', [
                [
                    'topic_views' => $topicInfo['topic_views'],
                ],
                [
                    'topic_id' => [$id, '='],
                ],
            ]);
        }

        // Get the posts from the database
        $rawPosts = Database::fetch('posts', true, [
            'topic_id' => [$id, '='],
        ]);

        // Create storage array
        $topic = [];

        // Add forum data
        $topic['forum'] = self::getForum($topicInfo['forum_id']);

        // Store the topic info
        $topic['topic'] = $topicInfo;

        // Get first post in topics
        $firstPost = Database::fetch('posts', false, [
            'topic_id' => [$topic['topic']['topic_id'], '='],
        ]);

        // Get the data of the first poster
        $topic['topic']['first_poster'] = [
            'post' => $firstPost,
            'user' => ($_FIRST_POSTER = Users::getUser($firstPost['poster_id'])),
            'rank' => Users::getRank($_FIRST_POSTER['rank_main']),
            'elap' => Main::timeElapsed($firstPost['post_time']),
        ];

        // Get last post in topics
        $lastPost = Database::fetch('posts', false, [
            'topic_id' => [$topic['topic']['topic_id'], '='],
        ], ['post_id', true]);

        // Get the data of the last poster
        $topic['topic']['last_poster'] = [
            'post' => $lastPost,
            'user' => ($_LAST_POSTER = Users::getUser($lastPost['poster_id'])),
            'rank' => Users::getRank($_LAST_POSTER['rank_main']),
            'elap' => Main::timeElapsed($lastPost['post_time']),
        ];

        // Create space for posts
        $topic['posts'] = [];

        // Parse the data of every post
        foreach ($rawPosts as $post) {
            // Add post and metadata to the global storage array
            $topic['posts'][$post['post_id']] = array_merge($post, [
                'is_op' => ($post['poster_id'] == $firstPost['poster_id'] ? '1' : '0'),
                'user' => ($_POSTER = Users::getUser($post['poster_id'])),
                'rank' => Users::getRank($_POSTER['rank_main']),
                'time_elapsed' => Main::timeElapsed($post['post_time']),
                'country' => Main::getCountryName($_POSTER['country']),
                'is_premium' => Users::checkUserPremium($_POSTER['id'])[0],
                'is_online' => Users::checkUserOnline($_POSTER['id']),
                'is_friend' => Users::checkFriend($_POSTER['id']),
                'parsed_post' => self::parseMarkUp($post['post_text'], $post['parse_mode'], $post['enable_emotes']),
                'signature' => empty($_POSTER['userData']['signature']) ?
                '' :
                self::parseMarkUp(
                    $_POSTER['userData']['signature']['text'],
                    $_POSTER['userData']['signature']['mode']
                ),
            ]);

            // Just in case
            unset($_POSTER);
        }

        // Return the compiled topic data
        return $topic;

    }

    // Get a topic ID from a post ID
    public static function getTopicIdFromPostId($id)
    {

        // Get the post
        $post = Database::fetch('posts', false, [
            'post_id' => [$id, '='],
        ]);

        // Return false if nothing was returned
        if (empty($post)) {
            return false;
        }

        // Return the topic id
        return $post['topic_id'];

    }

    // Parse different markup flavours
    public static function parseMarkUp($text, $mode, $emotes = 1)
    {

        // Clean string
        $text = Main::cleanString($text);

        // Parse emotes
        if ($emotes) {
            $text = Main::parseEmotes($text);
        }

        // Switch between modes
        switch ($mode) {
            case 1:
                return Main::bbParse($text);

            case 2:
                return Main::mdParse($text);

            case 0:
            default:
                return $text;
        }

    }

    // Get forum statistics of a user
    public static function getUserStats($uid)
    {

        // Collect the stats
        return [
            'posts' => Database::count(
                'posts',
                ['poster_id' => [$uid, '=']]
            )[0],
            'topics' => Database::count(
                'posts',
                true,
                ['poster_id' => [$uid, '=']],
                ['post_time'],
                null,
                ['topic_id']
            )[0],
        ];

    }

    // Creating a new post
    public static function createPost($subject, $text, $enableMD, $enableSig, $forum, $type = 0, $status = 0, $topic = 0)
    {

        // Check if this post is OP
        if (!$topic) {
            // If so create a new topic
            Database::insert('topics', [
                'forum_id' => $forum,
                'topic_hidden' => 0,
                'topic_title' => $subject,
                'topic_time' => time(),
                'topic_time_limit' => 0,
                'topic_last_reply' => 0,
                'topic_views' => 0,
                'topic_replies' => 0,
                'topic_status' => $status,
                'topic_status_change' => 0,
                'topic_type' => $type,
                'topic_first_post_id' => 0,
                'topic_first_poster_id' => Session::$userId,
            ]);
        }

    }
}
