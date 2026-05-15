<?php

// File: routes/api.php
// Laravel 13 — API routes

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EquipmentController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\RoomController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Smart-Hub API Routes — v1
|--------------------------------------------------------------------------
|
| Prefix  : /api/v1
| Auth    : Laravel Sanctum (Bearer Token)
| Roles   : admin | member
|
*/

Route::prefix('v1')->group(function () {

    // ------------------------------------------------------------------
    // PUBLIC: Authentication
    // ------------------------------------------------------------------
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);

        // Protected auth routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me',     [AuthController::class, 'me']);
        });
    });


    // ------------------------------------------------------------------
    // PROTECTED: All routes below require valid Sanctum token
    // ------------------------------------------------------------------
    Route::middleware('auth:sanctum')->group(function () {

        // --------------------------------------------------------------
        // ROOMS (read: any authenticated user)
        // --------------------------------------------------------------
        Route::prefix('rooms')->group(function () {
            Route::get('/',                      [RoomController::class, 'index']);
            Route::get('/{room}',                [RoomController::class, 'show']);
            Route::get('/{room}/availability',   [RoomController::class, 'availability']);
        });


        // --------------------------------------------------------------
        // EQUIPMENT + CHECKOUT (member-facing, tablet)
        // --------------------------------------------------------------
        Route::prefix('equipment')->group(function () {
            Route::get('/',                                         [EquipmentController::class, 'index']);
            Route::get('/checkouts/my',                             [EquipmentController::class, 'myCheckouts']);
            Route::get('/{equipment}',                              [EquipmentController::class, 'show']);
            Route::post('/{equipment}/checkout',                    [EquipmentController::class, 'checkout'])
                 ->middleware('role:member,admin');
            Route::post('/checkouts/{checkout}/return',             [EquipmentController::class, 'returnEquipment']);
        });


        // --------------------------------------------------------------
        // BOOKINGS (member creates, admin manages)
        // --------------------------------------------------------------
        Route::prefix('bookings')->group(function () {
            Route::post('/',                    [BookingController::class, 'store'])
                 ->middleware('role:member,admin');
            Route::get('/my',                   [BookingController::class, 'myBookings']);
            Route::get('/{booking}',            [BookingController::class, 'show']);
            Route::post('/{booking}/cancel',    [BookingController::class, 'cancel']);
        });


        // --------------------------------------------------------------
        // ADMIN-ONLY ROUTES
        // --------------------------------------------------------------
        Route::prefix('admin')->middleware('role:admin')->group(function () {

            // Dashboard
            Route::get('dashboard/stats', [DashboardController::class, 'stats']);

            // Equipment CRUD
            Route::prefix('equipment')->group(function () {
                Route::post('/',                        [EquipmentController::class, 'store']);
                Route::put('/{equipment}',              [EquipmentController::class, 'update']);
                Route::delete('/{equipment}',           [EquipmentController::class, 'destroy']);
                Route::get('/checkouts',                [EquipmentController::class, 'allCheckouts']);
            });

            // Rooms CRUD
            Route::prefix('rooms')->group(function () {
                Route::post('/',            [RoomController::class, 'store']);
                Route::put('/{room}',       [RoomController::class, 'update']);
                Route::delete('/{room}',    [RoomController::class, 'destroy']);
            });

            // Bookings management
            Route::prefix('bookings')->group(function () {
                Route::get('/',                             [BookingController::class, 'index']);
                Route::put('/{booking}/confirm',            [BookingController::class, 'confirm']);
                Route::put('/{booking}/status',             [BookingController::class, 'updateStatus']);
            });

            // Members management
            Route::prefix('members')->group(function () {
                Route::get('/',                         [MemberController::class, 'index']);
                Route::post('/',                        [MemberController::class, 'store']);
                Route::patch('/{user}/toggle',          [MemberController::class, 'toggle']);
            });
        });
    });
});
