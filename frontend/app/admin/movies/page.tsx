"use client";

import { useEffect, useState } from "react";
// Giả sử bạn có file api config, nếu chưa có thì dùng fetch thường
import api from "@/lib/api"; 
import { Pencil, Trash2, Plus, X, Save } from "lucide-react";

interface Movie {
  movie_id: number;
  title: string;
  title_vi: string;
  genre: string;
  duration_minutes: number;
  release_date: string;
  rating: number | null;
  status: "showing" | "upcoming" | "ended";
  // Các field hiển thị thêm nếu cần
  poster_url?: string;
  age_rating?: string;
}

export default function MoviesPage() {
  const [movies, setMovies] = useState<Movie[]>([]);
  const [loading, setLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);
  const [editingMovie, setEditingMovie] = useState<Movie | null>(null);

  // State Form đầy đủ theo Database
  const [formData, setFormData] = useState({
    title: "",
    title_vi: "",
    description: "",
    description_vi: "",
    genre: "",
    duration_minutes: 0,
    release_date: "",
    director: "",
    cast: "", // Database là TEXT
    rating: "", // Database là VARCHAR nhưng logic thường là số
    poster_url: "",
    trailer_url: "",
    status: "upcoming",
    language: "Vietnamese",
    age_rating: "P",
  });

  useEffect(() => {
    fetchMovies();
  }, []);

  const fetchMovies = async () => {
    try {
      // Thay bằng endpoint thực tế của bạn
      const response = await api.get("/admin/movies");
      setMovies(response.data.data || response.data);
    } catch (error) {
      console.error("Error fetching movies:", error);
      // alert("Lỗi khi tải danh sách phim!"); // Tạm tắt alert đỡ phiền
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm("Bạn có chắc muốn xóa phim này?")) return;
    try {
      await api.delete(`/admin/movies/${id}`);
      alert("Xóa phim thành công!");
      fetchMovies();
    } catch (error) {
      console.error("Error deleting movie:", error);
      alert("Lỗi khi xóa phim!");
    }
  };

  // Hàm đổ dữ liệu vào form khi sửa
  const handleEdit = (movie: any) => {
    setEditingMovie(movie);
    setFormData({
      title: movie.title || "",
      title_vi: movie.title_vi || "",
      description: movie.description || "",
      description_vi: movie.description_vi || "",
      genre: movie.genre || "",
      duration_minutes: movie.duration_minutes || 0,
      release_date: movie.release_date ? movie.release_date.split('T')[0] : "", // Format ngày
      director: movie.director || "",
      cast: movie.cast || "",
      rating: movie.rating ? movie.rating.toString() : "",
      poster_url: movie.poster_url || "",
      trailer_url: movie.trailer_url || "",
      status: movie.status || "upcoming",
      language: movie.language || "Vietnamese",
      age_rating: movie.age_rating || "P",
    });
    setShowModal(true);
  };

  const handleCreate = () => {
    setEditingMovie(null);
    setFormData({
      title: "",
      title_vi: "",
      description: "",
      description_vi: "",
      genre: "",
      duration_minutes: 0,
      release_date: "",
      director: "",
      cast: "",
      rating: "",
      poster_url: "",
      trailer_url: "",
      status: "upcoming",
      language: "Vietnamese",
      age_rating: "P",
    });
    setShowModal(true);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      // Chuẩn hóa dữ liệu trước khi gửi lên Server
      const submitData = {
        ...formData,
        duration_minutes: Number(formData.duration_minutes),
        rating: formData.rating ? parseFloat(formData.rating) : null,
      };

      if (editingMovie) {
        await api.put(`/admin/movies/${editingMovie.movie_id}`, submitData);
        alert("Cập nhật phim thành công!");
      } else {
        await api.post("/admin/movies", submitData);
        alert("Tạo phim thành công!");
      }
      setShowModal(false);
      fetchMovies();
    } catch (error: any) {
      console.error("Error saving movie:", error);
      const errorMsg = error.response?.data?.message || "Lỗi khi lưu phim!";
      alert(errorMsg);
    }
  };

  if (loading) return <div className="text-center py-12">Đang tải...</div>;

  return (
    <div className="space-y-6 p-6">
      <div className="flex items-center justify-between">
        <h1 className="text-3xl font-bold text-gray-800">Quản lý Phim</h1>
        <button
          onClick={handleCreate}
          className="flex items-center space-x-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition shadow"
        >
          <Plus size={20} />
          <span>Thêm Phim</span>
        </button>
      </div>

      {/* Table Danh sách */}
      <div className="bg-white rounded-xl shadow border border-gray-100 overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left">
            <thead className="bg-gray-50 border-b border-gray-200">
              <tr>
                <th className="px-6 py-4 font-semibold text-gray-600 text-sm">ID</th>
                <th className="px-6 py-4 font-semibold text-gray-600 text-sm">Tên phim (VI)</th>
                <th className="px-6 py-4 font-semibold text-gray-600 text-sm">Thể loại</th>
                <th className="px-6 py-4 font-semibold text-gray-600 text-sm">Thời lượng</th>
                <th className="px-6 py-4 font-semibold text-gray-600 text-sm">Trạng thái</th>
                <th className="px-6 py-4 font-semibold text-gray-600 text-sm text-center">Hành động</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200">
              {movies.map((movie) => (
                <tr key={movie.movie_id} className="hover:bg-gray-50 transition">
                  <td className="px-6 py-4 text-gray-500">#{movie.movie_id}</td>
                  <td className="px-6 py-4 font-medium text-gray-900">{movie.title_vi}</td>
                  <td className="px-6 py-4 text-gray-600">{movie.genre}</td>
                  <td className="px-6 py-4 text-gray-600">{movie.duration_minutes} phút</td>
                  <td className="px-6 py-4">
                    <span className={`px-3 py-1 text-xs rounded-full font-medium ${
                      movie.status === "showing" ? "bg-green-100 text-green-700" :
                      movie.status === "upcoming" ? "bg-blue-100 text-blue-700" :
                      "bg-gray-100 text-gray-700"
                    }`}>
                      {movie.status === "showing" ? "Đang chiếu" : movie.status === "upcoming" ? "Sắp chiếu" : "Đã chiếu"}
                    </span>
                  </td>
                  <td className="px-6 py-4 text-center">
                    <div className="flex justify-center space-x-2">
                      <button onClick={() => handleEdit(movie)} className="p-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                        <Pencil size={18} />
                      </button>
                      <button onClick={() => handleDelete(movie.movie_id)} className="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                        <Trash2 size={18} />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Modal Full Fields */}
      {showModal && (
        <div className="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4 backdrop-blur-sm">
          <div className="bg-white rounded-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto shadow-2xl animate-in fade-in zoom-in duration-200">
            {/* Modal Header */}
            <div className="sticky top-0 bg-white border-b px-6 py-4 flex items-center justify-between z-10">
              <h2 className="text-xl font-bold text-gray-800">
                {editingMovie ? "Cập nhật Phim" : "Thêm Phim Mới"}
              </h2>
              <button onClick={() => setShowModal(false)} className="p-2 hover:bg-gray-100 rounded-full transition">
                <X size={24} className="text-gray-500" />
              </button>
            </div>

            {/* Modal Body */}
            <form onSubmit={handleSubmit} className="p-6 space-y-6">
              
              {/* Nhóm 1: Thông tin cơ bản */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Tên gốc (EN) <span className="text-red-500">*</span></label>
                  <input type="text" required value={formData.title} 
                    onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                    className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 outline-none" placeholder="VD: Avatar 2" />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Tên tiếng Việt <span className="text-red-500">*</span></label>
                  <input type="text" required value={formData.title_vi}
                    onChange={(e) => setFormData({ ...formData, title_vi: e.target.value })}
                    className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 outline-none" placeholder="VD: Avatar: Dòng chảy của nước" />
                </div>
              </div>

              {/* Nhóm 2: Chi tiết phân loại */}
              <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div className="col-span-2 md:col-span-1">
                  <label className="block text-sm font-medium text-gray-700 mb-1">Thể loại</label>
                  <input type="text" required value={formData.genre}
                    onChange={(e) => setFormData({ ...formData, genre: e.target.value })}
                    className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 outline-none" />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Thời lượng (phút)</label>
                  <input type="number" required value={formData.duration_minutes}
                    onChange={(e) => setFormData({ ...formData, duration_minutes: parseInt(e.target.value) || 0 })}
                    className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 outline-none" />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Phân loại tuổi</label>
                  <select value={formData.age_rating}
                    onChange={(e) => setFormData({ ...formData, age_rating: e.target.value })}
                    className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 outline-none">
                    <option value="P">P (Mọi lứa tuổi)</option>
                    <option value="K">K (Dưới 13 có người lớn)</option>
                    <option value="C13">C13 (13+)</option>
                    <option value="C16">C16 (16+)</option>
                    <option value="C18">C18 (18+)</option>
                  </select>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Điểm đánh giá</label>
                  <input type="number" step="0.1" max="10" value={formData.rating}
                    onChange={(e) => setFormData({ ...formData, rating: e.target.value })}
                    className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 outline-none" placeholder="VD: 8.5" />
                </div>
              </div>

              {/* Nhóm 3: Đạo diễn & Diễn viên & Ngôn ngữ */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Đạo diễn</label>
                  <input type="text" value={formData.director}
                    onChange={(e) => setFormData({ ...formData, director: e.target.value })}
                    className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 outline-none" />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Ngôn ngữ</label>
                  <input type="text" value={formData.language}
                    onChange={(e) => setFormData({ ...formData, language: e.target.value })}
                    className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 outline-none" placeholder="VD: Tiếng Anh, Phụ đề Tiếng Việt" />
                </div>
                <div className="md:col-span-2">
                  <label className="block text-sm font-medium text-gray-700 mb-1">Diễn viên (Cast)</label>
                  <input type="text" value={formData.cast}
                    onChange={(e) => setFormData({ ...formData, cast: e.target.value })}
                    className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 outline-none" placeholder="Danh sách diễn viên ngăn cách bởi dấu phẩy" />
                </div>
              </div>

              {/* Nhóm 4: Media & Ngày chiếu */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                 <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Ngày khởi chiếu <span className="text-red-500">*</span></label>
                  <input type="date" required value={formData.release_date}
                    onChange={(e) => setFormData({ ...formData, release_date: e.target.value })}
                    className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 outline-none" />
                </div>
                 <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                  <select value={formData.status}
                    onChange={(e) => setFormData({ ...formData, status: e.target.value as any })}
                    className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 outline-none">
                    <option value="upcoming">Sắp chiếu</option>
                    <option value="showing">Đang chiếu</option>
                    <option value="ended">Đã kết thúc</option>
                  </select>
                </div>
                <div className="md:col-span-2">
                  <label className="block text-sm font-medium text-gray-700 mb-1">Poster URL (Ảnh dọc)</label>
                  <input type="url" value={formData.poster_url}
                    onChange={(e) => setFormData({ ...formData, poster_url: e.target.value })}
                    className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 outline-none" placeholder="https://..." />
                </div>
                 <div className="md:col-span-2">
                  <label className="block text-sm font-medium text-gray-700 mb-1">Trailer URL (Youtube)</label>
                  <input type="url" value={formData.trailer_url}
                    onChange={(e) => setFormData({ ...formData, trailer_url: e.target.value })}
                    className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 outline-none" placeholder="https://youtube.com/..." />
                </div>
              </div>

              {/* Nhóm 5: Mô tả */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                 <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Mô tả (Tiếng Anh)</label>
                  <textarea rows={4} value={formData.description}
                    onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                    className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 outline-none resize-none" />
                </div>
                 <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Mô tả (Tiếng Việt)</label>
                  <textarea rows={4} value={formData.description_vi}
                    onChange={(e) => setFormData({ ...formData, description_vi: e.target.value })}
                    className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-orange-500 outline-none resize-none" />
                </div>
              </div>

              {/* Footer Actions */}
              <div className="sticky bottom-0 bg-white border-t pt-4 flex space-x-3">
                <button type="button" onClick={() => setShowModal(false)}
                  className="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition">
                  Hủy bỏ
                </button>
                <button type="submit"
                  className="flex-1 px-4 py-3 bg-orange-500 text-white rounded-lg hover:bg-orange-600 font-medium transition flex justify-center items-center shadow-md hover:shadow-lg">
                  <Save size={20} className="mr-2" />
                  {editingMovie ? "Lưu Cập Nhật" : "Tạo Phim Mới"}
                </button>
              </div>

            </form>
          </div>
        </div>
      )}
    </div>
  );
}