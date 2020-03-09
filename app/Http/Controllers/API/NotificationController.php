<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\NotificationController as NotificationBackend;

class NotificationController extends ResponseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $notificationResponse = NotificationBackend::latest();
        return $this->sendResponse($notificationResponse);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $notificationID
     *
     * @return \Illuminate\Http\Response
     */
    public function update($notificationID)
    {
        return NotificationBackend::toggleReadState($notificationID);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $notificationID
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($notificationID)
    {
        $deleteNotificationResponse = NotificationBackend::destroy($notificationID);
        return $deleteNotificationResponse;
    }
}
