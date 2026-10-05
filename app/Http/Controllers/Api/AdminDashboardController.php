<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $totalCustomers = User::where('role', 'user')->count();

        $totalAdmins = User::where('role', 'admin')->count();

        $totalLoans = Loan::count();

        $pendingLoans = Loan::where('status', 'pending')->count();

        $approvedLoans = Loan::where('status', 'approved')->count();

        $activeLoans = Loan::where('status', 'active')->count();

        $rejectedLoans = Loan::where('status', 'rejected')->count();

        $overdueLoans = Loan::where('status', 'overdue')->count();

        $totalLoanAmount = Loan::sum('amount');

        $recentLoans = Loan::with('user')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($loan) {
                return [
                    'application_id' => $loan->application_id,
                    'customer' => $loan->user?->name,
                    'email' => $loan->user?->email,
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
                    'date' => $loan->created_at->format('d M Y'),
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Admin dashboard retrieved successfully',
            'data' => [
                'customers' => [
                    'total' => $totalCustomers,
                    'admins' => $totalAdmins,
                ],
                'loans' => [
                    'total' => $totalLoans,
                    'pending' => $pendingLoans,
                    'approved' => $approvedLoans,
                    'active' => $activeLoans,
                    'rejected' => $rejectedLoans,
                    'overdue' => $overdueLoans,
                    'total_amount' => $totalLoanAmount,
                ],
                'recent_loans' => $recentLoans,
            ],
        ]);
    }
}