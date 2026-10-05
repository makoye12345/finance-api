<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\LoanController;
use App\Http\Controllers\Api\AdminDashboardController;


// =====================================================
// PUBLIC ROUTES
// =====================================================

// Register
Route::post('/register', [AuthController::class, 'register']);

// Login
Route::post('/login', [AuthController::class, 'login']);


// =====================================================
// AUTHENTICATED ROUTES
// =====================================================

Route::middleware('auth:sanctum')->group(function () {

    // -------------------------------------------------
    // USER
    // -------------------------------------------------

    // Get currently logged-in user
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ]);
    });


    // -------------------------------------------------
    // PROFILE
    // -------------------------------------------------

    // Get profile
    Route::get('/profile', [ProfileController::class, 'show']);

    // Update profile
    Route::put('/profile', [ProfileController::class, 'update']);

     // ADMIN AUTHETICATION
    Route::middleware('admin')->group(function () {
    Route::get('/admin/dashboard', [
        AdminDashboardController::class,
        'index',
    ]);
   });

    // -------------------------------------------------
    // AUTHENTICATION
    // -------------------------------------------------

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);


    // -------------------------------------------------
    // LOANS
    // -------------------------------------------------

    // Get all loans belonging to logged-in user
    Route::get('/loans', [LoanController::class, 'index']);

    // Submit new loan application
    Route::post('/loans', [LoanController::class, 'store']);


    // -------------------------------------------------
    // DASHBOARD
    // -------------------------------------------------

    Route::get('/dashboard', function (Request $request) {

        $user = $request->user();

        // Get latest loan application
        $loan = $user->loans()
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Dashboard retrieved successfully',

            'data' => [

                // -----------------------------------------
                // USER INFORMATION
                // -----------------------------------------

                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ],


                // -----------------------------------------
                // ACCOUNT BALANCE
                // -----------------------------------------

                'balance' => 'TZS 0',


                // -----------------------------------------
                // ACTIVE LOAN
                // -----------------------------------------

                'active_loan' => $loan
                    ? 'TZS ' . number_format($loan->amount, 2)
                    : 'TZS 0',


                // -----------------------------------------
                // NEXT PAYMENT
                // -----------------------------------------

                'next_payment' => 'TZS 0',


                // -----------------------------------------
                // TRANSACTIONS
                // -----------------------------------------

                'transactions' => [],


                // -----------------------------------------
                // LATEST LOAN APPLICATION
                // -----------------------------------------

                'loan_application' => $loan
                    ? [
                        'application_id' => $loan->application_id,

                        'amount' => $loan->amount,

                        'duration' => $loan->duration,

                        'purpose' => $loan->purpose,

                        'status' => ucfirst(
                            str_replace(
                                '_',
                                ' ',
                                $loan->status
                            )
                        ),

                        'date' => $loan->created_at
                            ->format('d M Y'),
                    ]
                    : null,
            ],
        ]);
    });
});
