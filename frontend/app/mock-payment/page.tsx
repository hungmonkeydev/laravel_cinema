"use client";

import React, { Suspense, useState } from "react";
import { useSearchParams, useRouter } from "next/navigation";

function MockPayment() {
  const searchParams = useSearchParams();
  const router = useRouter();
  const order = searchParams?.get("order") ?? "";
  const amount = searchParams?.get("amount") ?? "";

  const [status, setStatus] = useState("");
  const [loading, setLoading] = useState(false);

  const sendIpn = async (success: boolean) => {
    setLoading(true);
    setStatus("Đang gửi IPN tới backend...");

    try {
      const base =
        process.env.NEXT_PUBLIC_SERVER_API || "http://127.0.0.1:8000";

      // SỬA: Thêm đầy đủ các trường dữ liệu giả lập MoMo
      const body = {
        partnerCode: "MOMO_MOCK",
        orderId: order || `MOCK-${Date.now()}`,
        requestId: `REQ-${Date.now()}`,
        amount: amount ? amount.toString() : "0",
        orderInfo: `Mock payment for ${order}`,
        orderType: "momo_wallet",
        transId: `TRANS-${Date.now()}`,
        resultCode: success ? 0 : 1006,
        message: success ? "Giao dịch thành công." : "Giao dịch bị từ chối.",
        payType: "qr",
        responseTime: Date.now(),
        extraData: "",
        signature: "mock-signature",
      };

      const res = await fetch(`${base}/api/payment/momo-ipn`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(body),
      });

      if (res.status === 204) {
        setStatus("IPN thành công (204). Đang chuyển hướng...");
        // SỬA: Tự động chuyển hướng nếu thành công
        if (success) {
          setTimeout(() => {
            // Thay đổi đường dẫn này theo router thật của bạn
            router.push(`/booking-success?booking_code=${order}`);
          }, 1500);
        }
      } else {
        const data = await res.text();
        setStatus(`Lỗi từ Server (${res.status}): ${data}`);
      }
    } catch (e: any) {
      setStatus(`Lỗi kết nối: ${e?.message || e}`);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div
      style={{
        padding: 24,
        fontFamily: "Inter, Arial, sans-serif",
        maxWidth: 600,
        margin: "0 auto",
      }}
    >
      <h1 style={{ marginBottom: 8, fontSize: 24, fontWeight: "bold" }}>
        Cổng thanh toán giả lập
      </h1>
      <p style={{ color: "#666", marginBottom: 24 }}>
        Môi trường Test Mode (Sandbox). Vui lòng chọn trạng thái giao dịch.
      </p>

      <div
        style={{
          background: "#fff",
          padding: 20,
          borderRadius: 8,
          border: "1px solid #e5e7eb",
          marginBottom: 24,
        }}
      >
        <div
          style={{
            marginBottom: 12,
            display: "flex",
            justifyContent: "space-between",
          }}
        >
          <strong style={{ color: "#374151" }}>Mã đơn hàng:</strong>
          <span style={{ fontFamily: "monospace", fontWeight: "bold" }}>
            {order || "---"}
          </span>
        </div>
        <div style={{ display: "flex", justifyContent: "space-between" }}>
          <strong style={{ color: "#374151" }}>Số tiền:</strong>
          <span style={{ color: "#d97706", fontWeight: "bold" }}>
            {amount
              ? new Intl.NumberFormat("vi-VN", {
                  style: "currency",
                  currency: "VND",
                }).format(Number(amount))
              : "0 đ"}
          </span>
        </div>
      </div>

      <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
        <button
          onClick={() => sendIpn(true)}
          disabled={loading}
          style={{
            padding: "12px",
            background: "#16a34a",
            color: "white",
            border: "none",
            borderRadius: 6,
            fontWeight: "600",
            cursor: loading ? "not-allowed" : "pointer",
            opacity: loading ? 0.7 : 1,
          }}
        >
          {loading ? "Đang xử lý..." : "✅ Thanh toán thành công"}
        </button>

        <button
          onClick={() => sendIpn(false)}
          disabled={loading}
          style={{
            padding: "12px",
            background: "#ef4444",
            color: "white",
            border: "none",
            borderRadius: 6,
            fontWeight: "600",
            cursor: loading ? "not-allowed" : "pointer",
            opacity: loading ? 0.7 : 1,
          }}
        >
          {loading ? "Đang xử lý..." : "❌ Thanh toán thất bại"}
        </button>

        <button
          onClick={() => router.push("/")}
          style={{
            marginTop: 12,
            padding: "10px",
            background: "transparent",
            color: "#6b7280",
            border: "1px solid #d1d5db",
            borderRadius: 6,
            cursor: "pointer",
          }}
        >
          Hủy và quay về trang chủ
        </button>
      </div>

      {status && (
        <div
          style={{
            marginTop: 24,
            padding: 12,
            borderRadius: 6,
            background: status.includes("Lỗi") ? "#fee2e2" : "#dcfce7",
            color: status.includes("Lỗi") ? "#b91c1c" : "#166534",
            fontSize: 14,
          }}
        >
          {status}
        </div>
      )}
    </div>
  );
}


export default function MockPaymentPage() {
  return (
    <Suspense fallback={<div>Loading...</div>}>
      <MockPayment />
    </Suspense>
  );
}