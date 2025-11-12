<?php
use \Illuminate\Support\Facades\Route;

Route::get('/','CourseController@index')->name('course.admin.index');
Route::get('/create','CourseController@create')->name('course.admin.create');
Route::get('/edit/{id}','CourseController@edit')->name('course.admin.edit');
Route::post('/store/{id}','CourseController@store')->name('course.admin.store');
Route::post('/bulkEdit','CourseController@bulkEdit')->name('course.admin.bulkEdit');

Route::match(['get'],'/category','CategoryController@index')->name('course.admin.category.index');
Route::match(['get'],'/category/edit/{id}','CategoryController@edit')->name('course.admin.category.edit');
Route::post('/category/store/{id}','CategoryController@store')->name('course.admin.category.store');
Route::post('/category/bulkEdit','CategoryController@bulkEdit')->name('course.admin.category.bulkEdit');
Route::get('/category/getForSelect2','CategoryController@getForSelect2')->name('course.admin.category.getForSelect2');

Route::get('/level','LevelController@index')->name('course.admin.level.index');
Route::get('/level/edit/{id}','LevelController@edit')->name('course.admin.level.edit');
Route::post('/level/store/{id}','LevelController@store')->name('course.admin.level.store');
Route::post('/level/bulkEdit','LevelController@bulkEdit')->name('course.admin.level.bulkEdit');
Route::get('/level/getForSelect2','LevelController@getForSelect2')->name('course.admin.level.getForSelect2');

Route::group(['prefix'=>'attribute'],function (){
    Route::get('/','AttributeController@index')->name('course.admin.attribute.index');
    Route::get('edit/{id}','AttributeController@edit')->name('course.admin.attribute.edit');
    Route::post('store/{id}','AttributeController@store')->name('course.admin.attribute.store');
    Route::post('bulkEdit','AttributeController@bulkEdit')->name('course.admin.attribute.bulkEdit');
    Route::get('getAttributeForSelect2','AttributeController@getAttributeForSelect2')->name('course.admin.attribute.getAttributeForSelect2');

    Route::get('terms/{id}','AttributeController@terms')->name('course.admin.attribute.term.index');
    Route::get('term_edit/{id}','AttributeController@term_edit')->name('course.admin.attribute.term.edit');
    Route::post('term_store','AttributeController@term_store')->name('course.admin.attribute.term.store');
    Route::get('getTermForSelect2','AttributeController@getTermForSelect2')->name('course.admin.attribute.term.getForSelect2');
    Route::post('editTermBulk','AttributeController@editTermBulk')->name('course.admin.attribute.term.editTermBulk');
});

Route::group(['prefix'=>'detail/{id}'],function (){
    Route::get('/lectures','LectureController@index')->name('course.admin.lecture.index');
    Route::post('/lectures/store','LectureController@store')->name('course.admin.lecture.store');
    Route::post('/lectures/delete','LectureController@destroy')->name('course.admin.lecture.delete');
    Route::post('/sections/store','SectionController@store')->name('course.admin.section.store');
    Route::post('/sections/delete','SectionController@destroy')->name('course.admin.section.delete');
});


