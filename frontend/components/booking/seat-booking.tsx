"use client";

import { useState, useEffect } from "react"; // <--- Nhớ import useEffect
import { ChevronLeft, CreditCard, Loader2, Clock } from "lucide-react";

interface BookingMovie {
  id: number;
  title: string;
  image: string;
  badge: string;
  duration: string;
  director: string;
}

interface SeatBookingProps {
  movie: BookingMovie;
  cinema: string;
  onBack: () => void;
}

const ROWS = ["A", "B", "C", "D", "E", "F"];
const SEATS_PER_ROW = 8;
const PRICE_STANDARD = 95000;
const PRICE_VIP = 120000;

export default function SeatBooking({
  movie,
  cinema,
  onBack,
}: SeatBookingProps) {
  const [selectedSeats, setSelectedSeats] = useState<string[]>([]);
  
  // State lưu danh sách giờ lấy từ API
  const [showtimes, setShowtimes] = useState<string[]>([]); 
  const [selectedTime, setSelectedTime] = useState<string>(""); 
  
  const [isProcessing, setIsProcessing] = useState(false);
  const [isLoadingTime, setIsLoadingTime] = useState(true); // Biến để hiện loading khi đang tải giờ

  // --- GỌI API LẤY GIỜ CHIẾU TỪ BACKEND ---
  useEffect(() => {
    const fetchShowtimes = async () => {
      setIsLoadingTime(true);
      try {
        // Gọi API Laravel mà bạn vừa tạo
        // encodeURIComponent(cinema) để xử lý tên rạp có dấu/khoảng trắng
        const res = await fetch(`http://127.0.0.1:8000/api/showtimes?movie_id=${movie.id}&cinema=${encodeURIComponent(cinema)}`);
        
        if (!res.ok) throw new Error("Lỗi kết nối");
        
        const data = await res.json();
        
        if (Array.isArray(data)) {
          setShowtimes(data);
          // Mặc định chọn giờ đầu tiên nếu có
          if (data.length > 0) {
            setSelectedTime(data[0]);
          }
        }
      } catch (error) {
        console.error("Lỗi lấy suất chiếu:", error);
        setShowtimes([]); // Nếu lỗi thì reset về rỗng
      } finally {
        setIsLoadingTime(false);
      }
    };

    if (movie.id && cinema) {
      fetchShowtimes();
    }
  }, [movie.id, cinema]);

  const totalPrice = selectedSeats.reduce((total, seat) => {
    const row = seat.charAt(0);
    return total + (["E", "F"].includes(row) ? PRICE_VIP : PRICE_STANDARD);
  }, 0);

  const toggleSeat = (seatId: string) => {
    if (selectedSeats.includes(seatId)) {
      setSelectedSeats(selectedSeats.filter((id) => id !== seatId));
    } else {
      setSelectedSeats([...selectedSeats, seatId]);
    }
  };

  const handleVNPayPayment = async () => {
    if (selectedSeats.length === 0) return;
    setIsProcessing(true);
    try {
      const response = await fetch("http://127.0.0.1:8000/api/vnpay_payment", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          order_total: totalPrice,
          order_desc: `Vé ${movie.title} - ${selectedTime} - ${cinema}`,
          language: "vn",
          seats: selectedSeats,
          movie_id: movie.id,
          cinema: cinema,
          showtime: selectedTime,
        }),
      });
      const data = await response.json();
      if (data.code === "00" && data.data) {
        window.location.href = data.data;
      } else {
        alert("Lỗi tạo thanh toán: " + (data.message || "Không xác định"));
      }
    } catch (error) {
      console.error("Payment Error:", error);
      alert("Không thể kết nối đến server thanh toán.");
    } finally {
      setIsProcessing(false);
    }
  };

  return (
    <div className="flex flex-col h-full w-full bg-background text-foreground overflow-hidden">
      {/* HEADER */}
      <div className="flex items-center p-4 border-b border-border bg-background shrink-0 z-10 shadow-sm gap-3">
        <button
          onClick={onBack}
          className="p-2 hover:bg-muted rounded-full transition"
        >
          <ChevronLeft size={24} />
        </button>
        <div>
          <h3 className="font-bold text-lg line-clamp-1">{movie.title}</h3>
          <p className="text-xs text-muted-foreground flex items-center gap-1">
            {cinema} • <Clock size={10} /> {selectedTime || "..."} •{" "}
            {selectedSeats.length > 0
              ? `${selectedSeats.length} ghế`
              : "Chưa chọn ghế"}
          </p>
        </div>
      </div>

      {/* BODY */}
      <div className="flex-1 min-h-0 overflow-y-auto p-4 md:p-8 bg-accent/5 scroll-smooth">
        {/* --- PHẦN CHỌN GIỜ CHIẾU --- */}
        <div className="mb-8">
          <p className="text-sm font-semibold mb-3 text-center text-muted-foreground">
            Chọn suất chiếu
          </p>
          
          {isLoadingTime ? (
             <div className="flex justify-center py-4"><Loader2 className="animate-spin text-primary"/></div>
          ) : showtimes.length === 0 ? (
             <div className="text-center text-red-500 text-sm py-2 bg-red-50 rounded-lg border border-red-100 mx-4">
                Hiện tại rạp này chưa có suất chiếu nào.
             </div>
          ) : (
             <div className="flex gap-3 overflow-x-auto pb-2 px-4 no-scrollbar justify-start md:justify-center snap-x">
                {showtimes.map((time) => (
                  <button
                    key={time}
                    onClick={() => setSelectedTime(time)}
                    className={`
                      px-4 py-2 rounded-xl text-sm font-bold border whitespace-nowrap snap-center transition-all
                      ${
                        selectedTime === time
                          ? "bg-primary text-primary-foreground border-primary shadow-md scale-105"
                          : "bg-background border-border hover:border-primary/50 text-muted-foreground"
                      }
                    `}
                  >
                    {time}
                  </button>
                ))}
             </div>
          )}
        </div>

        {/* Màn hình chiếu (Giữ nguyên) */}
        <div className="mb-10 text-center relative">
          <div className="h-1.5 w-2/3 bg-primary mx-auto rounded-full shadow-[0_5px_20px_rgba(var(--primary),0.5)] mb-3"></div>
          <p className="text-[10px] text-muted-foreground uppercase tracking-[0.2em]">
            Màn hình chiếu
          </p>
          <div className="absolute top-2 left-1/2 -translate-x-1/2 w-1/2 h-16 bg-gradient-to-b from-primary/10 to-transparent -z-10 blur-xl"></div>
        </div>

        {/* Lưới ghế (Giữ nguyên) */}
        <div className="flex justify-center mb-8 overflow-x-auto py-4 px-2">
          <div className="grid gap-2 md:gap-3">
            {ROWS.map((row) => (
              <div key={row} className="flex gap-2 md:gap-3 justify-center">
                {Array.from({ length: SEATS_PER_ROW }).map((_, i) => {
                  const seatNum = i + 1;
                  const seatId = `${row}${seatNum}`;
                  const isVip = ["E", "F"].includes(row);
                  const isSelected = selectedSeats.includes(seatId);

                  return (
                    <button
                      key={seatId}
                      onClick={() => toggleSeat(seatId)}
                      disabled={isProcessing}
                      className={`
                        w-9 h-9 md:w-11 md:h-11 rounded-t-xl text-[10px] md:text-xs font-bold transition-all duration-200 border relative group
                        ${
                          isSelected
                            ? "bg-primary text-primary-foreground border-primary shadow-lg transform -translate-y-1"
                            : isVip
                            ? "bg-purple-500/10 border-purple-500/50 text-purple-600 hover:bg-purple-500/20"
                            : "bg-background border-border text-muted-foreground hover:border-primary/50 hover:text-primary"
                        }
                      `}
                    >
                      {seatId}
                    </button>
                  );
                })}
              </div>
            ))}
          </div>
        </div>

        {/* Chú thích ghế (Giữ nguyên) */}
        <div className="flex justify-center flex-wrap gap-4 text-xs text-muted-foreground pb-4">
           <div className="flex items-center gap-2"><div className="w-4 h-4 rounded border border-border bg-background"></div>Thường</div>
           <div className="flex items-center gap-2"><div className="w-4 h-4 rounded border border-purple-500/50 bg-purple-500/10"></div>VIP</div>
           <div className="flex items-center gap-2"><div className="w-4 h-4 rounded bg-primary"></div>Đang chọn</div>
        </div>
      </div>

      {/* FOOTER */}
      <div className="p-4 border-t border-border bg-background shadow-[0_-5px_20px_rgba(0,0,0,0.1)] z-20 shrink-0">
        <div className="flex justify-between items-end mb-4 px-1">
          <div>
            <p className="text-sm text-muted-foreground mb-0.5">
              Tạm tính ({selectedSeats.length} vé)
            </p>
            <p className="text-2xl font-bold text-primary">
              {totalPrice.toLocaleString("vi-VN")} đ
            </p>
          </div>
          <div className="text-right max-w-[50%]">
            <p className="text-xs text-muted-foreground mb-1">
              Suất {selectedTime || "..."}
            </p>
            <p className="text-sm font-medium text-foreground truncate">
              {selectedSeats.length > 0 ? selectedSeats.join(", ") : "..."}
            </p>
          </div>
        </div>

        <button
          onClick={handleVNPayPayment}
          // 🔥 Disable nút thanh toán nếu chưa có giờ chiếu hoặc chưa chọn ghế
          disabled={selectedSeats.length === 0 || isProcessing || !selectedTime}
          className={`
            w-full py-3.5 rounded-xl font-bold text-base md:text-lg flex items-center justify-center gap-2 shadow-lg transition-all
            ${
              selectedSeats.length === 0 || !selectedTime
                ? "bg-muted text-muted-foreground cursor-not-allowed"
                : "bg-[#005BAA] hover:bg-[#004d90] text-white hover:shadow-blue-900/20"
            }
          `}
        >
          {isProcessing ? (
            <>
              <Loader2 className="animate-spin" /> Đang xử lý...
            </>
          ) : (
            <>
              <CreditCard size={22} />
              THANH TOÁN VNPAY
            </>
          )}
        </button>
      </div>
    </div>
  );
}