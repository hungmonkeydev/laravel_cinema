"use client";

import type React from "react";
import { useState } from "react";
import { X } from "lucide-react";
import api, { initCsrf } from "@/lib/api"; // ĐÃ THÊM DÒNG NÀY

interface LoginModalProps {
  isOpen: boolean;
  onClose: () => void;
}

export default function LoginModal({ isOpen, onClose }: LoginModalProps) {
  const [isSignUp, setIsSignUp] = useState(false);
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [fullName, setFullName] = useState("");
  const [phone, setPhone] = useState("");
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");

  const validateEmail = (email: string) => {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  };

  const validatePhone = (phone: string) => {
    return /^(\+84|0)[0-9]{9,10}$/.test(phone);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError("");
    setSuccess("");
    setIsLoading(true);

    try {
      if (isSignUp) {
        // ĐĂNG KÝ: tạm giữ nguyên (sau này bạn làm thật cũng được)
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
        if (password.length < 6) {
          setError("Mật khẩu phải có ít nhất 6 ký tự");
          setIsLoading(false);
          return;
        }
        if (password !== confirmPassword) {
          setError("Mật khẩu xác nhận không khớp");
          setIsLoading(false);
          return;
        }

        await new Promise((resolve) => setTimeout(resolve, 1500));
        setSuccess("Đăng ký thành công! Vui lòng đăng nhập.");
        setTimeout(() => {
          setFullName("");
          setEmail("");
          setPhone("");
          setPassword("");
          setConfirmPassword("");
          setSuccess("");
          setIsSignUp(false);
        }, 2000);
      } else {
        // ĐĂNG NHẬP THẬT VỚI LARAVEL – ĐÃ SỬA HOÀN TOÀN
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

        await initCsrf(); // Lấy cookie CSRF từ Laravel
        await api.post("/login", { email, password }); // Gọi API login thật

        setSuccess("Đăng nhập thành công!");

        setTimeout(() => {
          setEmail("");
          setPassword("");
          setError("");
          setSuccess("");
          onClose();
          window.location.reload(); // reload để cập nhật trạng thái đã login
        }, 1000);
      }
    } catch (err: any) {
      setError(err.response?.data?.message || "Sai email hoặc mật khẩu");
    } finally {
      setIsLoading(false);
    }
  };

  if (!isOpen) return null;

  return (
    <div
      className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center"
      onClick={onClose}
    >
      <div
        className="bg-card rounded-lg shadow-lg w-full max-w-md mx-4"
        onClick={(e) => e.stopPropagation()}
      >
        <div className="p-6 border-b border-border flex items-center justify-between">
          <h3 className="text-lg font-bold text-foreground">
            {isSignUp ? "Đăng Ký" : "Đăng Nhập"}
          </h3>
          <button onClick={onClose} className="p-1 hover:bg-muted rounded-lg">
            <X size={20} />
          </button>
        </div>

        <form onSubmit={handleSubmit} className="p-6 space-y-4">
          {isSignUp && (
            <>
              <div>
                <label className="block text-sm font-semibold text-foreground mb-2">
                  Họ tên
                </label>
                <input
                  type="text"
                  value={fullName}
                  onChange={(e) => setFullName(e.target.value)}
                  required
                  className="w-full px-4 py-2 bg-muted border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                  placeholder="Nguyễn Văn A"
                />
              </div>
              <div>
                <label className="block text-sm font-semibold text-foreground mb-2">
                  Số điện thoại
                </label>
                <input
                  type="tel"
                  value={phone}
                  onChange={(e) => setPhone(e.target.value)}
                  required
                  className="w-full px-4 py-2 bg-muted border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
                  placeholder="0901234567"
                />
              </div>
            </>
          )}

          <div>
            <label className="block text-sm font-semibold text-foreground mb-2">
              Email
            </label>
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
              className="w-full px-4 py-2 bg-muted border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
              placeholder="your@email.com"
            />
          </div>

          <div>
            <label className="block text-sm font-semibold text-foreground mb-2">
              Mật khẩu
            </label>
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
              className="w-full px-4 py-2 bg-muted border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
              placeholder="••••••••"
            />
          </div>

          {isSignUp && (
            <div>
              <label className="block text-sm font-semibold text-foreground mb-2">
                Xác nhận mật khẩu
              </label>
              <input
                type="password"
                value={confirmPassword}
                onChange={(e) => setConfirmPassword(e.target.value)}
                required
                className="w-full px-4 py-2 bg-muted border border-border rounded-lg text-foreground focus:outline-none focus:ring-2 focus:ring-primary"
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
            className="w-full py-3 bg-primary text-primary-foreground font-bold rounded-lg hover:bg-primary/90 transition disabled:opacity-50"
          >
            {isLoading
              ? isSignUp
                ? "Đang đăng ký..."
                : "Đang đăng nhập..."
              : isSignUp
              ? "Đăng Ký"
              : "Đăng Nhập"}
          </button>

          <p className="text-center text-muted-foreground text-sm">
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
                  className="text-primary hover:underline font-semibold"
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
                  className="text-primary hover:underline font-semibold"
                >
                  Đăng ký ngay
                </button>
              </>
            )}
          </p>
        </form>
      </div>
    </div>
  );
}
