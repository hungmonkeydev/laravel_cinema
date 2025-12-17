// "use client";

// import React, { useState, useRef, useEffect } from "react";
// import { X, Loader2, Mail, RefreshCw } from "lucide-react";
// import api, { initCsrf } from "@/lib/api";

// interface OtpModalProps {
//   isOpen: boolean;
//   onClose: () => void;
//   email: string;
//   onVerifySuccess?: (user: any) => void;
// }

// export default function OtpModal({
//   isOpen,
//   onClose,
//   email,
//   onVerifySuccess,
// }: OtpModalProps) {
//   const [otp, setOtp] = useState(["", "", "", "", "", ""]);
//   const [isLoading, setIsLoading] = useState(false);
//   const [error, setError] = useState("");
//   const [success, setSuccess] = useState("");
//   const [countdown, setCountdown] = useState(600); // 10 minutes
//   const [canResend, setCanResend] = useState(false);
//   // initialize with fixed length array to avoid undefined slots
//   const inputRefs = useRef<(HTMLInputElement | null)[]>(Array(6).fill(null));

//   useEffect(() => {
//     if (!isOpen) return;

//     const timer = setInterval(() => {
//       setCountdown((prev) => {
//         if (prev <= 1) {
//           setCanResend(true);
//           return 0;
//         }
//         return prev - 1;
//       });
//     }, 1000);

//     return () => clearInterval(timer);
//   }, [isOpen]);

//   const formatTime = (seconds: number) => {
//     const mins = Math.floor(seconds / 60);
//     const secs = seconds % 60;
//     return `${mins}:${secs.toString().padStart(2, "0")}`;
//   };

//   const handleChange = (index: number, value: string) => {
//     if (value.length > 1) {
//       value = value.slice(-1);
//     }

//     if (!/^\d*$/.test(value)) return;

//     const newOtp = [...otp];
//     newOtp[index] = value;
//     setOtp(newOtp);
//     setError("");

//     // Auto focus next input
//     if (value && index < 5) {
//       inputRefs.current[index + 1]?.focus();
//     }
//   };

//   // use more specific event type for input elements
//   const handleKeyDown = (index: number, e: React.KeyboardEvent<HTMLInputElement>) => {
//     if (e.key === "Backspace" && !otp[index] && index > 0) {
//       inputRefs.current[index - 1]?.focus();
//     }
//   };

//   const handlePaste = (e: React.ClipboardEvent) => {
//     e.preventDefault();
//     const pastedData = e.clipboardData.getData("text").slice(0, 6);
//     if (!/^\d+$/.test(pastedData)) return;

//     const newOtp = [...otp];
//     for (let i = 0; i < pastedData.length && i < 6; i++) {
//       newOtp[i] = pastedData[i];
//     }
//     setOtp(newOtp);
//     inputRefs.current[Math.min(pastedData.length, 5)]?.focus();
//   };

//   const handleVerify = async () => {
//     const otpString = otp.join("");
//     if (otpString.length !== 6) {
//       setError("Vui lòng nhập đầy đủ mã OTP");
//       return;
//     }

//     setError("");
//     setIsLoading(true);

//     try {
//       await initCsrf();
//       const response = await api.post("/verify-otp", {
//         email,
//         otp: otpString,
//       });

//       const userData = response.data.data;

//       if (userData) {
//         localStorage.setItem("user_info", JSON.stringify(userData));
//         window.dispatchEvent(new Event("userLoggedIn"));
//       }

//       setSuccess("Xác thực thành công!");

//       if (onVerifySuccess && userData) {
//         onVerifySuccess(userData);
//       }

//       setTimeout(() => {
//         onClose();
//         window.location.reload();
//       }, 1500);
//     } catch (err: any) {
//       console.error("Verify OTP error:", err);
//       const errorMessage =
//         err.response?.data?.message || "Mã OTP không đúng hoặc đã hết hạn";
//       setError(errorMessage);
//     } finally {
//       setIsLoading(false);
//     }
//   };

//   const handleResend = async () => {
//     setIsLoading(true);
//     setError("");

//     try {
//       await initCsrf();
//       await api.post("/resend-otp", { email });

//       setSuccess("Mã OTP mới đã được gửi!");
//       setCountdown(600);
//       setCanResend(false);
//       setOtp(["", "", "", "", "", ""]);
//       inputRefs.current[0]?.focus();

//       setTimeout(() => setSuccess(""), 3000);
//     } catch (err: any) {
//       console.error("Resend OTP error:", err);
//       setError(err.response?.data?.message || "Không thể gửi lại mã OTP");
//     } finally {
//       setIsLoading(false);
//     }
//   };

//   if (!isOpen) return null;

//   return (
//     <div
//       className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center animate-in fade-in duration-200"
//       onClick={onClose}
//     >
//       <div
//         className="bg-white dark:bg-gray-900 rounded-lg shadow-lg w-full max-w-md mx-4 overflow-hidden"
//         onClick={(e) => e.stopPropagation()}
//       >
//         <div className="p-6 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
//           <h3 className="text-lg font-bold text-foreground">
//             Xác thực email
//           </h3>
//           <button
//             onClick={onClose}
//             className="p-1 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition"
//           >
//             <X size={20} />
//           </button>
//         </div>

//         <div className="p-6 space-y-6">
//           <div className="text-center">
//             <div className="inline-flex items-center justify-center w-16 h-16 bg-orange-100 dark:bg-orange-900 rounded-full mb-4">
//               <Mail size={32} className="text-orange-500" />
//             </div>
//             <p className="text-sm text-gray-600 dark:text-gray-400">
//               Chúng tôi đã gửi mã xác thực 6 số đến
//             </p>
//             <p className="font-semibold text-foreground mt-1">{email}</p>
//           </div>

//           <div>
//             <label className="block text-sm font-semibold mb-3 text-center">
//               Nhập mã OTP
//             </label>
//             <div className="flex gap-2 justify-center" onPaste={handlePaste}>
//               {otp.map((digit, index) => (
//                 <input
//                   key={index}
//                   ref={(el) => { inputRefs.current[index] = el; }}
//                   type="text"
//                   inputMode="numeric"
//                   maxLength={1}
//                   value={digit}
//                   onChange={(e) => handleChange(index, e.target.value)}
//                   onKeyDown={(e) => handleKeyDown(index, e)}
//                   className="w-12 h-14 text-center text-2xl font-bold border-2 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition"
//                   disabled={isLoading}
//                 />
//               ))}
//             </div>
//           </div>

//           {error && <p className="text-red-500 text-sm text-center">{error}</p>}
//           {success && (
//             <p className="text-green-600 text-sm font-semibold text-center">
//               {success}
//             </p>
//           )}

//           <div className="text-center text-sm">
//             <p className="text-gray-600 dark:text-gray-400 mb-2">
//               Mã có hiệu lực trong:{" "}
//               <span className="font-bold text-orange-500">
//                 {formatTime(countdown)}
//               </span>
//             </p>
//             {canResend ? (
//               <button
//                 onClick={handleResend}
//                 disabled={isLoading}
//                 className="text-orange-500 hover:text-orange-600 font-semibold inline-flex items-center gap-2"
//               >
//                 <RefreshCw size={16} />
//                 Gửi lại mã OTP
//               </button>
//             ) : (
//               <p className="text-gray-500">
//                 Chưa nhận được mã?{" "}
//                 <span className="text-gray-400">
//                   Gửi lại sau {formatTime(countdown)}
//                 </span>
//               </p>
//             )}
//           </div>

//           <button
//             onClick={handleVerify}
//             disabled={isLoading || otp.join("").length !== 6}
//             className="w-full py-3 bg-orange-500 text-white font-bold rounded-lg hover:bg-orange-600 transition disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center gap-2"
//           >
//             {isLoading ? (
//               <>
//                 <Loader2 className="animate-spin" size={20} />
//                 Đang xác thực...
//               </>
//             ) : (
//               "Xác thực"
//             )}
//           </button>
//         </div>
//       </div>
//     </div>
//   );
// }