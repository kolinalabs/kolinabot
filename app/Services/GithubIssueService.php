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

        //$this->notifySlack($githubIssue, $channel);

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

        $replies = $this->slackApiService->getMessageReplies($channel['id'], $message['ts']);

        foreach ($replies['messages'] as $reply) {
            $userInfo = $this->slackApiService->getUserInfo($reply['user']);

            $name = $userInfo['user']['real_name'];
            $message = $reply['text'];

            $issueBody .= "\n$name: $message\n";
        }

        return $issueBody;
    }

    /**
     * @param $message
     * @return string
     */
    private function formatIssueTitle($message)
    {
        $issueTitle = $message['text'];

        if (strlen($issueTitle) > self::ISSUE_TITLE_LENGTH) {
            return substr($issueTitle, 0, self::ISSUE_TITLE_LENGTH) . "...";
        }

        return $issueTitle;
    }

    /**
     * @param $channel
     * @return mixed|null
     */
    private function findRepositoryByChannel($channel)
    {
        $matches = [];
        preg_match("/sices-report-(.*)/", $channel['name'], $matches);

        $repository = $matches[1] ?? null;

        return $repository;
    }

    /**
     * @param \GitHubIssue $gitHubIssue
     * @param $channel
     */
    private function notifySlack(\GitHubIssue $gitHubIssue, $channel)
    {
        if (!$gitHubIssue) {
            return;
        }

        $issueNumber = $gitHubIssue->getNumber();
        $issueUrl = $gitHubIssue->getHtmlUrl();
        $channelName = $channel['name'];
        $message = "Issue #$issueNumber criada: $issueUrl";

        $this->slackApiService->postMessage($channelName, $message);
    }
}