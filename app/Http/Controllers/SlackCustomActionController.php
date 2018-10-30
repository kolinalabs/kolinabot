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

use App\Jobs\CreateIssueJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * SlackCustomActionController
 *
 * @author Gianluca Bine <gian_bine@hotmail.com>
 */
class SlackCustomActionController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request)
    {
        $this->validate($request, [
            'payload' => 'required'
        ]);

        $payload = json_decode($request->get('payload'), true);

        $channel = $payload['channel'];
        $message = $payload['message'];
        $action = $payload['callback_id'];

        switch ($action) {
            case "create_issue":
                dispatch(new CreateIssueJob($channel, $message));
                break;
        }

        return JsonResponse::create();
    }
}