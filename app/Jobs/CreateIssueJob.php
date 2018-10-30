<?php

/**
 * This file is part of the SicesSolar package.
 *
 * (c) SicesSolar <http://sicesbrasil.com.br/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Jobs;

use App\Services\GithubIssueService;

/**
 * CreateIssueJob
 *
 * @author Gianluca Bine <gian_bine@hotmail.com>
 */
class CreateIssueJob extends Job
{
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 2;

    /** @var array */
    private $channel;

    /** @var array */
    private $message;

    /**
     * CreateIssueJob constructor.
     * @param array $channel
     * @param array $message
     */
    public function __construct(array $channel, array $message)
    {
        $this->channel = $channel;
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(GithubIssueService $githubIssueService)
    {
        $githubIssueService->createIssue($this->channel, $this->message);
    }
}