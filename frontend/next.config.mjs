/** @type {import('next').NextConfig} */
const nextConfig = {
  reactStrictMode: true,
  swcMinify: true,
  typescript: {
    // Set NEXT_IGNORE_TS_ERRORS=true only for temporary preview builds if necessary
    ignoreBuildErrors: process.env.NEXT_IGNORE_TS_ERRORS === 'true',
  },
  eslint: {
    // Allow skipping ESLint during build if explicitly set (avoid by fixing lint issues)
    ignoreDuringBuilds: process.env.NEXT_IGNORE_ESLINT === 'true',
  },
  images: {
    // Prefer false in production; use domains for external images
    unoptimized: process.env.NEXT_IMAGE_UNOPTIMIZED === 'true',
    domains: process.env.NEXT_IMAGE_DOMAINS
      ? process.env.NEXT_IMAGE_DOMAINS.split(',').map((d) => d.trim())
      : [],
  },
  async rewrites() {
    // Proxy /api to your backend (set NEXT_PUBLIC_API_URL in Vercel)
    return [
      {
        source: '/api/:path*',
        destination: `${process.env.NEXT_PUBLIC_API_URL}/:path*`,
      },
    ]
  },
}

export default nextConfig