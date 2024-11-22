<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $link;  // Khai báo biến link
    public $token; // Khai báo biến token

    public function __construct($link, $token)
    {
        $this->link = $link;  // Khởi tạo biến link
        $this->token = $token; // Khởi tạo biến token
    }

    public function build()
    {
        return $this->subject('Đặt lại mật khẩu')
                    ->view('emails.reset_password') // Đường dẫn tới view
                    ->with([
                        'link' => $this->link, // Truyền biến link vào view
                        'token' => $this->token // Truyền biến token vào view (nếu cần)
                    ]);
    }
}
