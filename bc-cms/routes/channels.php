<?php
    use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('user-channel-{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('booking-{id}', function ($user, $id) {
    $booking = \Modules\Booking\Models\Booking::find($id);
    if (!$booking) {
        return false;
    }
    
    // Создатель брони может слушать
    $creatorId = $booking->create_user ?? $booking->customer_id;
    if ($user->id == $creatorId) {
        return true;
    }
    
    // Вендор может слушать
    if ($booking->vendor_id && $user->id == $booking->vendor_id) {
        return true;
    }
    
    // Приглашенные охотники могут слушать
    if ($booking->isInvited($user->id)) {
        return true;
    }
    
    return false;
});
