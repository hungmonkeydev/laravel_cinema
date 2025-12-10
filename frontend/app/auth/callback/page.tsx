"use client";

import { Suspense, useEffect, useState } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import api, { initCsrf } from '@/lib/api';

function CallbackHandler() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const [error, setError] = useState('');
  const [debugInfo, setDebugInfo] = useState('');

  useEffect(() => {
    const handleCallback = async () => {
      const status = searchParams.get('login');
      const message = searchParams.get('message');
      const token = searchParams.get('token');

      console.log('🔍 OAuth Callback - Status:', status);
      console.log('🔍 OAuth Callback - Token:', token ? 'Received' : 'Not received');
      setDebugInfo(`Status: ${status}, Token: ${token ? 'Yes' : 'No'}`);

      if (status === 'success' && token) {
        try {
          console.log('💾 Saving token to localStorage...');
          localStorage.setItem('access_token', token);

          console.log('🔄 Initializing CSRF...');
          await initCsrf();

          console.log('🔄 Fetching user data from /api/user...');
          // Token sẽ tự động được thêm vào header bởi api interceptor
          const response = await api.get('/user');
          const userData = response.data;

          console.log('✅ User data received:', userData);
          setDebugInfo(`User: ${userData.full_name || userData.email}`);

          localStorage.setItem('user_info', JSON.stringify(userData));
          console.log('💾 Saved user info to localStorage');

          // Wait a bit to see the debug info
          setTimeout(() => {
            console.log('🔄 Redirecting to homepage...');
            window.location.href = '/';
          }, 1500);
        } catch (err: any) {
          console.error('❌ Failed to fetch user data:', err);
          console.error('❌ Error response:', err.response?.data);
          setError('Không thể lấy thông tin người dùng: ' + (err.response?.data?.message || err.message));
          setTimeout(() => router.push('/'), 5000);
        }
      } else if (status === 'error') {
        console.error('❌ OAuth error:', message);
        setError(message || 'Đăng nhập thất bại');
        setTimeout(() => router.push('/'), 3000);
      } else if (status === 'success' && !token) {
        console.error('❌ No token received from backend');
        setError('Lỗi: Không nhận được token từ server');
        setTimeout(() => router.push('/'), 3000);
      }
    };

    handleCallback();
  }, [searchParams, router]);

  return (
    <div className="flex items-center justify-center min-h-screen bg-gray-50">
      <div className="text-center bg-white p-8 rounded-lg shadow-lg max-w-md">
        {error ? (
          <>
            <p className="text-red-500 font-semibold text-lg mb-2">{error}</p>
            <p className="mt-2 text-gray-600">Đang chuyển về trang chủ...</p>
          </>
        ) : (
          <>
            <div className="animate-spin rounded-full h-16 w-16 border-b-4 border-orange-500 mx-auto"></div>
            <p className="mt-6 text-gray-700 font-semibold text-lg">Đang xử lý đăng nhập...</p>
            {debugInfo && (
              <div className="mt-4 p-3 bg-gray-100 rounded text-xs text-left break-all">
                <p className="font-mono text-gray-600">{debugInfo}</p>
              </div>
            )}
          </>
        )}
      </div>
    </div>
  );
}

export default function AuthCallback() {
  return (
    <Suspense fallback={
      <div className="flex items-center justify-center min-h-screen bg-gray-50">
        <div className="text-center">
          <div className="animate-spin rounded-full h-16 w-16 border-b-4 border-orange-500 mx-auto"></div>
          <p className="mt-6 text-gray-700 font-semibold text-lg">Đang xử lý...</p>
        </div>
      </div>
    }>
      <CallbackHandler />
    </Suspense>
  );
}
