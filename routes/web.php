<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/',function(){
    return view('login');
});

Route::group(['middleware' => ['is_login']],function(){
    
    Route::get('/register',[UserController::class,'loadRegister']);
    
    Route::get('/user-registered',[UserController::class,'registered'])->name('registered');
    Route::get('/referral-register',[UserController::class,'loadReferralRegister']);
    Route::get('/email-verification/{token}',[UserController::Class,'emailVerification']);
    
    Route::get('/login',[UserController::class,'loadLogin']);
    Route::post('/login',[UserController::class,'userLogin'])->name('login');

});

Route::group(['middleware' => ['is_logout']],function(){
    Route::get('/dashboard',[UserController::class,'loadDashboard']);
    Route::get('/logout',[UserController::class,'logout'])->name('logot');
    Route::get('/referral-track',[UserController::class,'referralTrack'])->name('referralTrack');
    Route::get('/delete-account',[UserController::class,'deleteAccount'])->name('deleteAccount');

});


