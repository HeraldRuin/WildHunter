<?php

	use Illuminate\Support\Facades\Route;

	Route::group(['prefix' => env('COURSE_ROUTE_PREFIX', 'course')], function () {
		Route::get('/', 'CourseController@index')->name('course.search'); // Search

		Route::get('/{slug}', 'CourseController@detail')->name('course.detail');// Detail
		Route::get('/{slug}/learn', 'CourseController@learn')->name('course.learn');// Learn
        Route::get('/scorm-player/{id}','ScormPlayerController@player')->name('course.scorm_player');
        Route::post('/study-log','CourseController@studyLog')->name('course.study-log')->middleware('auth');
	});
	Route::group(['prefix' => 'user/' . env('COURSE_ROUTE_PREFIX', 'course')], function () {
		Route::match(['get','post'],'/','ManageCourseController@manageCar')->name('course.teacher.index');
		Route::match(['get','post'],'/create','ManageCourseController@createCar')->name('course.teacher.create');
		Route::match(['get','post'],'/edit/{slug}','ManageCourseController@editCar')->name('course.teacher.edit');
		Route::match(['get','post'],'/del/{slug}','ManageCourseController@deleteCar')->name('course.teacher.delete');
		Route::match(['post'],'/store/{slug}','ManageCourseController@store')->name('course.teacher.store');
		Route::get('bulkEdit/{id}','ManageCourseController@bulkEditCar')->name("course.teacher.bulk_edit");
	});
