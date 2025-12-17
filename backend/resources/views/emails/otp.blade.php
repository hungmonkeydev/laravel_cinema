<x-mail::message>
# Xin chào! 👋

Bạn đang thực hiện đăng ký/đăng nhập tại **SOLID TECH**.

Đây là mã xác thực (OTP) của bạn. Mã này sẽ hết hạn sau **10 phút**.

<x-mail::panel>
<div style="font-size: 32px; font-weight: bold; text-align: center; letter-spacing: 5px; color: #2d3748;">
    {{ $otp }}
</div>
</x-mail::panel>

<x-mail::button :url="config('app.url')">
Truy cập Website
</x-mail::button>

**Lưu ý bảo mật:**
* Tuyệt đối không chia sẻ mã này cho bất kỳ ai.
* Nếu bạn không yêu cầu mã này, vui lòng bỏ qua email.

Trân trọng,<br>
**Đội ngũ SOLID TECH** 🏃‍♂️👟
</x-mail::message>