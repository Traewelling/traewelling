<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\NotificationController as NotificationBackend;
use Illuminate\Http\JsonResponse;

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
     * @param int $notificationID
     *
     * @return JsonResponse
     */
    public function update(int $notificationID) {
        return NotificationBackend::toggleReadState($notificationID);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $notificationID
     *
     * @return JsonResponse
     */
    public function destroy(int $notificationID) {
        return NotificationBackend::destroy($notificationID);
    }
}
