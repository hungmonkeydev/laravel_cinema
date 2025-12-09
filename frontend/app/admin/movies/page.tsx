"use client";

import { useEffect, useState } from "react";
import api from "@/lib/api";
import { Pencil, Trash2, Plus, X } from "lucide-react";

interface Movie {
  movie_id: number;
  title: string;
  title_vi: string;
  genre: string;
  duration_minutes: number;
  release_date: string;
  rating: number | null;
  status: "showing" | "upcoming" | "ended";
}

export default function MoviesPage() {
  const [movies, setMovies] = useState<Movie[]>([]);
  const [loading, setLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);
  const [editingMovie, setEditingMovie] = useState<Movie | null>(null);
  const [formData, setFormData] = useState({
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
    status: "upcoming" as "showing" | "upcoming" | "ended",
    language: "Vietnamese",
    age_rating: "" as "" | "C13" | "C16" | "C18" | "P",
  });

  useEffect(() => {
    fetchMovies();
  }, []);

  const fetchMovies = async () => {
    try {
      const response = await api.get("/admin/movies");
      setMovies(response.data.data || response.data);
    } catch (error) {
      console.error("Error fetching movies:", error);
      alert("Lỗi khi tải danh sách phim!");
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

  const handleEdit = (movie: Movie) => {
    setEditingMovie(movie);
    setFormData({
      title: movie.title,
      title_vi: movie.title_vi,
      description: "",
      description_vi: "",
      genre: movie.genre,
      duration_minutes: movie.duration_minutes,
      release_date: movie.release_date,
      director: "",
      cast: "",
      rating: movie.rating?.toString() || "",
      poster_url: "",
      trailer_url: "",
      status: movie.status,
      language: "Vietnamese",
      age_rating: "" as any,
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
      age_rating: "",
    });
    setShowModal(true);
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    try {
      const submitData: any = {
        ...formData,
        duration_minutes: Number(formData.duration_minutes),
        rating: formData.rating ? parseFloat(formData.rating) : null,
      };
      if (!submitData.age_rating) delete submitData.age_rating;

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

  if (loading) {
    return <div className="text-center py-12 text-gray-700">Đang tải...</div>;
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-3xl font-bold text-gray-900">Quản lý Phim</h1>
        <button
          onClick={handleCreate}
          className="flex items-center space-x-2 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition"
        >
          <Plus size={20} />
          <span>Thêm Phim</span>
        </button>
      </div>

      <div className="bg-white rounded-lg shadow overflow-x-auto">
        <table className="w-full">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                ID
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                Tiêu đề
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                Thể loại
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                Thời lượng
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                Trạng thái
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                Hành động
              </th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-200">
            {movies.map((movie) => (
              <tr key={movie.movie_id}>
                <td className="px-6 py-4 text-sm text-gray-900">
                  {movie.movie_id}
                </td>
                <td className="px-6 py-4 text-sm text-gray-900">
                  {movie.title_vi}
                </td>
                <td className="px-6 py-4 text-sm text-gray-900">{movie.genre}</td>
                <td className="px-6 py-4 text-sm text-gray-900">
                  {movie.duration_minutes} phút
                </td>
                <td className="px-6 py-4">
                  <span
                    className={`px-2 py-1 text-xs rounded-full ${
                      movie.status === "showing"
                        ? "bg-green-100 text-green-800"
                        : movie.status === "upcoming"
                        ? "bg-blue-100 text-blue-800"
                        : "bg-gray-100 text-gray-800"
                    }`}
                  >
                    {movie.status}
                  </span>
                </td>
                <td className="px-6 py-4 text-sm">
                  <div className="flex items-center space-x-2">
                    <button
                      onClick={() => handleEdit(movie)}
                      className="p-2 text-blue-600 hover:bg-blue-50 rounded transition"
                    >
                      <Pencil size={16} />
                    </button>
                    <button
                      onClick={() => handleDelete(movie.movie_id)}
                      className="p-2 text-red-600 hover:bg-red-50 rounded transition"
                    >
                      <Trash2 size={16} />
                    </button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {/* Simplified Modal */}
      {showModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div className="flex items-center justify-between mb-4">
              <h2 className="text-xl font-bold text-gray-900">
                {editingMovie ? "Sửa Phim" : "Thêm Phim"}
              </h2>
              <button
                onClick={() => setShowModal(false)}
                className="p-1 hover:bg-gray-100 rounded"
              >
                <X size={20} />
              </button>
            </div>

            <form onSubmit={handleSubmit} className="space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Tiêu đề (EN)
                  </label>
                  <input
                    type="text"
                    value={formData.title}
                    onChange={(e) =>
                      setFormData({ ...formData, title: e.target.value })
                    }
                    required
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Tiêu đề (VI)
                  </label>
                  <input
                    type="text"
                    value={formData.title_vi}
                    onChange={(e) =>
                      setFormData({ ...formData, title_vi: e.target.value })
                    }
                    required
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Thể loại
                  </label>
                  <input
                    type="text"
                    value={formData.genre}
                    onChange={(e) =>
                      setFormData({ ...formData, genre: e.target.value })
                    }
                    required
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Thời lượng (phút)
                  </label>
                  <input
                    type="number"
                    value={formData.duration_minutes}
                    onChange={(e) =>
                      setFormData({
                        ...formData,
                        duration_minutes: parseInt(e.target.value) || 0,
                      })
                    }
                    required
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Ngày phát hành
                  </label>
                  <input
                    type="date"
                    value={formData.release_date}
                    onChange={(e) =>
                      setFormData({ ...formData, release_date: e.target.value })
                    }
                    required
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Trạng thái
                  </label>
                  <select
                    value={formData.status}
                    onChange={(e) =>
                      setFormData({
                        ...formData,
                        status: e.target.value as any,
                      })
                    }
                    className="w-full px-3 py-2 border border-gray-300 rounded-lg"
                  >
                    <option value="upcoming">Sắp chiếu</option>
                    <option value="showing">Đang chiếu</option>
                    <option value="ended">Đã kết thúc</option>
                  </select>
                </div>
              </div>

              <div className="flex space-x-3 pt-4">
                <button
                  type="submit"
                  className="flex-1 bg-orange-500 text-white py-2 rounded-lg hover:bg-orange-600 transition"
                >
                  {editingMovie ? "Cập nhật" : "Tạo mới"}
                </button>
                <button
                  type="button"
                  onClick={() => setShowModal(false)}
                  className="flex-1 bg-gray-200 text-gray-700 py-2 rounded-lg hover:bg-gray-300 transition"
                >
                  Hủy
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
