<?php

/**
 * This file is part of the SicesSolar package.
 *
 * (c) SicesSolar <http://sicesbrasil.com.br/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Services;

use wrapi\slack\slack;

/**
 * SlackApiService
 *
 * @author Gianluca Bine <gian_bine@hotmail.com>
 */
class SlackApiService
{
    /** @var slack */
    private $slack;

    public function __construct()
    {
        $token = env("SLACK_API_TOKEN");

        $this->slack = new slack($token);
    }

    /**
     * @return slack
     */
    public function getSlack()
    {
        return $this->slack;
    }

    /**
     * @param $channelId
     * @param $ts
     * @param int $limit
     * @return mixed
     */
    public function getMessageReplies($channelId, $ts, $limit = 10)
    {
        return $this->slack->conversations->replies([
            "channel" => $channelId,
            "ts" => $ts,
            "limit" => $limit
        ]);
    }

    /**
     * @param $userId
     * @return mixed
     */
    public function getUserInfo($userId)
    {
        return $this->slack->users->info([
            "user" => $userId
        ]);
    }

    /**
     * @param $channel
     * @param $notification
     * @param null $threadTs
     * @return mixed
     */
    public function postMessage($channel, $notification, $threadTs = null)
    {
        $params = [
            "channel" => $channel,
            "text" => $notification,
            "username" => "kolinabot",
            "as_user" => false,
            "parse" => "full",
            "link_names" => 1,
            "unfurl_links" => true,
            "unfurl_media" => false,
        ];

        if ($threadTs) {
            $params["thread_ts"] = $threadTs;
        }

        return $this->slack->chat->postMessage($params);
    }

}