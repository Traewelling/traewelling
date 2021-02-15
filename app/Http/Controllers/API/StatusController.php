<?php

namespace App\Http\Controllers\API;

use App\Exceptions\StatusAlreadyLikedException;
use App\Http\Controllers\StatusController as StatusBackend;
use App\Http\Controllers\UserController as UserBackend;
use App\Models\Event;
use App\Models\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StatusController extends ResponseController
{
    /**
     * Show active statuses
     * Returns all statuses of currently active trains
     *
     * @group Statuses
     * @responseFile status=200 storage/responses/v0/statuses.enroute.get.json
     * @response 401 scenario="Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied." <<>>
     * @responseFile status=406 scenario="Not Acceptable The privacy agreement has not yet been accepted." storage/responses/v0/server.406.json
     * @return \Illuminate\Http\JsonResponse
     */
    public function enRoute() {
        $activeStatusesResponse = StatusBackend::getActiveStatuses();
        $response               = [];
        if ($activeStatusesResponse['statuses'] !== null) {
            $response = [
                'statuses'  => $activeStatusesResponse['statuses'],
                'polylines' => $activeStatusesResponse['polylines']
            ];
        }
        return $this->sendResponse($response);
    }

    /**
     * Event-Statuses
     * Displays all statuses concerning a specific event as a paginated object.
     *
     * @group Statuses
     * @urlParam eventID string required the slug of the event
     * @responseFile status=200 storage/responses/v0/statuses.event.get.
     * @response 400 scenario="Bad Request The parameters are wrong or not given." <<>>
     * @response 401 scenario="Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied." <<>>
     * @response 404 scenario="Not found The parameters in the request were valid, but the server did not find a corresponding object." <<>>
     * @responseFile status=406 scenario="Not Acceptable The privacy agreement has not yet been accepted." storage/responses/v0/server.406.json
     *
     * @param $eventID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByEvent(int $eventID): JsonResponse {
        $eventStatusResponse = Event::findOrFail($eventID)->statuses()
                                    ->simplePaginate(15);
        return $this->sendResponse($eventStatusResponse);
    }

    /**
     * Dashboard & User-statuses
     * Retrieves either the (global) dashboard for the logged in user or all statuses of a specified user
     *
     * @group Statuses
     * @queryParam view string
     * Filters the list of statuses so that it will either return the global view
     * (i.e. all public statuses == global dashboard), just the statuses of the current user’s follows
     * (i.e. the user’s dashboard). Can be user,global or personal. Example: user
     * @queryParam username string Only required if view is set to user. Example: gertrud123
     * @queryParam page int Needed to display the specified page
     * @responseFile status=200 storage/responses/v0/statuses.get.json
     * @response 400 scenario="Bad Request The parameters are wrong or not given." <<>>
     * @response 401 scenario="Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied." <<>>
     * @responseFile status=406 scenario="Not Acceptable The privacy agreement has not yet been accepted." storage/responses/v0/server.406.json
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {
        $validator = Validator::make($request->all(), [
            'username'    => 'string|required_if:view,user',
            'view'        => 'in:user,global,personal'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }

        $view = 'global';
        if (!empty($request->view)) {
            $view = $request->view;
        }
        $statuses = ['statuses' => ''];
        if ($view === 'global') {
            $statuses['statuses'] = StatusBackend::getGlobalDashboard();
        }
        if ($view === 'personal') {
            $statuses['statuses'] = StatusBackend::getDashboard(Auth::user());
        }
        if ($view === 'user') {
            $statuses = UserBackend::getProfilePage($request->username);
        }
        return response()->json($statuses['statuses']);
    }

    /**
     * Retrieve Status
     * Retrieves a single status.
     *
     * @group Statuses
     * @urlParam statusId int required The id of a status.
     * @responseFile storage/responses/v0/statuses.single.get.json
     * @response 400 scenario="Bad Request The parameters are wrong or not given." <<>>
     * @response 401 scenario="Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied." <<>>
     * @response 404 scenario="Not found The parameters in the request were valid, but the server did not find a corresponding object." <<>>
     * @responseFile status=406 scenario="Not Acceptable The privacy agreement has not yet been accepted." storage/responses/v0/server.406.json
     *
     * @param $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($status) {
        $statusResponse = StatusBackend::getStatus($status);
        return $this->sendResponse($statusResponse);
    }

    /**
     * Update status
     * Updates the status text that a user previously posted
     *
     * @group Statuses
     * @urlParam status int required ID of the status
     * @bodyParam {} string New body of the status. Example: This is an updated status body! 🥳
     * ToDo: This accepts plaintext as body, not a key=>value pair.
     * @response status=200 scenario="The status object has been modified on the server (i.e. the status text was changed). The response contains the modified version of the status." {"This is an updated status body! 🥳"}
     * @response 400 scenario="Bad Request The parameters are wrong or not given." <<>>
     * @response 401 scenario="Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied." <<>>
     * @response 403 scenario="Forbidden The logged in user is not permitted to perform this action. (e.g. edit a status of another user.)" <<>>
     * @response 404 scenario="Not found The parameters in the request were valid, but the server did not find a corresponding object." <<>>
     * @responseFile status=406 scenario="Not Acceptable The privacy agreement has not yet been accepted." storage/responses/v0/server.406.json
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request) {
        $validator = Validator::make($request->all(), [
            'body'     => 'max:280',
            'business' => 'integer',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }
        $editStatusResponse = StatusBackend::EditStatus(
            Auth::user(),
            $request['statusId'],
            $request['body'],
            $request['businessCheck']
        );
        if ($editStatusResponse === null) {
            return $this->sendError('Not found');
        }
        if ($editStatusResponse === false) {
            return $this->sendError(__('controller.status.not-permitted'), 403);
        }
        return $this->sendResponse(['newBody' => $editStatusResponse]);
    }

    /**
     * Destroy status
     * Removes a status that a user has posted previously.
     *
     * @group Statuses
     * @urlParam status int required ID of the status
     * @response status=204 scenario="No content. The status with the given ID has been deleted. Nothing further needs to be said, so the response will not have any content." <<>>
     * @response 400 scenario="Bad Request The parameters are wrong or not given." <<>>
     * @response 401 scenario="Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied." <<>>
     * @response 404 scenario="Not found The parameters in the request were valid, but the server did not find a corresponding object." <<>>
     * @responseFile status=406 scenario="Not Acceptable The privacy agreement has not yet been accepted." storage/responses/v0/server.406.json
     *
     * @param $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($status) {
        $deleteStatusResponse = StatusBackend::DeleteStatus(Auth::user(), $status);

        if ($deleteStatusResponse === null) {
            return $this->sendError('Not found');
        }
        if ($deleteStatusResponse === false) {
            return $this->sendError(__('controller.status.not-permitted'), 403);
        }
        return $this->sendResponse(__('controller.status.delete-ok'));
    }

    /**
     * Like a Status
     * Creates a like for a given status
     *
     * @group Statuses
     * @urlParam statusId int required id for the to-be-liked status
     * @response 200 scenario="Like successfully created" <<true>>
     * @response 400 scenario="Bad Request The parameters are wrong or not given." <<>>
     * @response 401 scenario="Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied." <<>>
     * @response 403 scenario="Forbidden The logged in user is not permitted to perform this action. (e.g. edit a status of another user.)" <<>>
     * @response 404 scenario="Not found The parameters in the request were valid, but the server did not find a corresponding object." <<>>
     * @responseFile status=406 scenario="Not Acceptable The privacy agreement has not yet been accepted." storage/responses/v0/server.406.json
     *
     * @param $statusId
     * @return \Illuminate\Http\JsonResponse
     */
    public function createLike($statusId) {
        $status = Status::find($statusId);
        if ($status == null) {
            return $this->sendError(false, 404);
        }
        try {
            StatusBackend::createLike(Auth::user(), $status);
            return $this->sendResponse(true);
        } catch (StatusAlreadyLikedException $e) {
            return $this->sendError(false, 400);
        }

    }

    /**
     * Unlike a Status
     * Removes a like for a given status
     *
     * @group Statuses
     * @urlParam statusId int required id for the to-be-unliked status
     * @response 200 scenario="Like successfully destroyed" <<true>>
     * @response 400 scenario="Bad Request The parameters are wrong or not given." <<>>
     * @response 401 scenario="Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied." <<>>
     * @response 403 scenario="Forbidden The logged in user is not permitted to perform this action. (e.g. edit a status of another user.)" <<>>
     * @response 404 scenario="Not found The parameters in the request were valid, but the server did not find a corresponding object." <<>>
     * @responseFile status=406 scenario="Not Acceptable The privacy agreement has not yet been accepted." storage/responses/v0/server.406.json
     *
     * @param $statusId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyLike($statusId) {
        $destroyLikeResponse = StatusBackend::DestroyLike(Auth::user(), $statusId);

        return $this->sendResponse($destroyLikeResponse);
    }

    /**
     * Retrieve Likes
     * Retrieves all likes for a status
     *
     * @group Statuses
     * @urlParam statusId int required
     * @queryParam page int Needed to display the specified page
     * @responseFile status=200 storage/responses/v0/statuses.likes.get.json
     * @response 400 scenario="Bad Request The parameters are wrong or not given." <<>>
     * @response 401 scenario="Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied." <<>>
     * @response 404 scenario="Not found The parameters in the request were valid, but the server did not find a corresponding object." <<>>
     * @responseFile status=406 scenario="Not Acceptable The privacy agreement has not yet been accepted." storage/responses/v0/server.406.json
     *
     * @param $statusId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLikes($statusId) {
        $getLikesResponse = StatusBackend::getLikes($statusId);

        return $this->sendResponse($getLikesResponse);
    }
}
