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

use GitHubClient;
use GitHubIssue;

/**
 * GithubApiService
 *
 * @author Gianluca Bine <gian_bine@hotmail.com>
 */
class GithubApiService
{
    /** @var GitHubClient */
    private $github;

    /**
     * GithubApiService constructor.
     * @throws \GitHubClientException
     */
    public function __construct()
    {
        $this->github = new GitHubClient();

        $this->github->setCredentials(env('GITHUB_USERNAME'), env('GITHUB_PASSWORD'));
    }

    /**
     * @param GitHubClient $github
     */
    public function setGithub(GitHubClient $github): void
    {
        $this->github = $github;
    }

    /**
     * @param string $repository
     * @param string $title
     * @param string $body
     * @return GitHubIssue
     */
    public function createIssue(string $repository, string $title, string $body)
    {
        return $this->github->issues->createAnIssue(
            env('GITHUB_REPOSITORY_OWNER'),
            $repository,
            $title,
            $body
        );
    }
}