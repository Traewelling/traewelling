<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\NotificationController as NotificationBackend;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NotificationController extends ResponseController
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index() {
        $notificationResponse = NotificationBackend::latest();
        return $this->sendResponse($notificationResponse);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $notificationID
     *
     * @return Response
     */
    public function update($notificationID) {
        return NotificationBackend::toggleReadState($notificationID);
    }

    /**
     * Remove the specified resource from storage.
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
