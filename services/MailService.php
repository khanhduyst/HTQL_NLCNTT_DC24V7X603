<?php

require_once dirname(__DIR__) . '/libs/PHPMailer/Exception.php';
require_once dirname(__DIR__) . '/libs/PHPMailer/PHPMailer.php';
require_once dirname(__DIR__) . '/libs/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{

    private $host = 'mail.tendomaincuaem.com';
    private $port = 465;
    private $username = 'system@tendomaincuaem.com';
    private $password = 'MatKhauEmailHostingCuaEm';
    private $from_name = 'HỆ THỐNG ERP WAREHOUSE';

    public function sendWelcomeEmail($userEmail, $fullName, $username, $plainPassword)
    {
        $mail = new PHPMailer(true);

        try {

            $mail->isSMTP();
            $mail->Host       = $this->host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->username;
            $mail->Password   = $this->password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = $this->port;
            $mail->CharSet    = 'UTF-8';


            $mail->setFrom($this->username, $this->from_name);
            $mail->addAddress($userEmail, $fullName);


            $mail->isHTML(true);
            $mail->Subject = '🎉 Chào mừng nhân sự mới - Tài khoản truy cập hệ thống';

            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e5e7eb; rounded-direction: 8px;'>
                    <div style='background-color: #4361ee; padding: 15px; text-align: center; border-radius: 6px 6px 0 0;'>
                        <h2 style='color: white; margin: 0;'>Chào mừng thành viên mới!</h2>
                    </div>
                    <div style='padding: 20px; color: #374151; line-height: 1.6;'>
                        <p>Xin chào <b>$fullName</b>,</p>
                        <p>Tài khoản vận hành hệ thống Quản lý kho hàng của bạn đã được bộ phận Quản trị viên khởi tạo thành công trên hệ thống.</p>
                        
                        <div style='background-color: #f3f4f6; padding: 15px; border-radius: 6px; margin: 20px 0;'>
                            <h4 style='margin-top: 0; color: #1f2937;'>Thông tin đăng nhập của bạn:</h4>
                            <table style='width: 100%; border-collapse: collapse;'>
                                <tr>
                                    <td style='width: 40%; padding: 5px 0;'><b>Tên tài khoản:</b></td>
                                    <td style='color: #d63384; font-weight: bold;'>$username</td>
                                </tr>
                                <tr>
                                    <td style='padding: 5px 0;'><b>Mật khẩu khởi tạo:</b></td>
                                    <td style='color: #10b981; font-weight: bold;'>$plainPassword</td>
                                </tr>
                            </table>
                        </div>
                        
                        <p style='color: #ef4444; font-size: 13px;'>* Lưu ý: Để bảo mật thông tin dữ liệu kho, vui lòng đăng nhập vào hệ thống và thực hiện thay đổi mật khẩu ngay trong lần đầu tiên truy cập.</p>
                        <hr style='border: 0; border-top: 1px solid #e5e7eb; margin: 20px 0;'>
                        <p style='font-size: 12px; color: #6b7280; text-align: center;'>Đây là email tự động từ hệ thống ERP, vui lòng không phản hồi lại thư này.</p>
                    </div>
                </div>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {

            error_log("Lỗi gửi mail: " . $mail->ErrorInfo);
            return false;
        }
    }
}
