<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;

    /**
     * Create a new message instance.
     */
    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    /**
     * Tiêu đề Email
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔑 Mã xác thực OTP - SOLID TECH',
        );
    }

    /**
     * Nội dung Email (Sử dụng Markdown cho đẹp)
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.otp', // Chúng ta sẽ tạo file giao diện ở bước 3
            with: [
                'otp' => $this->otp,
            ],
        );
    }
}