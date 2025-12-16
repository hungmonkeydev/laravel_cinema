"use client";

import { useState, useEffect } from "react";
import { ChevronLeft, ChevronRight } from "lucide-react";

const slides = [
  {
    id: 1,
    title: "IMAX TREASURE HUNT",
    subtitle: "KHỞI HÀNH - SẢN LƯNG - CHINH PHỤC",
    image: "/poster/thai-movie-poster-horror.jpg",
    badge: "TÌM HIỂU NGAY",
  },
  {
    id: 2,
    title: "NEW RELEASES",
    subtitle: "Những bộ phim mới nhất",
    image: "/poster/animated-movie-poster.png",
    badge: "XEM CHI TIẾT",
  },
  {
    id: 3,
    title: "Trái Tim Quê Quán",
    subtitle: "Những bộ phim mới nhất",
    image: "/poster/vietnamese-movie-poster-drama.jpg",
    badge: "XEM CHI TIẾT",
  },
  {
    id: 4,
    title: "Phá Đảm Sinh Nhật Mẹ",
    subtitle: "Những bộ phim mới nhất",
    image: "/poster/comedy-movie-poster.png",
    badge: "XEM CHI TIẾT",
  },
];

export default function Hero() {
  const [mounted, setMounted] = useState(false);
  const [current, setCurrent] = useState(0);

  useEffect(() => setMounted(true), []);

  // Auto slide chỉ chạy khi client đã mount
  useEffect(() => {
    if (!mounted) return;
    const timer = setInterval(() => {
      setCurrent((p) => (p + 1) % slides.length);
    }, 5000);
    return () => clearInterval(timer);
  }, [mounted]);

  // Nếu chưa mounted thì render placeholder
  if (!mounted)
    return (
      <div className="w-full h-[400px] md:h-[500px] bg-black/20 animate-pulse" />
    );

  const slide = slides[current];

  return (
    <section className="relative w-full h-[400px] md:h-[500px] overflow-hidden">
      {/* Background */}
      <div
        className="absolute inset-0 bg-cover bg-center transition-all duration-700"
        style={{ backgroundImage: `url(${slide.image})` }}
      >
        <div className="absolute inset-0 bg-black/50" />
      </div>

      {/* Text */}
      <div className="relative h-full flex flex-col justify-center items-center text-center px-4">
        <h1 className="text-4xl md:text-6xl font-bold text-white mb-4 text-balance">
          {slide.title}
        </h1>
        <p className="text-lg md:text-2xl text-gray-200 mb-8 uppercase tracking-widest">
          {slide.subtitle}
        </p>
        <button className="px-8 py-3 bg-primary text-primary-foreground font-bold rounded-lg hover:bg-primary/90 transition">
          {slide.badge}
        </button>
      </div>

      {/* Controls */}
      <button
        onClick={() =>
          setCurrent((p) => (p - 1 + slides.length) % slides.length)
        }
        className="absolute left-4 top-1/2 -translate-y-1/2 z-10 p-3 bg-black/50 hover:bg-black/70 rounded-full transition"
      >
        <ChevronLeft size={24} className="text-white" />
      </button>

      <button
        onClick={() => setCurrent((p) => (p + 1) % slides.length)}
        className="absolute right-4 top-1/2 -translate-y-1/2 z-10 p-3 bg-black/50 hover:bg-black/70 rounded-full transition"
      >
        <ChevronRight size={24} className="text-white" />
      </button>

      {/* Indicators */}
      <div className="absolute bottom-8 left-1/2 -translate-x-1/2 flex gap-2">
        {slides.map((_, i) => (
          <button
            key={i}
            onClick={() => setCurrent(i)}
            className={`w-3 h-3 rounded-full transition ${
              i === current ? "bg-primary" : "bg-white/50"
            }`}
          />
        ))}
      </div>
    </section>
  );
}
