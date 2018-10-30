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

/**
 * GithubIssueService
 *
 * @author Gianluca Bine <gian_bine@hotmail.com>
 */
class GithubIssueService
{
    /** @var int */
    const ISSUE_TITLE_LENGTH = 80;

    /** @var SlackApiService */
    private $slackApiService;

    /** @var GithubApiService */
    private $githubApiService;

    /**
     * BotService constructor.
     * @param SlackApiService $slackApiService
     * @param GithubApiService $githubApiService
     */
    public function __construct(SlackApiService $slackApiService, GithubApiService $githubApiService)
    {
        $this->slackApiService = $slackApiService;
        $this->githubApiService = $githubApiService;
    }

    /**
     * @param $channel
     * @param $message
     * @return \GitHubIssue
     */
    public function createIssue($channel, $message)
    {
        $repository = $this->findRepositoryByChannel($channel);
        $title = $this->formatIssueTitle($message);
        $body = $this->formatIssueBody($channel, $message);

        $githubIssue = $this->githubApiService->createIssue(
            $repository,
            $title,
            $body
        );

        $this->notifySlack($githubIssue, $channel, $message);

        return $githubIssue;
    }

    /**
     * @param $channel
     * @param $message
     * @return string
     */
    private function formatIssueBody($channel, $message)
    {
        $issueBody = "";

        $replyCount = intval($message['reply_count']);

        $replies = $this->slackApiService->getMessageReplies($channel["id"], $message["ts"], $replyCount);

        foreach ($replies["messages"] as $reply) {
            $userInfo = $this->slackApiService->getUserInfo($reply["user"]);

            $name = $userInfo["user"]["real_name"];
            $message = $reply["text"];

            $issueBody .= "\n$name: $message\n";

            $files = "";
            if (isset($reply['files'])) {
                foreach ($reply['files'] as $file) {
                    $mimetype = $file['mimetype'];

                    if (str_contains($mimetype, 'image')) {
                        $image = $file['url_private'];
                        $files .= "- $image";
                    } else {
                        $downloadLink = $file['url_private_download'];
                        $files .= "- $downloadLink";
                    }

                    $files .= "\n";
                }
            }

            $issueBody .= $files;
        }

        return $this->stripNonUTF($issueBody);
    }

    /**
     * @param $message
     * @return string
     */
    private function formatIssueTitle($message)
    {
        $issueTitle = $this->stripNonUTF($message["text"]);

        if (strlen($issueTitle) > self::ISSUE_TITLE_LENGTH) {
            return substr($issueTitle, 0, self::ISSUE_TITLE_LENGTH) . "...";
        }

        return $this->stripNonUTF($issueTitle);
    }

    /**
     * @param $channel
     * @return mixed|null
     */
    private function findRepositoryByChannel($channel)
    {
        $matches = [];
        preg_match("/sices-report-(.*)/", $channel["name"], $matches);

        $repository = $matches[1] ?? null;

        return $repository;
    }

    /**
     * @param \GitHubIssue $gitHubIssue
     * @param array $channel
     * @param array $message
     */
    private function notifySlack(\GitHubIssue $gitHubIssue, array $channel, array $message)
    {
        $issueNumber = $gitHubIssue->getNumber();
        $issueUrl = $gitHubIssue->getHtmlUrl();
        $channelName = $channel["name"];
        $notification = "Issue #$issueNumber criada: $issueUrl";
        $threadTs = $message["thread_ts"];

        $this->slackApiService->postMessage($channelName, $notification, $threadTs);
    }

    /**
     * @param $string
     * @return string
     */
    private function stripNonUTF($string){
        $sanitizedString = preg_replace("/\<([^<>]*+|(?R))*\>/","", $string);

        $sanitizedString = str_replace("&amp;", "&", $sanitizedString);

        $sanitizedString = str_replace("&lt;", "<", $sanitizedString);

        $sanitizedString = str_replace("&gt;", ">", $sanitizedString);

        return trim($sanitizedString);
    }
}