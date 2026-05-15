<?php

// File: routes/web.php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\AdminDashboardController;
use App\Http\Controllers\Web\AdminEquipmentController;
use App\Http\Controllers\Web\AdminBookingController;
use App\Http\Controllers\Web\AdminRoomController;
use App\Http\Controllers\Web\AdminCheckoutController;
use App\Http\Controllers\Web\AdminMemberController;
use App\Http\Controllers\Web\MemberDashboardController;
use App\Http\Controllers\Web\MemberEquipmentController;
use App\Http\Controllers\Web\MemberBookingController;
use App\Http\Controllers\Web\MemberCheckoutController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Smart-Hub Web Routes
|--------------------------------------------------------------------------
*/

// ── ROOT REDIRECT ─────────────────────────────────────────────────────────────
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route(auth()->user()->isAdmin() ? 'admin.dashboard' : 'member.dashboard');
    }
    return redirect()->route('login');
});

// ── AUTH ──────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/login-with-token', [AuthController::class, 'loginWithToken'])->name('login-with-token');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ── ADMIN ROUTES ──────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin', 'ensure_api_token'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Equipment
    Route::prefix('equipment')->name('equipment.')->group(function () {
        Route::get('/',           [AdminEquipmentController::class, 'index'])->name('index');
        Route::post('/',          [AdminEquipmentController::class, 'store'])->name('store');
        Route::put('/{equipment}',    [AdminEquipmentController::class, 'update'])->name('update');
        Route::delete('/{equipment}', [AdminEquipmentController::class, 'destroy'])->name('destroy');
    });

    // Rooms
    Route::prefix('rooms')->name('rooms.')->group(function () {
        Route::get('/',          [AdminRoomController::class, 'index'])->name('index');
        Route::post('/',         [AdminRoomController::class, 'store'])->name('store');
        Route::put('/{room}',        [AdminRoomController::class, 'update'])->name('update');
        Route::delete('/{room}',     [AdminRoomController::class, 'destroy'])->name('destroy');
        Route::patch('/{room}/toggle', [AdminRoomController::class, 'toggle'])->name('toggle');
    });

    // Bookings
    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/',              [AdminBookingController::class, 'index'])->name('index');
        Route::get('/{booking}',     [AdminBookingController::class, 'show'])->name('show');
        Route::put('/{booking}/confirm', [AdminBookingController::class, 'confirm'])->name('confirm');
        Route::post('/{booking}/cancel', [AdminBookingController::class, 'cancel'])->name('cancel');
    });

    // Checkouts
    Route::prefix('checkouts')->name('checkouts.')->group(function () {
        Route::get('/',                       [AdminCheckoutController::class, 'index'])->name('index');
        Route::post('/{checkout}/return',     [AdminCheckoutController::class, 'processReturn'])->name('return');
    });

    // Members
    Route::prefix('members')->name('members.')->group(function () {
        Route::get('/',          [AdminMemberController::class, 'index'])->name('index');
        Route::post('/',         [AdminMemberController::class, 'store'])->name('store');
        Route::patch('/{user}/toggle', [AdminMemberController::class, 'toggle'])->name('toggle');
    });
});

// ── MEMBER ROUTES ─────────────────────────────────────────────────────────────
Route::prefix('member')->name('member.')->middleware(['auth', 'role:member,admin', 'ensure_api_token'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [MemberDashboardController::class, 'index'])->name('dashboard');

    // Equipment browsing & checkout
    Route::prefix('equipment')->name('equipment.')->group(function () {
        Route::get('/',                           [MemberEquipmentController::class, 'index'])->name('index');
        Route::post('/{equipment}/checkout',      [MemberEquipmentController::class, 'checkout'])->name('checkout');
    });

    // Bookings
    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/',          [MemberBookingController::class, 'index'])->name('index');
        Route::get('/create',    [MemberBookingController::class, 'create'])->name('create');
        Route::post('/',         [MemberBookingController::class, 'store'])->name('store');
        Route::post('/{booking}/cancel', [MemberBookingController::class, 'cancel'])->name('cancel');
    });

    // Checkout history & return
    Route::prefix('checkouts')->name('checkouts.')->group(function () {
        Route::get('/',                        [MemberCheckoutController::class, 'index'])->name('index');
        Route::post('/{checkout}/return',      [MemberEquipmentController::class, 'returnEquipment'])->name('return');
    });
});
