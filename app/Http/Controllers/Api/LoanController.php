<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LoanController extends Controller
{
    /**
     * Get all loans belonging to the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $loans = $request->user()
            ->loans()
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Loans retrieved successfully',
            'data' => $loans->map(function ($loan) {
                return [
                    'application_id' => $loan->application_id,
                    'amount' => $loan->amount,
                    'duration' => $loan->duration,
                    'purpose' => $loan->purpose,
                    'status' => ucfirst(
                        str_replace('_', ' ', $loan->status)
                    ),
                    'date' => $loan->created_at->format('d M Y'),
                ];
            }),
        ]);
    }

    /**
     * Submit a new loan application.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // Check if user already has an outstanding loan
        $existingLoan = $user->loans()
            ->whereIn('status', [
                'pending',
                'approved',
                'active',
                'overdue',
            ])
            ->latest()
            ->first();

        if ($existingLoan) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an outstanding loan. Please complete your current loan before applying for another one.',
            ], 422);
        }

        // Validate new loan application
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'duration' => 'required|in:1 Month,3 Months,6 Months,12 Months',
            'purpose' => 'required|string|max:1000',
        ]);

        // Generate application ID
        $applicationId = 'LN-' .
            now()->format('Ymd') .
            '-' .
            strtoupper(Str::random(6));

        // Create loan
        $loan = Loan::create([
            'user_id' => $user->id,
            'application_id' => $applicationId,
            'amount' => $validated['amount'],
            'duration' => $validated['duration'],
            'purpose' => $validated['purpose'],
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Loan application submitted successfully',
            'data' => [
                'application_id' => $loan->application_id,
                'amount' => $loan->amount,
                'duration' => $loan->duration,
                'purpose' => $loan->purpose,
                'status' => 'Pending Review',
                'created_at' => $loan->created_at,
            ],
        ], 201);
    }
}
