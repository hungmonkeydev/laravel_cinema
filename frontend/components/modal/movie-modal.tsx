"use client";

import { useState, useEffect } from "react";
import { X, MapPin, ChevronLeft } from "lucide-react";
import SeatBooking from "@/components/booking/seat-booking";

// 1. CẤU HÌNH DOMAIN BACKEND
const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL;
// 2. INTERFACE
export interface Movie {
  id?: number;
  movie_id?: number;
  title: string;
  rating: number | string | null;
  poster_url?: string | null;
  image?: string | null;
  badge: string | null;
  genre: string | null;
  duration: number | string | null;
  director: string | null;
  description: string | null;
  description_vi?: string | null;
  releaseDate?: string;
  release_date?: string;
  cast?: string | null;
}

interface MovieModalProps {
  movie: Movie;
  onClose: () => void;
}

const CINEMAS = [
  "Galaxy Nguyễn Du",
  "Galaxy Sala",
  "Galaxy Tân Bình",
  "Galaxy Kinh Dương Vương",
  "Galaxy Quang Trung",
  "Galaxy Bến Tre",
  "Galaxy Mipec Long Biên",
  "Galaxy Đà Nẵng",
  "Galaxy Cà Mau",
];

export default function MovieModal({ movie, onClose }: MovieModalProps) {
  const [bookingState, setBookingState] = useState<"details" | "cinema" | "booking">("details");
  const [selectedCinema, setSelectedCinema] = useState<string>("");

  useEffect(() => {
    setBookingState("details");
    setSelectedCinema("");
  }, [movie]);

  const getImageUrl = (path?: string | null) => {
    if (!path) return "/placeholder.svg";
    if (path.startsWith("http")) return path;
    const cleanPath = path.startsWith("/") ? path.substring(1) : path;
    return `${API_BASE_URL}/${cleanPath}`;
  };

  const posterPath = movie.poster_url || movie.image;
  const movieId = movie.movie_id || movie.id || 0;

  const isMovieReleased = () => {
    const dateString = movie.release_date || movie.releaseDate;
    if (!dateString) return true;
    const releaseDate = new Date(dateString);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return releaseDate <= today;
  };

  const handleStartBooking = () => setBookingState("cinema");
  const handleSelectCinema = (cinemaName: string) => {
    setSelectedCinema(cinemaName);
    setBookingState("booking");
  };

  if (!movie) return null;

  return (
    <div
      className="fixed inset-0 bg-black/80 z-[60] flex items-center justify-center p-2 md:p-4 backdrop-blur-sm animate-in fade-in duration-200"
      onClick={onClose}
    >
      <div
        // 🔥 FIX QUAN TRỌNG: 
        // Khi bookingState === 'booking', dùng h-[90vh] (cố định chiều cao) thay vì max-h.
        // Điều này bắt buộc Flexbox phải tính toán không gian còn dư chính xác cho Footer.
        className={`bg-card rounded-xl shadow-2xl w-full max-w-4xl flex border border-border/50 relative overflow-hidden transition-all duration-300 ${
          bookingState === "details"
            ? "flex-col md:flex-row max-h-[90vh]"
            : "flex-col h-[85vh] md:h-[90vh]" // Set cố định chiều cao khi booking
        }`}
        onClick={(e) => e.stopPropagation()}
      >
        {/* Nút đóng */}
        <button
          onClick={onClose}
          className="absolute top-4 right-4 z-50 p-2 bg-black/50 hover:bg-black/70 rounded-full transition text-white"
        >
          <X size={20} />
        </button>

        {/* --- BƯỚC 1: CHI TIẾT --- */}
        {bookingState === "details" && (
          <>
            <div className="w-full md:w-2/5 h-64 md:h-auto relative shrink-0">
              <img
                src={getImageUrl(posterPath)}
                alt={movie.title}
                className="w-full h-full object-cover"
                onError={(e) => {
                  e.currentTarget.src = "/placeholder.svg";
                }}
              />
              <div className="absolute inset-0 bg-gradient-to-t from-black/90 via-transparent to-transparent md:hidden" />
              <div className="absolute bottom-4 left-4 right-4 md:hidden">
                <h2 className="text-2xl font-bold text-white mb-1 drop-shadow-md">
                  {movie.title}
                </h2>
                <p className="text-yellow-400 font-bold">
                  ★ {movie.rating || "N/A"}
                </p>
              </div>
            </div>

            <div className="w-full md:w-3/5 p-6 md:p-8 flex flex-col overflow-y-auto bg-card text-card-foreground">
              {/* Nội dung chi tiết phim... (Giữ nguyên như cũ) */}
              <div className="hidden md:block mb-4">
                 <h2 className="text-3xl font-bold mb-2 text-foreground">{movie.title}</h2>
                 <div className="flex items-center gap-3 text-sm">
                   <span className="bg-primary px-2 py-1 rounded text-primary-foreground font-bold text-xs">{movie.badge || "T13"}</span>
                   <span className="text-muted-foreground">{movie.genre}</span>
                   <span className="text-yellow-500 font-bold text-base ml-2">★ {movie.rating || "N/A"}</span>
                 </div>
              </div>

              <div className="space-y-4 mb-6">
                 <div className="grid grid-cols-2 gap-4 text-sm">
                    <div>
                       <p className="text-muted-foreground mb-1">Đạo diễn</p>
                       <p className="font-medium">{movie.director || "Đang cập nhật"}</p>
                    </div>
                    <div>
                       <p className="text-muted-foreground mb-1">Khởi chiếu</p>
                       <p className="font-medium">
                         {movie.release_date 
                             ? new Date(movie.release_date).toLocaleDateString("vi-VN") 
                             : (movie.releaseDate ? new Date(movie.releaseDate).toLocaleDateString("vi-VN") : "Sắp chiếu")}
                       </p>
                    </div>
                 </div>
                 <div className="text-sm">
                   <p className="text-muted-foreground mb-2">Nội dung</p>
                   <p className="leading-relaxed text-foreground/90 max-h-40 overflow-y-auto pr-2">
                     {movie.description_vi || movie.description || "Chưa có mô tả cho phim này."}
                   </p>
                 </div>
              </div>

              <div className="mt-auto pt-4 border-t border-border">
                {isMovieReleased() ? (
                  <button
                    onClick={handleStartBooking}
                    className="w-full py-3.5 bg-primary text-primary-foreground font-bold rounded-xl hover:bg-primary/90 transition shadow-lg transform active:scale-95 flex items-center justify-center gap-2"
                  >
                    <span>🎟️</span> MUA VÉ NGAY
                  </button>
                ) : (
                  <button
                    disabled
                    className="w-full py-3.5 bg-muted text-muted-foreground font-bold rounded-xl cursor-not-allowed border border-border"
                  >
                    PHIM CHƯA KHỞI CHIẾU
                  </button>
                )}
              </div>
            </div>
          </>
        )}

        {/* --- BƯỚC 2: CHỌN RẠP --- */}
        {bookingState === "cinema" && (
          <div className="flex flex-col w-full h-full p-6 md:p-8 animate-in slide-in-from-right duration-300 overflow-hidden">
            <div className="flex items-center gap-3 mb-6 shrink-0">
              <button
                onClick={() => setBookingState("details")}
                className="p-2 hover:bg-muted rounded-full transition"
              >
                <ChevronLeft size={24} />
              </button>
              <div>
                <h3 className="text-2xl font-bold text-foreground">
                  Chọn rạp chiếu
                </h3>
                <p className="text-muted-foreground text-sm">
                  Bạn muốn xem "{movie.title}" ở đâu?
                </p>
              </div>
            </div>
            {/* Thêm flex-1 min-h-0 ở đây để danh sách rạp cũng scroll tốt */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-3 overflow-y-auto pr-2 flex-1 min-h-0 pb-4">
              {CINEMAS.map((cinemaName, index) => (
                <button
                  key={index}
                  onClick={() => handleSelectCinema(cinemaName)}
                  className="flex items-center p-4 border border-border rounded-xl hover:border-primary hover:bg-primary/5 transition group text-left bg-card h-fit"
                >
                  <div className="bg-primary/10 w-12 h-12 rounded-full flex items-center justify-center mr-4 group-hover:bg-primary/20 transition shrink-0">
                    <MapPin size={20} className="text-primary" />
                  </div>
                  <div>
                    <p className="font-bold text-foreground group-hover:text-primary transition">
                      {cinemaName}
                    </p>
                    <p className="text-xs text-muted-foreground mt-1">
                      Tiêu chuẩn • 2D • Phụ đề
                    </p>
                  </div>
                </button>
              ))}
            </div>
          </div>
        )}

        {/* --- BƯỚC 3: CHỌN GHẾ --- */}
        {bookingState === "booking" && (
          // 🔥 FIX QUAN TRỌNG:
          // Thêm 'flex-1 min-h-0'. Điều này bảo với flex container rằng 
          // "Div này sẽ chiếm toàn bộ không gian còn lại, nhưng nếu nội dung quá dài thì hãy co lại (min-h-0) để scroll bên trong hoạt động"
          <div className="w-full flex-1 min-h-0 flex flex-col animate-in slide-in-from-right duration-300 overflow-hidden bg-background">
            <SeatBooking
              movie={{
                id: movieId,
                title: movie.title,
                image: getImageUrl(posterPath),
                badge: movie.badge || "T13",
                duration: String(movie.duration) || "0",
                director: movie.director || "",
              }}
              cinema={selectedCinema}
              onBack={() => setBookingState("cinema")}
            />
          </div>
        )}
      </div>
    </div>
  );
}