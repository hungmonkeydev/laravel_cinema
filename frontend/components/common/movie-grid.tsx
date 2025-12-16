"use client";

import { useState, useEffect } from "react";
import { Star, Loader2, Film } from "lucide-react";
import MovieModal from "@/components/modal/movie-modal";

// 1. CẬP NHẬT INTERFACE CHO KHỚP API
interface Movie {
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

  releaseDate?: string;
  release_date?: string;
}

export default function MovieGrid() {
  const [movies, setMovies] = useState<Movie[]>([]);
  const [loading, setLoading] = useState(true);

  // Mặc định chọn "Tất cả" để tránh lỗi không hiện phim do ngày tháng
  const [selectedTab, setSelectedTab] = useState<
    "showing" | "upcoming" | "all"
  >("all");
  const [selectedMovie, setSelectedMovie] = useState<Movie | null>(null);
  const [favorites, setFavorites] = useState<number[]>([]);

  // 2. CẤU HÌNH API
const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL;
  // 3. HÀM XỬ LÝ ẢNH CHUẨN
  const getImageUrl = (path?: string | null) => {
    if (!path) return "/placeholder.svg";
    if (path.startsWith("http")) return path;

    // Xóa dấu / ở đầu path nếu có để tránh bị 2 dấu //
    const cleanPath = path.startsWith("/") ? path.substring(1) : path;
    return `${API_BASE_URL}/${cleanPath}`;
  };

  // 4. GỌI API LẤY PHIM
  useEffect(() => {
    const fetchMovies = async () => {
      try {
        const res = await fetch(`${API_BASE_URL}/api/movies`);
        const data = await res.json();

        console.log("DATA MOVIE GRID:", data);

        // Xử lý dữ liệu trả về (Laravel có thể trả mảng hoặc object { data: [] })
        const movieList = Array.isArray(data) ? data : data.data || [];
        setMovies(movieList);
      } catch (error) {
        console.error("Lỗi tải phim:", error);
      } finally {
        setLoading(false);
      }
    };

    fetchMovies();
  }, []);

  // 5. LOGIC LỌC PHIM
  const filteredMovies = movies.filter((movie) => {
    if (selectedTab === "all") return true;

    const dateString = movie.release_date || movie.releaseDate;
    if (!dateString) return true; // Không có ngày -> hiện luôn

    const releaseDate = new Date(dateString);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    if (selectedTab === "showing") {
      return releaseDate <= today;
    } else {
      return releaseDate > today;
    }
  });

  // Helper lấy ID chuẩn
  const getMovieId = (m: Movie) => m.movie_id || m.id || 0;

  const toggleFavorite = (id: number) => {
    setFavorites((prev) =>
      prev.includes(id) ? prev.filter((fav) => fav !== id) : [...prev, id]
    );
  };

  if (loading) {
    return (
      <div className="flex flex-col justify-center items-center h-64 gap-3">
        <Loader2 className="animate-spin text-primary" size={48} />
        <p className="text-muted-foreground">Đang tải danh sách phim...</p>
      </div>
    );
  }

  return (
    <>
      <section
        id="movies"
        className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16"
      >
        <div className="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
          <div>
            <h2 className="text-2xl md:text-3xl font-bold text-foreground mb-4 md:mb-2 flex items-center gap-2">
              <Film className="text-primary" /> PHIM CHIẾU RẠP
            </h2>

            <div className="flex gap-1 bg-muted/30 p-1 rounded-lg">
              <button
                onClick={() => setSelectedTab("all")}
                className={`px-4 py-2 rounded-md font-semibold text-sm transition ${
                  selectedTab === "all"
                    ? "bg-primary text-primary-foreground shadow-sm"
                    : "text-muted-foreground hover:bg-muted"
                }`}
              >
                Tất cả
              </button>
              <button
                onClick={() => setSelectedTab("showing")}
                className={`px-4 py-2 rounded-md font-semibold text-sm transition ${
                  selectedTab === "showing"
                    ? "bg-primary text-primary-foreground shadow-sm"
                    : "text-muted-foreground hover:bg-muted"
                }`}
              >
                Đang chiếu
              </button>
              <button
                onClick={() => setSelectedTab("upcoming")}
                className={`px-4 py-2 rounded-md font-semibold text-sm transition ${
                  selectedTab === "upcoming"
                    ? "bg-primary text-primary-foreground shadow-sm"
                    : "text-muted-foreground hover:bg-muted"
                }`}
              >
                Sắp chiếu
              </button>
            </div>
          </div>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
          {filteredMovies.map((movie) => {
            const mId = getMovieId(movie);
            const displayImage = movie.poster_url || movie.image;

            return (
              <div
                key={mId}
                className="group cursor-pointer bg-card rounded-xl overflow-hidden border border-border/50 shadow-sm hover:shadow-xl hover:border-primary/50 transition-all duration-300 transform hover:-translate-y-1"
                onClick={() => setSelectedMovie(movie)}
              >
                <div className="relative aspect-[2/3] overflow-hidden">
                  <img
                    src={getImageUrl(displayImage)}
                    alt={movie.title}
                    className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                    onError={(e) => {
                      e.currentTarget.src = "/placeholder.svg";
                    }}
                  />

                  <div className="absolute top-3 left-3 bg-primary/90 backdrop-blur-sm text-white px-2.5 py-1 rounded-md font-bold text-xs shadow-lg">
                    {movie.badge || "T13"}
                  </div>

                  <button
                    onClick={(e) => {
                      e.stopPropagation();
                      toggleFavorite(mId);
                    }}
                    className="absolute top-3 right-3 p-2 bg-black/40 hover:bg-red-500/80 backdrop-blur-sm rounded-full transition-all duration-300"
                  >
                    <Star
                      size={18}
                      className={
                        favorites.includes(mId)
                          ? "fill-white text-white"
                          : "text-white"
                      }
                    />
                  </button>

                  <div className="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                    <button className="px-6 py-3 bg-primary text-primary-foreground font-bold rounded-full shadow-lg hover:scale-105 transition-transform">
                      Mua vé ngay
                    </button>
                  </div>
                </div>

                <div className="p-4">
                  <h3 className="text-lg font-bold text-foreground mb-1 line-clamp-1 group-hover:text-primary transition-colors">
                    {movie.title}
                  </h3>

                  <div className="flex justify-between items-center text-sm">
                    <div className="flex items-center gap-1 text-yellow-500">
                      <Star size={14} fill="currentColor" />
                      <span className="font-bold">{movie.rating || "N/A"}</span>
                    </div>
                    <span className="text-muted-foreground text-xs bg-muted px-2 py-1 rounded">
                      {movie.duration ? `${movie.duration} phút` : "---"}
                    </span>
                  </div>
                  <div className="mt-2 text-xs text-muted-foreground line-clamp-1">
                    {movie.genre || "Thể loại: Đang cập nhật"}
                  </div>
                </div>
              </div>
            );
          })}
        </div>

        {!loading && filteredMovies.length === 0 && (
          <div className="flex flex-col items-center justify-center py-20 text-center border-2 border-dashed border-muted rounded-xl bg-muted/10">
            <Film size={64} className="text-muted-foreground mb-4 opacity-20" />
            <h3 className="text-xl font-semibold text-foreground">
              Không tìm thấy phim nào
            </h3>
            <p className="text-muted-foreground max-w-md mt-2">
              Dữ liệu đã có nhưng có thể bộ lọc ngày tháng chưa khớp. Hãy thử
              bấm tab "Tất cả".
            </p>
            <button
              onClick={() => setSelectedTab("all")}
              className="mt-4 text-primary hover:underline font-medium"
            >
              Xem tất cả phim
            </button>
          </div>
        )}
      </section>

      {selectedMovie && (
        <MovieModal
          movie={selectedMovie}
          onClose={() => setSelectedMovie(null)}
        />
      )}
    </>
  );
}
