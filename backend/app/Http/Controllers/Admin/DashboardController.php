<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Movie;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function index()
    {
        try {
            // Total statistics
            $totalUsers = User::where('role', 'customer')->count();
            $totalMovies = Movie::count();

            // Check if bookings table exists
            $totalBookings = 0;
            $totalRevenue = 0;
            $recentBookings = [];
            $bookingsByStatus = [];

            try {
                $totalBookings = DB::table('bookings')->count();
                $totalRevenue = DB::table('bookings')
                    ->where('status', 'paid')
                    ->sum('final_amount');

                // Recent bookings (last 10)
                $recentBookings = DB::table('bookings')
                    ->join('users', 'bookings.user_id', '=', 'users.user_id')
                    ->select(
                        'bookings.booking_id',
                        'bookings.booking_code',
                        'bookings.status',
                        'bookings.final_amount',
                        'bookings.created_at',
                        'users.full_name as user_name',
                        'users.email as user_email'
                    )
                    ->orderBy('bookings.created_at', 'desc')
                    ->limit(10)
                    ->get();

                // Bookings grouped by status
                $bookingsByStatus = DB::table('bookings')
                    ->select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->get()
                    ->keyBy('status');
            } catch (\Exception $e) {
                // Table doesn't exist yet, use defaults
            }

            // Movies grouped by status
            $moviesByStatus = Movie::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->keyBy('status');

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => [
                        'total_users' => $totalUsers,
                        'total_movies' => $totalMovies,
                        'total_bookings' => $totalBookings,
                        'total_revenue' => $totalRevenue ?? 0,
                    ],
                    'recent_bookings' => $recentBookings,
                    'movies_by_status' => $moviesByStatus,
                    'bookings_by_status' => $bookingsByStatus,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }
}
