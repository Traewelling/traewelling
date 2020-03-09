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
        $NotificationResponse = NotificationBackend::latest();
        return $this->sendResponse($NotificationResponse);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        return NotificationBackend::toggleReadState($id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $deleteNotificationResponse = NotificationBackend::destroy($id);
        return $deleteNotificationResponse;
    }
}
