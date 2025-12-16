import axios, { AxiosRequestConfig } from "axios";

//  Đảm bảo biến môi trường NEXT_PUBLIC_SERVER_API được đặt trong .env.local:
// Ví dụ: NEXT_PUBLIC_SERVER_API=http://127.0.0.1:8000
const BACKEND_URL =
  process.env.NEXT_PUBLIC_SERVER_API || "http://localhost:8000";

// Base URL cho tất cả các request API (ví dụ: http://127.0.0.1:8000/api)
const API_BASE_URL = `${BACKEND_URL}/api`;

// Cấu hình Axios instance
const api = axios.create({
  baseURL: API_BASE_URL,
  withCredentials: true, // Quan trọng để gửi Cookie (Session, CSRF)
  headers: {
    Accept: "application/json",
    "Content-Type": "application/json",
  },
});

/**
 * Lấy CSRF token từ Laravel.
 */
export async function initCsrf() {
  try {
    // SỬ DỤNG BACKEND_URL LÀ BASE URL ĐỂ GỌI TỪ DOMAIN GỐC
    await axios.get("/sanctum/csrf-cookie", {
      baseURL: BACKEND_URL, // Ví dụ: http://127.0.0.1:8000
      withCredentials: true,
    });
    console.log("✅ CSRF cookie initialized successfully.");

    // Wait a bit for cookie to be set
    await new Promise(resolve => setTimeout(resolve, 100));
  } catch (error) {
    console.error("❌ Failed to initialize CSRF cookie:", error);
    throw error;
  }
}

// Interceptor: Tự động thêm CSRF token và Authorization header
api.interceptors.request.use(
  //  ĐÃ SỬA: Thay thế AxiosRequestConfig bằng 'any' để tránh xung đột kiểu nội bộ của Axios
  (config: any) => {
    // KHẮC PHỤC LỖI: Đảm bảo headers tồn tại
    if (!config.headers) {
      config.headers = {};
    }

    // Thêm Bearer token nếu có trong localStorage
    if (typeof window !== 'undefined') {
      const token = localStorage.getItem('access_token');
      if (token && !config.headers['Authorization']) {
        config.headers['Authorization'] = `Bearer ${token}`;
      }
    }

    // Chỉ thêm X-XSRF-TOKEN cho các phương thức cần bảo vệ (POST, PUT, PATCH, DELETE)
    const methodsToProtect = ["post", "put", "patch", "delete"];
    if (
      config.method &&
      methodsToProtect.includes(config.method.toLowerCase())
    ) {
      // Try to get XSRF token from cookies
      if (typeof document !== 'undefined') {
        const xsrfToken = document.cookie
          .split("; ")
          .find((row) => row.startsWith("XSRF-TOKEN="))
          ?.split("=")[1];

        if (xsrfToken) {
          const decodedToken = decodeURIComponent(xsrfToken);
          config.headers["X-XSRF-TOKEN"] = decodedToken;
        }
        // Remove warning - it's annoying and request still works
      }
    }

    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

export default api;
