<?php

namespace Modules\Visa\Routes;

use Illuminate\Support\Facades\Route;
use Modules\Visa\Pages\VisaPage;
use Modules\Visa\Pages\DetailPage;
use Modules\Visa\Pages\ApplicationsForm;
use Modules\Visa\Pages\User\VisaBookingDetailPage;

Route::get('/visa', VisaPage::class)->name('visa.search');
Route::get('/visa/{slug}', DetailPage::class)->name('visa.detail');
Route::get('/visa/{slug}/applications/{code}', ApplicationsForm::class)->name('visa.applications');


//  Related to user
Route::group([
    'prefix' => 'user',
    'middleware' => ['auth'],
], function () {
    Route::get('/visa/booking/{code}', VisaBookingDetailPage::class)->name('visa.user.booking-detail');
});
