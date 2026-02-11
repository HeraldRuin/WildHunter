<?php
use Illuminate\Support\Facades\Route;

// Booking
Route::group(['prefix'=>config('booking.booking_route_prefix')],function(){
    Route::post('/addToCart','BookingController@addToCart');
    Route::post('/addToCartAnimal','BookingController@addToCartAnimal');
    Route::post('/validateRooms','BookingController@validateRooms');
    Route::post('/doCheckout','BookingController@doCheckout')->name('booking.doCheckout');
    Route::get('/confirm/{gateway}','BookingController@confirmPayment')->name('booking.confirm-payment');
    Route::get('/cancel/{gateway}','BookingController@cancelPayment');
    Route::get('/{code}','BookingController@detail');
    Route::get('/{code}/checkout','BookingController@checkout')->name('booking.checkout');
    Route::get('/{code}/check-status','BookingController@checkStatusCheckout');
    Route::post('/{booking}/change-user','BookingController@changeUserBooking');
    Route::post('/{booking}/confirm','BookingController@confirmBooking');
    Route::post('/{booking}/start-collection','BookingController@startCollection');
    Route::post('/{booking}/cancel-collection','BookingController@cancelCollection');
    Route::post('/{booking}/finish-collection','BookingController@finishCollection');
    Route::post('/{booking}/invite-hunter','BookingController@inviteHunter');
    Route::post('/{booking}/invite-hunter-by-email','BookingController@inviteHunterByEmail');
    Route::get('/{booking}/invited-hunters','BookingController@getInvitedHunters');
    Route::post('/{booking}/email-hunter','BookingController@emailHunter');
    Route::post('/{booking}/accept-invitation','BookingController@acceptInvitation');
    Route::post('/{booking}/decline-invitation','BookingController@declineInvitation');
    Route::post('/{booking}/cancel','BookingController@cancelBooking');
    Route::post('/{booking}/complete','BookingController@completeBooking');


    // Добавление услуг
    Route::get('/{booking}/saved-services', 'BookingController@getBookingServices');
    Route::post('/{booking}/save-services', 'BookingController@saveServices');

    Route::post('/{booking}/trophies', 'BookingController@storeTrophy');
    Route::get('/trophies/animals', 'BookingController@getAnimalTrophyServices');
    Route::delete('/{booking}/trophy/{trophyId}', 'BookingController@deleteTrophy');
    Route::get('/booking/{booking}/saved-penalties', 'BookingController@getBookingPenalties');

    Route::get('/{booking}/penalty/animals', 'BookingController@getAnimalPenaltyServices');
    Route::post('/{booking}/penalty', 'BookingController@storePenalty');
    Route::delete('/{booking}/penalty/{penaltyId}', 'BookingController@deletePenalty');

    Route::get('/{booking}/preparation/animals', 'BookingController@getAnimalPreparationServices');
    Route::post('/{booking}/preparation', 'BookingController@storePreparation');
    Route::delete('/{booking}/preparation/{preparationId}', 'BookingController@deletePreparation');

    Route::post('/{booking}/food', 'BookingController@storeFoods');
    Route::delete('/{booking}/food/{foodId}', 'BookingController@deleteFoods');

    Route::get('/{booking}/addetional/services', 'BookingController@getAddetionalServices');
    Route::post('/{booking}/addetional', 'BookingController@storeAddetional');
    Route::delete('/{booking}/addetional/{addetionalId}', 'BookingController@deleteAddetional');

    //ical
	Route::get('/export-ical/{type}/{id}','BookingController@exportIcal')->name('booking.admin.export-ical');
    //inquiry
    Route::post('/addEnquiry','BookingController@addEnquiry');
    Route::post('/setPaidAmount','BookingController@setPaidAmount')->name('booking.setPaidAmount')->middleware(['auth']);

    Route::get('/modal/{booking}','BookingController@modal')->name('booking.modal');

    Route::post('/storeNoteBooking','BookingController@storeNoteBooking');
});


Route::group(['prefix'=>'gateway'],function(){
    Route::get('/confirm/{gateway}','NormalCheckoutController@confirmPayment')->name('gateway.confirm');
    Route::get('/cancel/{gateway}','NormalCheckoutController@cancelPayment')->name('gateway.cancel');
    Route::get('/info','NormalCheckoutController@showInfo')->name('gateway.info');
    Route::match(['get','post'],'/gateway_callback/{gateway}','BookingController@callbackPayment')->name('gateway.webhook');
});
