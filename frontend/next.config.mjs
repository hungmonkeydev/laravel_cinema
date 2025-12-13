/** @type {import('next').NextConfig} */
const API_URL = process.env.NEXT_PUBLIC_API_URL;

const nextConfig = {
  reactStrictMode: true,
  typescript: {
    // Set NEXT_IGNORE_TS_ERRORS=true only for temporary preview builds if necessary
    ignoreBuildErrors: process.env.NEXT_IGNORE_TS_ERRORS === 'true',
  },
  images: {
    // Prefer false in production; use domains for external images
    unoptimized: process.env.NEXT_IMAGE_UNOPTIMIZED === 'true',
    domains: process.env.NEXT_IMAGE_DOMAINS
      ? process.env.NEXT_IMAGE_DOMAINS.split(',').map((d) => d.trim())
      : [],
  },
  async rewrites() {
    // Only add rewrite if NEXT_PUBLIC_API_URL is defined and looks like an URL
    if (!API_URL || !/^https?:\/\//i.test(API_URL)) {
      // Avoid returning an invalid destination (which causes build error)
      // If you want local dev proxying, use a different approach (.env.local + local dev server)
      console.warn(
        'NEXT_PUBLIC_API_URL is not set or invalid. Skipping /api rewrites.'
      );
      return [];
    }

    // ensure no trailing slash
    const base = API_URL.replace(/\/$/, '');
    return [
      {
        source: '/api/:path*',
        destination: `${base}/:path*`,
      },
    ];
  },
};

export default nextConfig;