<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Khởi tạo lớp Mail nhận mã số OTP
     */
    public function __construct(public string $otp) {}

    /**
     * Định nghĩa tiêu đề Email (Hiển thị ở hộp thư đến của khách hàng)
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🌸 [Cyberbloom] - Mã OTP Kích Hoạt Tài Khoản Khách Hàng',
        );
    }

    /**
     * Thiết kế giao diện Email trực tiếp bằng HTML nghệ thuật
     */
    public function content(): Content
    {
        return new Content(
            htmlString: "
            <div style='background-color: #fcf6f8; padding: 40px 20px; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; min-height: 100%;'>
                <table align='center' border='0' cellpadding='0' cellspacing='0' width='100%' style='max-width: 560px; background-color: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 8px 24px rgba(219, 39, 119, 0.04); border: 1px solid #f5e3eb;'>
                    
                    <tr>
                        <td align='center' style='padding: 35px 40px 20px 40px;'>
                            <h1 style='margin: 0; font-size: 28px; font-weight: 800; color: #be185d; letter-spacing: 2px; text-transform: uppercase;'>
                                Cyberbloom
                            </h1>
                            <p style='margin: 5px 0 0 0; font-size: 12px; color: #9d174d; letter-spacing: 3px; text-transform: uppercase; font-weight: 500;'>
                                Fresh Floral Experience
                            </p>
                            <div style='height: 1px; width: 60px; background-color: #f472b6; margin: 20px auto 0 auto;'></div>
                        </td>
                    </tr>

                    <tr>
                        <td style='padding: 20px 45px 10px 45px;'>
                            <p style='margin: 0; font-size: 16px; line-height: 1.6; color: #374151; font-weight: 400;'>
                                Xin chào bạn,
                            </p>
                            <p style='margin: 12px 0 0 0; font-size: 15px; line-height: 1.6; color: #4b5563;'>
                                Cảm ơn bạn đã lựa chọn tham gia cùng không gian hoa tươi của <strong>Cyberbloom</strong>. Để hoàn tất quy trình xác thực và kích hoạt tài khoản thành viên, vui lòng sử dụng mã mã OTP bảo mật dưới đây:
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td align='center' style='padding: 25px 45px;'>
                            <table border='0' cellpadding='0' cellspacing='0' style='background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 100%); border-radius: 14px; border: 1px dashed #f472b6;'>
                                <tr>
                                    <td align='center' style='padding: 16px 45px; letter-spacing: 6px; font-size: 34px; font-weight: 800; color: #db2777; font-family: \"Courier New\", Courier, monospace;'>
                                        {$this->otp}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style='padding: 10px 45px 30px 45px;'>
                            <div style='background-color: #fff5f5; border-left: 3px solid #f43f5e; padding: 12px 18px; border-radius: 4px 8px 8px 4px;'>
                                <p style='margin: 0; font-size: 13px; line-height: 1.5; color: #e11d48; font-weight: 500;'>
                                    * Mã xác thực có hiệu lực trong vòng <strong>10 phút</strong>. Để đảm bảo an toàn tuyệt đối cho tài khoản, vui lòng không cung cấp chuỗi mã này cho bất kỳ ai.
                                </p>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td align='center' style='padding: 0 45px 35px 45px;'>
                            <p style='margin: 0; font-size: 13px; color: #9ca3af;'>
                                Chúc bạn luôn có những trải nghiệm ngập tràn sắc hoa!
                            </p>
                            <p style='margin: 4px 0 0 0; font-size: 14px; font-weight: 600; color: #be185d;'>
                                Đội ngũ Cyberbloom Team
                            </p>
                        </td>
                    </tr>
                </table>

                <table align='center' border='0' cellpadding='0' cellspacing='0' width='100%' style='max-width: 560px; margin-top: 20px;'>
                    <tr>
                        <td align='center' style='font-size: 11px; color: #9ca3af; line-height: 1.5;'>
                            Đây là email gửi tự động từ hệ thống thử nghiệm Backend dự án Cyberbloom.<br>
                            © 2026 Cyberbloom Store. All rights reserved.
                        </td>
                    </tr>
                </table>
            </div>
            "
        );
    }
}