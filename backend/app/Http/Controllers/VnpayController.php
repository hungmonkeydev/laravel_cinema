<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // Import Log để ghi lại lỗi nếu cần

class VnpayController extends Controller
{
    public function createPayment(Request $request)
    {
        try {
            // 1. Lấy thông tin người dùng đang đăng nhập (Nhờ auth:sanctum)
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'code' => '401',
                    'message' => 'Bạn cần đăng nhập để thực hiện thanh toán.'
                ], 401);
            }

            // 2. Kiểm tra cấu hình VNPay
            $vnp_TmnCode = env('VNPAY_TMN_CODE');
            $vnp_HashSecret = env('VNPAY_HASH_SECRET');
            $vnp_Url = env('VNPAY_URL');
            $vnp_Returnurl = env('VNPAY_RETURN_URL');

            if (!$vnp_HashSecret || !$vnp_TmnCode) {
                return response()->json([
                    'code' => '500',
                    'message' => 'Lỗi Server: Chưa cấu hình VNPAY_HASH_SECRET hoặc VNPAY_TMN_CODE.'
                ], 500);
            }

            // 3. Chuẩn bị dữ liệu thanh toán
            // Tạo mã đơn hàng duy nhất: YYYYMMDDHHMMSS + Random
            // Ví dụ: 20251224153099
            $vnp_TxnRef = date('YmdHis') . rand(10, 99); 
            
            // Nội dung thanh toán: Thêm UserID vào để sau này tra soát
            $vnp_OrderInfo = "User " . $user->user_id . ": " . ($request->order_desc ?? 'Mua ve xem phim');
            
            $vnp_OrderType = 'billpayment';
            
            // Số tiền: VNPay yêu cầu nhân 100
            // Ép kiểu int để tránh lỗi số thập phân
            $vnp_Amount = (int)$request->order_total * 100;

            $vnp_Locale = $request->language ?? 'vn';
            $vnp_BankCode = $request->bank_code ?? '';
            $vnp_IpAddr = $request->ip(); // Cách lấy IP chuẩn của Laravel

            // 4. Mảng dữ liệu gửi sang VNPay
            $inputData = array(
                "vnp_Version" => "2.1.0",
                "vnp_TmnCode" => $vnp_TmnCode,
                "vnp_Amount" => $vnp_Amount,
                "vnp_Command" => "pay",
                "vnp_CreateDate" => date('YmdHis'),
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $vnp_IpAddr,
                "vnp_Locale" => $vnp_Locale,
                "vnp_OrderInfo" => $vnp_OrderInfo,
                "vnp_OrderType" => $vnp_OrderType,
                "vnp_ReturnUrl" => $vnp_Returnurl,
                "vnp_TxnRef" => $vnp_TxnRef,
            );

            if (isset($vnp_BankCode) && $vnp_BankCode != "") {
                $inputData['vnp_BankCode'] = $vnp_BankCode;
            }

            // 5. Sắp xếp và tạo chữ ký (Quan trọng nhất)
            ksort($inputData);
            $query = "";
            $i = 0;
            $hashdata = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }

            $vnp_Url = $vnp_Url . "?" . $query;
            
            if (isset($vnp_HashSecret)) {
                $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
                $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
            }

            // 6. Trả về JSON thành công
            return response()->json([
                'code' => '00',
                'message' => 'success',
                'data' => $vnp_Url
            ]);

        } catch (\Exception $e) {
            // Ghi log lỗi để debug
            Log::error('VNPAY Error: ' . $e->getMessage());

            // Trả về JSON lỗi thay vì trang trắng
            return response()->json([
                'code' => '500',
                'message' => 'Có lỗi xảy ra khi tạo thanh toán: ' . $e->getMessage()
            ], 500);
        }
    }
}