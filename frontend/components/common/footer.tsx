import { Facebook, Instagram, Twitter, Youtube } from "lucide-react";

export default function Footer() {
  return (
    <footer className="bg-card border-t border-border">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
          <div>
            <h3 className="font-bold text-lg text-foreground mb-4">
              Galaxy Cinema
            </h3>
            <p className="text-muted-foreground">
              Nền tảng bán vé xem phim trực tuyến hàng đầu Việt Nam.
            </p>
          </div>

          <div>
            <h4 className="font-bold text-foreground mb-4">Liên Kết</h4>
            <ul className="space-y-2">
              <li>
                <a
                  href="#"
                  className="text-muted-foreground hover:text-primary transition"
                >
                  Trang Chủ
                </a>
              </li>
              <li>
                <a
                  href="#"
                  className="text-muted-foreground hover:text-primary transition"
                >
                  Phim
                </a>
              </li>
              <li>
                <a
                  href="#"
                  className="text-muted-foreground hover:text-primary transition"
                >
                  Khuyến Mãi
                </a>
              </li>
            </ul>
          </div>

          <div>
            <h4 className="font-bold text-foreground mb-4">Hỗ Trợ</h4>
            <ul className="space-y-2">
              <li>
                <a
                  href="#"
                  className="text-muted-foreground hover:text-primary transition"
                >
                  Liên Hệ
                </a>
              </li>
              <li>
                <a
                  href="#"
                  className="text-muted-foreground hover:text-primary transition"
                >
                  FAQ
                </a>
              </li>
              <li>
                <a
                  href="#"
                  className="text-muted-foreground hover:text-primary transition"
                >
                  Chính Sách
                </a>
              </li>
            </ul>
          </div>

          <div>
            <h4 className="font-bold text-foreground mb-4">Theo Dõi</h4>
            <div className="flex gap-4">
              <a href="#" className="p-2 hover:bg-muted rounded-lg transition">
                <Facebook size={20} className="text-primary" />
              </a>
              <a href="#" className="p-2 hover:bg-muted rounded-lg transition">
                <Instagram size={20} className="text-primary" />
              </a>
              <a href="#" className="p-2 hover:bg-muted rounded-lg transition">
                <Twitter size={20} className="text-primary" />
              </a>
              <a href="#" className="p-2 hover:bg-muted rounded-lg transition">
                <Youtube size={20} className="text-primary" />
              </a>
            </div>
          </div>
        </div>

        <div className="border-t border-border pt-8 text-center text-muted-foreground">
          <p>&copy; 2025 Galaxy Cinema. Tất cả quyền được bảo lưu.</p>
        </div>
      </div>
    </footer>
  );
}
