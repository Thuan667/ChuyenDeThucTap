<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use App\Mail\ResetPasswordMail; // Tạo mail để gửi mã OTP
use Illuminate\Support\Str; // Để tạo mã OTP
use Illuminate\Support\Facades\Mail; // Thêm dòng này để sử dụng Mail
// Import các thư viện cần thiết
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class PasswordResetController extends Controller
{
    // Phương thức xử lý yêu cầu đặt lại mật khẩu
    public function reset(Request $request)
    {
        // Xác thực và xử lý yêu cầu từ người dùng
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        // Cập nhật mật khẩu cho người dùng
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại.'], 404);
        }

        // Cập nhật mật khẩu
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Mật khẩu đã được cập nhật.'], 200);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $passwordReset = DB::table('password_resets')
            ->where('token', $request->token)
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset) {
            return response()->json(['message' => 'Token không hợp lệ hoặc đã hết hạn.'], 400);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại.'], 404);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();


        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Mật khẩu đã được cập nhật thành công.'], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function showResetForm($token, Request $request)
    {
        // Kiểm tra xem token có tồn tại hay không
        if (empty($token)) {
            return response()->json(['message' => 'Token không hợp lệ.'], 400);
        }

        return view('auth.reset_password')->with([
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    //
    public function sendResetLink(Request $request)
{
    $request->validate(['email' => 'required|email']);
    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['message' => 'Email không tồn tại.'], 404);
    }

    // Tạo token và link reset password
    $token = Str::random(60);
    $link = URL::temporarySignedRoute('password.reset', now()->addMinutes(30), [
        'token' => $token,
        'email' => $user->email
    ]);

    // Lưu token vào cơ sở dữ liệu
    DB::table('password_resets')->insert([
        'email' => $user->email,
        'token' => $token,
        'user_id' => $user->id, // Thêm user_id vào đây
        'created_at' => now()
    ]);

    // Gửi email với đường link
    Mail::to($user->email)->send(new ResetPasswordMail($link, $token));

    return response()->json(['message' => 'Đường link đặt lại mật khẩu đã được gửi đến email của bạn.'], 200);
}


    // Phương thức xác thực mã OTP và cập nhật mật khẩu
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        // Kiểm tra người dùng dựa trên email
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại.'], Response::HTTP_NOT_FOUND);
        }

        // Cập nhật mật khẩu
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Mật khẩu đã được cập nhật.'], Response::HTTP_OK);
    }
}
