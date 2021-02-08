<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\NotificationController as NotificationBackend;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NotificationController extends ResponseController
{
    /**
     * List Notifications
     * Display a listing of the resource.
     *
     * @group Notifications
     *
     * @responseFile 200 storage/responses/v0/notifications.get.json
     * @return Response
     */
    public function index() {
        $notificationResponse = NotificationBackend::latest();
        return $this->sendResponse($notificationResponse);
    }

    /**
     * Read/Unread notification
     * sets the current notification to "read"
     *
     * @group Notifications
     *
     * @urlParam notification string required
     * The ID of the to-be-changed notification. Example: 87eed448-6ddc-44bc-97c6-bb4fe2c8d9e0
     * @responseFile 201 scenario="new state = read" storage/responses/v0/notifications.put.json
     * @responseFile 202 scenario="new state = unread" storage/responses/v0/notifications.put.202.json
     * @response 400 scenario="Bad Request The parameters are wrong or not given." <<>>
     * @response 401 scenario="Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied." <<>>
     * @response 404 scenario="Not found The parameters in the request were valid, but the server did not find a corresponding object." <<>>
     * @responseFile status=406 scenario="Not Acceptable The privacy agreement has not yet been accepted." storage/responses/v0/server.406.json
     * @param Request $request
     * @param int $notificationID
     *
     * @return Response
     */
    public function update($notificationID) {
        return NotificationBackend::toggleReadState($notificationID);
    }

    /**
     * Delete the notification
     *
     * @group Notifications
     *
     * @urlParam notification string required
     * The ID of the to-be-deleted notification. Example: 87eed448-6ddc-44bc-97c6-bb4fe2c8d9e0
     * @responseFile status="Ok. Notification has been deleted" storage/responses/v0/notifications.delete.json
     * @response 400 scenario="Bad Request The parameters are wrong or not given." <<>>
     * @response 401 scenario="Unauthorized. Will be returned by the server if no user was logged in or wrong credentials were supplied." <<>>
     * @response 404 scenario="Not found The parameters in the request were valid, but the server did not find a corresponding object." <<>>
     * @responseFile status=406 scenario="Not Acceptable The privacy agreement has not yet been accepted." storage/responses/v0/server.406.json
     *
     * @param int $notificationID
     *
     * @return Response
     */
    public function destroy($notificationID) {
        $deleteNotificationResponse = NotificationBackend::destroy($notificationID);
        return $deleteNotificationResponse;
    }
}
