<?php

namespace App\Exceptions;

/**
 * Exception that a notification instance can throw towards the NotificationController if it thinks
 * that it has become useless (e.g. The like doesn't exist anymore or the other person removed
 * their check-in).
 */
class ShouldDeleteNotificationException extends Referencable
{
}

