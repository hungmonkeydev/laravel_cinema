"use client";

import React, { useState } from "react";
import { X, Loader2, Eye, EyeOff } from "lucide-react";
import api, { initCsrf } from "@/lib/api";
import OtpModal from "./otp-modal";

interface LoginModalProps {
  isOpen: boolean;
  onClose: () => void;
  onLoginSuccess?: (user: any) => void;
}

export default function LoginModal({
  isOpen,
  onClose,
  onLoginSuccess,
}: LoginModalProps) {
  const [isSignUp, setIsSignUp] = useState(false);
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [fullName, setFullName] = useState("");
  const [phone, setPhone] = useState("");
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [showOtpModal, setShowOtpModal] = useState(false);
  const [otpEmail, setOtpEmail] = useState("");

  const validateEmail = (email: string) => {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  };

  const validatePhone = (phone: string) => {
    return /^(\+84|0)[0-9]{9,11}$/.test(phone);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    setSuccess("");
    setIsLoading(true);

    try {
      if (isSignUp) {
        // ============================================================
        // === LOGIC ĐĂNG KÝ (ĐÃ SỬA: KHÔNG OTP, KHÔNG AUTO LOGIN) ===
        // ============================================================
        if (!fullName.trim()) {
          setError("Vui lòng nhập họ tên");
          setIsLoading(false);
          return;
        }
        if (!validateEmail(email)) {
          setError("Email không hợp lệ");
          setIsLoading(false);
          return;
        }
        if (!validatePhone(phone)) {
          setError("Số điện thoại không hợp lệ (VN)");
          setIsLoading(false);
          return;
        }
        if (password.length < 8) {
          setError("Mật khẩu phải có ít nhất 8 ký tự");
          setIsLoading(false);
          return;
        }
        if (password !== confirmPassword) {
          setError("Mật khẩu xác nhận không khớp");
          setIsLoading(false);
          return;
        }

        await initCsrf();

        // Gọi API Đăng ký
        await api.post("/register", {
          full_name: fullName,
          email,
          phone,
          password,
          password_confirmation: confirmPassword,
        });

        // --- ĐOẠN XỬ LÝ MỚI ---
        setSuccess("Đăng ký thành công! Vui lòng đăng nhập.");

        // Đợi 1.5 giây để người dùng đọc thông báo, sau đó chuyển sang form Đăng nhập
        setTimeout(() => {
          setIsSignUp(false); // Chuyển sang tab Đăng nhập
          setSuccess(""); // Xóa thông báo
          // Giữ nguyên Email & Pass đã nhập để người dùng đỡ phải gõ lại
        }, 1500);
        // -----------------------
      } else {
        // ============================================================
        // === LOGIC ĐĂNG NHẬP (GIỮ NGUYÊN NHƯ CŨ) ===
        // ============================================================
        if (!validateEmail(email)) {
          setError("Email không hợp lệ");
          setIsLoading(false);
          return;
        }
        if (password.length < 6) {
          setError("Mật khẩu phải có ít nhất 6 ký tự");
          setIsLoading(false);
          return;
        }

        await initCsrf();
        const response = await api.post("/login", { email, password });

        console.log("API Response:", response);
        const data = response.data;

        // Tìm user ở nhiều vị trí có thể xảy ra trong response
        let userData = data.user || data.data?.user || data.data || null;

        // Fallback: nếu data chính là user (có id và email)
        if (!userData && data.id && data.email) {
          userData = data;
        }

        const token =
          data.token || data.access_token || data.data?.access_token;

        console.log("User tìm thấy:", userData);

        if (userData) {
          // Extract role and redirect_to from response
          const role = data.data?.role || userData.role || "customer";
          const redirectTo =
            data.data?.redirect_to || (role === "admin" ? "/admin" : "/");

          localStorage.setItem("user_info", JSON.stringify(userData));
          localStorage.setItem("user_role", role);
          if (token) localStorage.setItem("access_token", token);

          // Dispatch custom event to notify header component
          window.dispatchEvent(new Event("userLoggedIn"));

          if (onLoginSuccess) {
            console.log("Gọi onLoginSuccess");
            onLoginSuccess(userData);
          } else {
            console.warn("Chưa truyền onLoginSuccess");
          }

          setSuccess("Đăng nhập thành công!");

          // Redirect based on role
          setTimeout(() => {
            window.location.href = redirectTo;
          }, 1000);
          return; // Don't execute the rest
        }

        setSuccess("Đăng nhập thành công!");

        setTimeout(() => {
          setEmail("");
          setPassword("");
          setError("");
          setSuccess("");
          onClose();
        }, 1000);
      }
    } catch (err: any) {
      // === XỬ LÝ LỖI ===
      console.error("Lỗi:", err);
      let errorMessage = "Đã xảy ra lỗi không xác định.";

      if (err.response?.data?.errors) {
        const validationErrors = err.response.data.errors;
        const firstErrorKey = Object.keys(validationErrors)[0];
        errorMessage = validationErrors[firstErrorKey][0];
      } else {
        errorMessage =
          err.response?.data?.message ||
          (isSignUp ? "Đăng ký thất bại." : "Sai email hoặc mật khẩu");
      }
      setError(errorMessage);
    } finally {
      setIsLoading(false);
    }
  };

  const handleGoogleLogin = () => {
    const backendUrl = process.env.NEXT_PUBLIC_API_URL;
    window.location.href = `${backendUrl}/api/auth/google`;
  };

  const handleOtpVerifySuccess = (user: any, responseData?: any) => {
    // Extract role and redirect_to from response
    const role = responseData?.role || user.role || "customer";
    const redirectTo =
      responseData?.redirect_to || (role === "admin" ? "/admin" : "/");

    // Store role
    if (!localStorage.getItem("user_role")) {
      localStorage.setItem("user_role", role);
    }

    if (onLoginSuccess) {
      onLoginSuccess(user);
    }
    setShowOtpModal(false);
    onClose();

    // Redirect based on role
    setTimeout(() => {
      window.location.href = redirectTo;
    }, 500);
  };

  if (!isOpen) return null;

  return (
    <>
      <div
        className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center animate-in fade-in duration-200"
        onClick={onClose}
      >
        <div
          className="bg-card bg-white dark:bg-gray-900 rounded-lg shadow-lg w-full max-w-md mx-4 overflow-hidden"
          onClick={(e) => e.stopPropagation()}
        >
          <div className="p-6 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
            <h3 className="text-lg font-bold text-foreground">
              {isSignUp ? "Đăng Ký" : "Đăng Nhập"}
            </h3>
            <button
              onClick={onClose}
              className="p-1 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition"
            >
              <X size={20} />
            </button>
          </div>

          <form onSubmit={handleSubmit} className="p-6 space-y-4">
            {isSignUp && (
              <>
                <div>
                  <label className="block text-sm font-semibold mb-2">
                    Họ tên
                  </label>
                  <input
                    type="text"
                    value={fullName}
                    onChange={(e) => setFullName(e.target.value)}
                    required
                    className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary outline-none"
                    placeholder="Nguyễn Văn A"
                  />
                </div>
                <div>
                  <label className="block text-sm font-semibold mb-2">
                    Số điện thoại
                  </label>
                  <input
                    type="tel"
                    value={phone}
                    onChange={(e) => setPhone(e.target.value)}
                    required
                    className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary outline-none"
                    placeholder="0901234567"
                  />
                </div>
              </>
            )}

            <div>
              <label className="block text-sm font-semibold mb-2">Email</label>
              <input
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
                className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary outline-none"
                placeholder="your@email.com"
              />
            </div>

            <div>
              <label className="block text-sm font-semibold mb-2">
                Mật khẩu
              </label>
              <div className="relative">
                <input
                  type={showPassword ? "text" : "password"}
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  required
                  className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary outline-none"
                  placeholder="••••••••"
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"
                >
                  {showPassword ? <EyeOff size={18} /> : <Eye size={18} />}
                </button>
              </div>
            </div>

            {isSignUp && (
              <div>
                <label className="block text-sm font-semibold mb-2">
                  Xác nhận mật khẩu
                </label>
                <input
                  type="password"
                  value={confirmPassword}
                  onChange={(e) => setConfirmPassword(e.target.value)}
                  required
                  className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary outline-none"
                  placeholder="••••••••"
                />
              </div>
            )}

            {error && <p className="text-red-500 text-sm">{error}</p>}
            {success && (
              <p className="text-green-600 text-sm font-semibold">{success}</p>
            )}

            <button
              type="submit"
              disabled={isLoading}
              className="w-full py-3 bg-orange-500 text-white font-bold rounded-lg hover:bg-orange-600 transition disabled:opacity-50 flex justify-center items-center gap-2"
            >
              {isLoading ? (
                <>
                  <Loader2 className="animate-spin" size={20} /> Đang xử lý...
                </>
              ) : isSignUp ? (
                "Đăng Ký"
              ) : (
                "Đăng Nhập"
              )}
            </button>

            {/* Divider */}
            <div className="relative my-4">
              <div className="absolute inset-0 flex items-center">
                <div className="w-full border-t border-gray-300"></div>
              </div>
              <div className="relative flex justify-center text-sm">
                <span className="px-2 bg-white text-gray-500">Hoặc</span>
              </div>
            </div>

            {/* Google Login Button */}
            <button
              type="button"
              onClick={handleGoogleLogin}
              className="w-full py-3 px-4 border border-gray-300 rounded-lg hover:bg-gray-50 transition flex items-center justify-center gap-3 font-semibold"
            >
              <svg className="w-5 h-5" viewBox="0 0 24 24">
                <path
                  fill="#4285F4"
                  d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                />
                <path
                  fill="#34A853"
                  d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                />
                <path
                  fill="#FBBC05"
                  d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                />
                <path
                  fill="#EA4335"
                  d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                />
              </svg>
              Đăng nhập với Google
            </button>

            <p className="text-center text-sm text-gray-500">
              {isSignUp ? (
                <>
                  Đã có tài khoản?{" "}
                  <button
                    type="button"
                    onClick={() => {
                      setIsSignUp(false);
                      setError("");
                      setSuccess("");
                    }}
                    className="text-orange-500 hover:underline font-semibold"
                  >
                    Đăng nhập
                  </button>
                </>
              ) : (
                <>
                  Chưa có tài khoản?{" "}
                  <button
                    type="button"
                    onClick={() => {
                      setIsSignUp(true);
                      setError("");
                      setSuccess("");
                    }}
                    className="text-orange-500 hover:underline font-semibold"
                  >
                    Đăng ký ngay
                  </button>
                </>
              )}
            </p>
          </form>
        </div>
      </div>

      <OtpModal
        isOpen={showOtpModal}
        onClose={() => setShowOtpModal(false)}
        email={otpEmail}
        onVerifySuccess={handleOtpVerifySuccess}
      />
    </>
  );
}
