"use client";

import { useState, useEffect } from "react";
import { X, Search, Loader2 } from "lucide-react";
import MovieModal from "@/components/modal/movie-modal";

// 1. DÙNG CHUNG INTERFACE VỚI MOVIE GRID
interface MovieType {
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

interface SearchModalProps {
  isOpen: boolean;
  onClose: () => void;
}

export default function SearchModal({ isOpen, onClose }: SearchModalProps) {
  const [query, setQuery] = useState("");
  const [results, setResults] = useState<MovieType[]>([]);
  const [selectedMovie, setSelectedMovie] = useState<MovieType | null>(null);
  const [loading, setLoading] = useState(false);

  // 2. CẤU HÌNH API
const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL;
  // Hàm gọi API tìm kiếm
  const handleSearch = async (value: string) => {
    setQuery(value);

    if (value.trim().length === 0) {
      setResults([]);
      return;
    }

    setLoading(true);
    try {
      // Gọi API tìm kiếm của Laravel
      const res = await fetch(
        `${API_BASE_URL}/api/movies/search?query=${value}`
      );
      const data = await res.json();

      if (Array.isArray(data)) {
        setResults(data);
      } else if (data.data && Array.isArray(data.data)) {
        setResults(data.data);
      } else {
        setResults([]);
      }
    } catch (error) {
      console.error("Lỗi tìm kiếm:", error);
      setResults([]);
    } finally {
      setLoading(false);
    }
  };

  const handleSelectMovie = (movie: MovieType) => {
    setSelectedMovie(movie);
    setQuery("");
    setResults([]);
  };

  if (!isOpen) return null;

  if (selectedMovie) {
    return (
      <MovieModal
        movie={selectedMovie}
        onClose={() => {
          setSelectedMovie(null);
          onClose(); // Đóng luôn search modal nếu muốn
        }}
      />
    );
  }

  return (
    <div
      className="fixed inset-0 bg-black/60 z-50 flex items-start justify-center pt-24 backdrop-blur-sm animate-in fade-in duration-200"
      onClick={onClose}
    >
      <div
        className="bg-card rounded-xl shadow-2xl w-full max-w-lg mx-4 border border-border"
        onClick={(e) => e.stopPropagation()}
      >
        <div className="p-4 border-b border-border flex items-center justify-between">
          <h3 className="text-lg font-bold text-foreground">Tìm kiếm phim</h3>
          <button
            onClick={onClose}
            className="p-2 hover:bg-muted rounded-full transition"
          >
            <X size={20} />
          </button>
        </div>

        <div className="p-4 space-y-4">
          <div className="flex items-center gap-3 bg-muted/50 rounded-xl px-4 py-3 border border-transparent focus-within:border-primary transition-colors">
            <Search size={20} className="text-muted-foreground" />
            <input
              type="text"
              placeholder="Nhập tên phim muốn tìm..."
              value={query}
              onChange={(e) => handleSearch(e.target.value)}
              autoFocus
              className="flex-1 bg-transparent outline-none text-foreground placeholder-muted-foreground text-base"
            />
            {loading && (
              <Loader2 size={18} className="animate-spin text-primary" />
            )}
          </div>

          <div className="max-h-[60vh] overflow-y-auto space-y-1 pr-1 custom-scrollbar">
            {results.length > 0 ? (
              results.map((movie) => {
                // Xử lý ảnh nhỏ để hiển thị thumbnail
                const posterPath = movie.poster_url || movie.image;
                const imageUrl = posterPath
                  ? posterPath.startsWith("http")
                    ? posterPath
                    : `${API_BASE_URL}/${
                        posterPath.startsWith("/")
                          ? posterPath.substring(1)
                          : posterPath
                      }`
                  : "/placeholder.svg";

                return (
                  <button
                    key={movie.id || movie.movie_id}
                    onClick={() => handleSelectMovie(movie)}
                    className="w-full flex items-center gap-4 p-3 hover:bg-muted/50 rounded-lg transition group text-left"
                  >
                    <img
                      src={imageUrl}
                      alt={movie.title}
                      className="w-12 h-16 object-cover rounded bg-muted"
                      onError={(e) => {
                        e.currentTarget.src = "/placeholder.svg";
                      }}
                    />
                    <div>
                      <h4 className="font-semibold text-foreground group-hover:text-primary transition">
                        {movie.title}
                      </h4>
                      <p className="text-xs text-muted-foreground mt-1 line-clamp-1">
                        {movie.genre || "Đang cập nhật"} •{" "}
                        {movie.duration ? `${movie.duration} phút` : ""}
                      </p>
                    </div>
                  </button>
                );
              })
            ) : query && !loading ? (
              <div className="text-center py-8">
                <span className="text-4xl block mb-2">🤔</span>
                <p className="text-muted-foreground">
                  Không tìm thấy phim nào phù hợp.
                </p>
              </div>
            ) : (
              <div className="text-center py-8 text-muted-foreground text-sm">
                Nhập từ khóa để bắt đầu tìm kiếm
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
