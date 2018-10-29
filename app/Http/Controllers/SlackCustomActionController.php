<?php

/**
 * This file is part of the SicesSolar package.
 *
 * (c) SicesSolar <http://sicesbrasil.com.br/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Http\Controllers;

use App\Services\GithubIssueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * SlackCustomActionController
 *
 * @author Gianluca Bine <gian_bine@hotmail.com>
 */
class SlackCustomActionController extends Controller
{
    /** @var GithubIssueService */
    private $githubIssueService;

    /**
     * CreateIssueController constructor.
     * @param GithubIssueService $githubIssueService
     */
    public function __construct(GithubIssueService $githubIssueService)
    {
        $this->githubIssueService = $githubIssueService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request)
    {
        $this->validate($request, [
            'token' => 'required',
            'callback_id' => 'required',
            'type' => 'required',
            'response_url' => 'required',
            'team' => 'required',
            'user' => 'required',
            'channel' => 'required',
            'message' => 'required'
        ]);

        $channel = $request->get('channel');
        $message = $request->get('message');
        $action = $request->get('callback_id');


        switch ($action) {
            case "create_issue":
                $githubIssue = $this->githubIssueService->createIssue($channel, $message);

                if ($githubIssue) {
                    return JsonResponse::create((array) $githubIssue);
                }
                break;
        }

        return JsonResponse::create([
            'message' => 'Undefined action'
        ], Response::HTTP_NOT_FOUND);
    }
}