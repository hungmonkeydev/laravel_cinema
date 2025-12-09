"use client";

import { useEffect, useState } from "react";
import api from "@/lib/api";
import { Users, Film, Ticket, DollarSign } from "lucide-react";

interface DashboardStats {
  total_users: number;
  total_movies: number;
  total_bookings: number;
  total_revenue: number;
}

interface RecentBooking {
  booking_id: number;
  booking_code: string;
  user_name: string;
  status: string;
  final_amount: number;
  created_at: string;
}

export default function AdminDashboard() {
  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [recentBookings, setRecentBookings] = useState<RecentBooking[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchDashboardData();
  }, []);

  const fetchDashboardData = async () => {
    try {
      const response = await api.get("/admin/dashboard");
      setStats(response.data.data.stats);
      setRecentBookings(response.data.data.recent_bookings);
    } catch (error) {
      console.error("Error fetching dashboard data:", error);
      alert("Lỗi khi tải dữ liệu dashboard!");
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return <div className="text-center py-12 text-gray-700">Đang tải...</div>;
  }

  const statCards = [
    {
      title: "Tổng Users",
      value: stats?.total_users || 0,
      icon: Users,
      color: "bg-blue-500",
    },
    {
      title: "Tổng Phim",
      value: stats?.total_movies || 0,
      icon: Film,
      color: "bg-green-500",
    },
    {
      title: "Tổng Đặt vé",
      value: stats?.total_bookings || 0,
      icon: Ticket,
      color: "bg-orange-500",
    },
    {
      title: "Doanh thu",
      value: `${(stats?.total_revenue || 0).toLocaleString()} VNĐ`,
      icon: DollarSign,
      color: "bg-purple-500",
    },
  ];

  const getStatusColor = (status: string) => {
    switch (status) {
      case "paid":
        return "bg-green-100 text-green-800";
      case "pending":
        return "bg-yellow-100 text-yellow-800";
      case "cancelled":
        return "bg-red-100 text-red-800";
      default:
        return "bg-gray-100 text-gray-800";
    }
  };

  return (
    <div className="space-y-6">
      <h1 className="text-3xl font-bold text-gray-900">Dashboard</h1>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {statCards.map((card) => {
          const Icon = card.icon;
          return (
            <div
              key={card.title}
              className="bg-white rounded-lg shadow p-6 flex items-center space-x-4"
            >
              <div className={`${card.color} p-4 rounded-lg`}>
                <Icon className="text-white" size={24} />
              </div>
              <div>
                <p className="text-sm text-gray-600">{card.title}</p>
                <p className="text-2xl font-bold text-gray-900">{card.value}</p>
              </div>
            </div>
          );
        })}
      </div>

      {/* Recent Bookings */}
      <div className="bg-white rounded-lg shadow">
        <div className="p-6 border-b border-gray-200">
          <h2 className="text-xl font-bold text-gray-900">Đặt vé gần đây</h2>
        </div>
        {recentBookings.length === 0 ? (
          <div className="p-6 text-center text-gray-500">
            Chưa có đặt vé nào
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    Mã đặt vé
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    Khách hàng
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    Trạng thái
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    Tổng tiền
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    Ngày đặt
                  </th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200">
                {recentBookings.map((booking) => (
                  <tr key={booking.booking_id}>
                    <td className="px-6 py-4 text-sm text-gray-900">
                      {booking.booking_code}
                    </td>
                    <td className="px-6 py-4 text-sm text-gray-900">
                      {booking.user_name}
                    </td>
                    <td className="px-6 py-4">
                      <span
                        className={`px-2 py-1 text-xs rounded-full ${getStatusColor(
                          booking.status
                        )}`}
                      >
                        {booking.status}
                      </span>
                    </td>
                    <td className="px-6 py-4 text-sm text-gray-900">
                      {booking.final_amount.toLocaleString()} VNĐ
                    </td>
                    <td className="px-6 py-4 text-sm text-gray-500">
                      {new Date(booking.created_at).toLocaleDateString("vi-VN")}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}
