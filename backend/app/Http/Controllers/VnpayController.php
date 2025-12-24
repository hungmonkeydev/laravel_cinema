<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VnpayController extends Controller
{
    public function createPayment(Request $request)
    {
        $vnp_HashSecret = env('VNPAY_HASH_SECRET');
        if (!$vnp_HashSecret) {
            dd("LỖI: Chưa đọc được VNPAY_HASH_SECRET từ file .env. Hãy chạy php artisan config:clear");
        }
        // 1. Lấy thông tin từ config hoặc .env
        $vnp_TmnCode = env('VNPAY_TMN_CODE'); // Mã website tại VNPAY 
        $vnp_HashSecret = env('VNPAY_HASH_SECRET'); // Chuỗi bí mật
        $vnp_Url = env('VNPAY_URL'); // URL thanh toán của VNPAY
        $vnp_Returnurl = env('VNPAY_RETURN_URL'); // URL trả về khi thanh toán xong

        // 2. Lấy dữ liệu từ Frontend gửi lên
        $vnp_TxnRef = rand(1000, 99999); // Mã đơn hàng (Tạm thời random, thực tế nên lấy ID đơn hàng)
        $vnp_OrderInfo = $request->order_desc ?? 'Thanh toan don hang';
        $vnp_OrderType = 'billpayment';

        // Quan trọng: Số tiền bên VNPay phải nhân 100
        $vnp_Amount = $request->order_total * 100;

        $vnp_Locale = $request->language ?? 'vn';
        $vnp_BankCode = $request->bank_code ?? '';
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR']; // Lấy IP khách hàng

        // 3. Tạo mảng dữ liệu gửi sang VNPay
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

        // 4. Sắp xếp dữ liệu theo bảng chữ cái (Bắt buộc)
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

        // 5. Tạo chữ ký bảo mật (Secure Hash)
        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        // 6. Trả về URL cho React để chuyển hướng
        return response()->json([
            'code' => '00',
            'message' => 'success',
            'data' => $vnp_Url
        ]);
    }
}
