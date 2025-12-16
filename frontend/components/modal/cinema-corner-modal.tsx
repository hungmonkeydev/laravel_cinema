"use client";

import { X, Loader2 } from "lucide-react"; // Nhớ import Loader2
import { useState, useEffect } from "react";

interface CinemaCornerModalProps {
  section: string;
  onClose: () => void;
}

export default function CinemaCornerModal({
  section,
  onClose,
}: CinemaCornerModalProps) {
  // 1. Thay thế các biến mảng cứng bằng State
  const [data, setData] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  // 2. Gọi API khi modal mở ra hoặc section thay đổi
  useEffect(() => {
    const fetchData = async () => {
      setLoading(true);
      try {
        // Thay đổi đường dẫn này nếu port của bạn khác 8000
        const res = await fetch(
          `${process.env.NEXT_PUBLIC_API_URL}/api/cinema-corner/${section}`
        );
        const result = await res.json();
        setData(result);
      } catch (error) {
        console.error("Lỗi lấy dữ liệu:", error);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [section]);

  const getTitle = () => {
    switch (section) {
      case "genres":
        return "Thể Loại Phim";
      case "actors":
        return "Diễn Viên";
      case "directors":
        return "Đạo Diễn";
      case "reviews":
        return "Bình Luận Phim";
      case "blog":
        return "Blog Điện Ảnh";
      default:
        return "Góc Điện Ảnh";
    }
  };

  return (
    <div className="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
      <div className="bg-card rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div className="sticky top-0 bg-card border-b border-border p-6 flex justify-between items-center">
          <h2 className="text-2xl font-bold text-foreground">{getTitle()}</h2>
          <button
            onClick={onClose}
            className="p-1 hover:bg-muted rounded-lg transition"
          >
            <X size={24} className="text-foreground" />
          </button>
        </div>

        <div className="p-6">
          {/* 3. Hiển thị Loading */}
          {loading ? (
            <div className="flex justify-center items-center py-10">
              <Loader2 className="animate-spin text-primary" size={32} />
            </div>
          ) : (
            <>
              {/* 4. Render dữ liệu động từ State `data` */}

              {section === "genres" && (
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                  {data.map((genre: any, idx) => (
                    <div
                      key={idx}
                      className="p-4 bg-muted rounded-lg hover:bg-primary/10 cursor-pointer transition"
                    >
                      <p className="font-semibold text-foreground mb-1">
                        {genre.name}
                      </p>
                      <p className="text-sm text-muted-foreground">
                        {genre.count} phim
                      </p>
                    </div>
                  ))}
                </div>
              )}

              {(section === "actors" || section === "directors") && (
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                  {data.map((person: any, idx) => (
                    <div
                      key={idx}
                      className="p-4 bg-muted rounded-lg hover:bg-primary/10 cursor-pointer transition text-center"
                    >
                      <div className="w-16 h-16 bg-primary/20 rounded-full mx-auto mb-2 flex items-center justify-center text-xl font-bold text-primary">
                        {person.name.charAt(0)}
                      </div>
                      <p className="font-semibold text-foreground mb-1">
                        {person.name}
                      </p>
                      <p className="text-sm text-muted-foreground">
                        {person.movies} phim
                      </p>
                    </div>
                  ))}
                </div>
              )}

              {section === "reviews" && (
                <div className="space-y-4">
                  {data.map((review: any, idx) => (
                    <div
                      key={idx}
                      className="p-4 bg-muted rounded-lg hover:bg-primary/5 transition cursor-pointer"
                    >
                      <div className="flex items-start justify-between mb-2">
                        <h3 className="font-semibold text-foreground flex-1">
                          {review.title}
                        </h3>
                        <span className="text-primary font-bold ml-4">
                          ★ {review.rating}
                        </span>
                      </div>
                      <p className="text-sm text-muted-foreground mb-2">
                        Bởi {review.author}
                      </p>
                      <p className="text-foreground text-sm">
                        {review.excerpt}
                      </p>
                    </div>
                  ))}
                </div>
              )}

              {section === "blog" && (
                <div className="space-y-4">
                  {data.map((post: any, idx) => (
                    <div
                      key={idx}
                      className="p-4 bg-muted rounded-lg hover:bg-primary/5 transition cursor-pointer"
                    >
                      <h3 className="font-semibold text-foreground mb-2">
                        {post.title}
                      </h3>
                      <div className="flex items-center justify-between text-sm text-muted-foreground">
                        <span>{post.date}</span>
                        <span>{post.views?.toLocaleString()} lượt xem</span>
                      </div>
                    </div>
                  ))}
                </div>
              )}

              {/* Thông báo nếu không có dữ liệu */}
              {data.length === 0 && (
                <p className="text-center text-muted-foreground">
                  Chưa có dữ liệu nào.
                </p>
              )}
            </>
          )}
        </div>
      </div>
    </div>
  );
}
