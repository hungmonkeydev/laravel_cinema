"use client";
import { useState, useMemo, useEffect } from "react";
import {
  Menu,
  X,
  Search,
  User,
  Heart,
  ChevronDown,
  LogOut,
} from "lucide-react";
import SearchModal from "@/components/modal/search-modal";
import LoginModal from "@/components/modal/login-modal";
import CinemaCornerModal from "@/components/modal/cinema-corner-modal";
import MovieModal from "@/components/modal/movie-modal";

// 1. Cấu hình đường dẫn API (Backend Laravel)
const API_BASE_URL = "http://127.0.0.1:8000";

// 2. Interface khớp với Controller Laravel
export interface Movie {
  id: number;
  title: string;
  rating: number | string | null;
  poster_url?: string | null; // Database field
  image?: string | null;      // API alias (Controller trả về cái này)
  badge: string | null;
  genre: string | null;
  duration: number | string | null;
  director: string | null;
  cast: string | null;
  description: string | null;
  description_vi?: string | null;
  releaseDate: string;
  trailer_url?: string | null;
  status: string;
}

export default function Header() {
  const [isOpen, setIsOpen] = useState(false);
  const [showSearch, setShowSearch] = useState(false);
  const [showLogin, setShowLogin] = useState(false);
  const [cinemaCornerSection, setCinemaCornerSection] = useState<string | null>(null);
  
  // State dữ liệu
  const [movies, setMovies] = useState<Movie[]>([]);
  const [selectedMovie, setSelectedMovie] = useState<Movie | null>(null);
  const [user, setUser] = useState<any | null>(null);

  const cinemas = [
    "Galaxy Nguyễn Du", "Galaxy Sala", "Galaxy Tân Bình",
    "Galaxy Kinh Dương Vương", "Galaxy Quang Trung", "Galaxy Bến Tre",
    "Galaxy Mipec Long Biên", "Galaxy Đà Nẵng", "Galaxy Cà Mau",
  ];

  // =========================================================
  // 3. HÀM XỬ LÝ ẢNH CHUẨN (Quan trọng nhất)
  // =========================================================
  const getImageUrl = (path?: string | null) => {
    // Nếu không có ảnh -> Trả về ảnh giữ chỗ (nhớ copy file placeholder.svg vào cả backend/frontend cho chắc)
    if (!path) return "/placeholder.svg"; 
    
    // Nếu ảnh là link online (http...) -> Giữ nguyên
    if (path.startsWith("http")) return path;
    
    // Nếu ảnh là đường dẫn nội bộ (/poster/...) -> Nối thêm domain Backend vào
    const cleanPath = path.startsWith("/") ? path.substring(1) : path;
    return `${API_BASE_URL}/${cleanPath}`;
  };

  // 4. Fetch API phim cho Dropdown Menu
  useEffect(() => {
    fetch(`${API_BASE_URL}/api/movies`)
      .then((res) => res.json())
      .then((data) => setMovies(data))
      .catch((err) => console.error("Lỗi tải phim header:", err));
  }, []);

  // Lọc phim cho Menu Dropdown
  const showingMovies = useMemo(() => {
    return movies.filter((movie) => movie.status === "now_showing" || movie.status === "showing");
  }, [movies]);

  const upcomingMovies = useMemo(() => {
    return movies.filter((movie) => movie.status === "coming_soon" || movie.status === "upcoming");
  }, [movies]);

  // --- Logic User (Login/Logout) giữ nguyên ---
  useEffect(() => {
    const checkLoginStatus = () => {
      const storedUser = localStorage.getItem("user_info");
      if (storedUser) {
        try {
          const parsedUser = JSON.parse(storedUser);
          if (parsedUser && (parsedUser.name || parsedUser.full_name || parsedUser.email)) {
            setUser(parsedUser);
          } else {
            handleLogout();
          }
        } catch (e) {
          handleLogout();
        }
      }
    };
    checkLoginStatus();
    const handleStorageChange = (e: StorageEvent) => {
      if (e.key === "user_info") checkLoginStatus();
    };
    const handleLoginEvent = () => {
      checkLoginStatus();
    };
    window.addEventListener("storage", handleStorageChange);
    window.addEventListener("userLoggedIn", handleLoginEvent);
    return () => {
      window.removeEventListener("storage", handleStorageChange);
      window.removeEventListener("userLoggedIn", handleLoginEvent);
    };
  }, []);

  const handleLogout = () => {
    localStorage.removeItem("user_info");
    localStorage.removeItem("access_token");
    setUser(null);
    window.location.reload(); // Reload trang để reset state sạch sẽ
  };

  const handleLoginSuccess = (userData: any) => {
    setUser(userData);
    setShowLogin(false);
  };

  const getDisplayName = () => {
    if (!user) return "";
    return user.name || user.full_name || user.fullname || user.email || "Khách hàng";
  };

  return (
    <>
      <header className="sticky top-0 z-50 bg-card/95 backdrop-blur border-b border-border">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center py-4">
            
            {/* Logo */}
            <div className="flex items-center space-x-2 cursor-pointer" onClick={() => window.location.href = '/'}>
              <div className="w-10 h-10 bg-primary rounded-full flex items-center justify-center">
                <span className="text-primary-foreground font-bold text-lg">G</span>
              </div>
              <span className="text-xl font-bold text-foreground hidden sm:inline">Vie Cinema</span>
            </div>

            {/* Desktop Nav */}
            <nav className="hidden md:flex space-x-8 items-center">
              
              {/* Dropdown Phim */}
              <div className="relative group">
                <button className="flex items-center space-x-1 text-foreground hover:text-primary transition py-2">
                  <span>Phim</span>
                  <ChevronDown size={18} />
                </button>
                {/* Mega Menu Dropdown */}
                <div className="absolute left-0 mt-0 w-[900px] bg-card border border-border rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 p-6 z-50">
                  <div className="mb-6">
                    <h3 className="text-lg font-bold text-primary mb-4 pb-2 border-l-4 border-primary pl-3">
                      PHIM ĐANG CHIẾU
                    </h3>
                    <div className="grid grid-cols-4 gap-4">
                      {showingMovies.slice(0, 4).map((movie) => (
                        <div
                          key={movie.id}
                          onClick={() => setSelectedMovie(movie)}
                          className="group cursor-pointer"
                        >
                          <div className="relative mb-2 overflow-hidden rounded-lg h-48 bg-muted">
                            {/* Dùng hàm getImageUrl đã sửa */}
                            <img
                              src={getImageUrl(movie.image || movie.poster_url)}
                              alt={movie.title}
                              className="w-full h-full object-cover group-hover:scale-110 transition duration-300"
                              onError={(e) => {
                                (e.target as HTMLImageElement).src = "/placeholder.svg";
                              }}
                            />
                            <div className="absolute bottom-1 right-1 bg-primary text-primary-foreground px-2 py-1 rounded text-xs font-bold">
                              {movie.badge || "T13"}
                            </div>
                          </div>
                          <p className="text-xs font-semibold text-foreground line-clamp-2">
                            {movie.title}
                          </p>
                          <div className="flex text-primary text-xs mt-1">
                            ★ {movie.rating || "N/A"}
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>
                  <div>
                    <h3 className="text-lg font-bold text-primary mb-4 pb-2 border-l-4 border-primary pl-3">
                      PHIM SẮP CHIẾU
                    </h3>
                    <div className="grid grid-cols-4 gap-4">
                      {upcomingMovies.slice(0, 4).map((movie) => (
                        <div
                          key={movie.id}
                          onClick={() => setSelectedMovie(movie)}
                          className="group cursor-pointer"
                        >
                          <div className="relative mb-2 overflow-hidden rounded-lg h-48 bg-muted">
                            <img
                              src={getImageUrl(movie.image || movie.poster_url)}
                              alt={movie.title}
                              className="w-full h-full object-cover group-hover:scale-110 transition duration-300"
                              onError={(e) => {
                                (e.target as HTMLImageElement).src = "/placeholder.svg";
                              }}
                            />
                            <div className="absolute bottom-1 right-1 bg-primary text-primary-foreground px-2 py-1 rounded text-xs font-bold">
                              {movie.badge || "T13"}
                            </div>
                          </div>
                          <p className="text-xs font-semibold text-foreground line-clamp-2">
                            {movie.title}
                          </p>
                          <div className="flex text-primary text-xs mt-1">
                            Sắp ra mắt
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>
                </div>
              </div>

              {/* Các menu khác */}
              <div className="relative group">
                <button className="flex items-center space-x-1 text-foreground hover:text-primary transition py-2">
                  <span>Góc Điện Ảnh</span> <ChevronDown size={18} />
                </button>
                <div className="absolute left-0 mt-0 w-48 bg-card border border-border rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 py-2 z-50">
                  <button
                    onClick={() => setCinemaCornerSection("genres")}
                    className="w-full text-left px-4 py-2 text-foreground hover:bg-muted hover:text-primary transition"
                  >
                    Thể Loại
                  </button>
                </div>
              </div>

              <div className="relative group">
                <button className="flex items-center space-x-1 text-foreground hover:text-primary transition py-2">
                  <span>Rạp/Giá Vé</span> <ChevronDown size={18} />
                </button>
                <div className="absolute left-0 mt-0 w-56 bg-card border border-border rounded-lg shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 py-2 z-50">
                  {cinemas.map((c) => (
                    <button
                      key={c}
                      className="w-full text-left px-4 py-2 hover:bg-muted text-sm"
                    >
                      {c}
                    </button>
                  ))}
                </div>
              </div>

              <a href="#" className="text-foreground hover:text-primary transition">
                Khuyến mãi
              </a>
            </nav>

            {/* Actions: Search, User */}
            <div className="flex items-center space-x-4">
              <button
                onClick={() => setShowSearch(true)}
                className="p-2 hover:bg-muted rounded-lg transition"
              >
                <Search size={20} />
              </button>
              
              {user ? (
                <div className="hidden sm:flex items-center gap-3">
                  <span className="text-sm font-semibold truncate max-w-[150px]">
                    {getDisplayName()}
                  </span>
                  <button
                    onClick={handleLogout}
                    className="p-2 hover:bg-red-500 hover:text-white rounded transition"
                    title="Đăng xuất"
                  >
                    <LogOut size={18} />
                  </button>
                </div>
              ) : (
                <button
                  onClick={() => setShowLogin(true)}
                  className="hidden sm:flex items-center space-x-2 px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition"
                >
                  <User size={18} />
                  <span>Đăng Nhập</span>
                </button>
              )}
              
              <button className="md:hidden" onClick={() => setIsOpen(!isOpen)}>
                {isOpen ? <X size={24} /> : <Menu size={24} />}
              </button>
            </div>
          </div>
        </div>
      </header>

      {/* Modals */}
      <SearchModal isOpen={showSearch} onClose={() => setShowSearch(false)} />
      
      <LoginModal
        isOpen={showLogin}
        onClose={() => setShowLogin(false)}
        onLoginSuccess={handleLoginSuccess}
      />
      
      {cinemaCornerSection && (
        <CinemaCornerModal
          section={cinemaCornerSection}
          onClose={() => setCinemaCornerSection(null)}
        />
      )}

      {selectedMovie && (
        <MovieModal
          movie={selectedMovie}
          onClose={() => setSelectedMovie(null)}
        />
      )}
    </>
  );
}