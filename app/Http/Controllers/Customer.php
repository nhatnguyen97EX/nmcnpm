<?php

namespace App\Http\Controllers;

use App\Banner_model;
use App\Mail\SendTokenMail;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Validator;
use App\Users;
use Mail;
use App\Mail\SendMail;
use Redirect;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\CheckValidate;
use App\User;
use App\PasswordReset;

class Customer extends Controller
{
    //
    protected $users, $banner_model, $reset_Pass;

    public function __construct()
    {
        $this->users = new Users();
        $this->banner_model = new Banner_model();
        $this->reset_Pass = new PasswordReset();
    }

    public function index(Request $request)
    {
        $banner = $this->banner_model->getInfo();
        return view('customer.home', ['banner' => $banner]);
    }

    public function login(Request $request)
    {
        //Lấy ra thông tin trong form đăng nhập trừ _token (username,password)
        $data = $request->except('_token');
        // Sử dụng thư viện Auth của laravel để kiểm tra username password trong database
        if (Auth::attempt($data)) {  //Trả về true hoặc false
            $data = Auth::user();//Nếu true lấy ra thông tin user
            if ($data->active == 1) {//Kiểm tra user có active nếu active = 1 redirec về trang chủ
//                session flash duy nhta trong 1 route, load lai trang thi mat
                $request->session()->flash('login', 'Đăng nhập thành công');
                return redirect('/');
            } else {
                //User chưa active trả về lỗi
                Auth::logout();
                $request->session()->flash('fail', 'Đăng nhập thất bại');
                return redirect('/');
            }
        } else {
            Auth::logout();
            $request->session()->flash('fail', 'Đăng nhập thất bại');
            return redirect('/');
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }
//Đăng ký---------------------------------------------------------------
    public function signup(Request $res)
    {
        //5. Kiểm tra tính hợp lệ của thông tin
        $validator = Validator::make($res->all(), [
            'last_name' => 'required',
            'first_name' => 'required',
        	'email' => 'unique:users|required|email',
        	'password' => 'required|min:8',
        	'confirm_password' => 'required|same:password',
        ],
            [
                'unique' => ':attribute đã tồn tại',
            	'email' => ':attribute không đúng định dạng',
                'required' => ':attribute không được bỏ trống',
            	'min' => ':attribute phải ít nhất 8 kí tự',
           		'same' => ':attribute phải trùng khớp với password',
            	
            ]);
        //Thông tin không hợp lệ, hiển thị thông báo lỗi
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }
        //6. Lưu thông tin tạm thời với trạng thái active = 0 và quyền truy cập mặc định là user
        $this->users->name = $res->input('last_name') . ' ' . $res->input('first_name');
        $this->users->email = $res->input('email');
        $this->users->password = bcrypt($res->input('password'));
        $this->users->active = '0';
        $this->users->role = 'user';
        //7.1 Tạo nội dung message xác nhận kích hoạt tài khoản với đường dẫn tới funcition update
        $message = array(
            'name' => $res->input('last_name') . ' ' . $res->input('first_name'),
            'link' => $res->root() . '/customer/update/' . $res->input('email'),
            'email' => $res->input('email'),
        );

        if ($this->users->save()) {
            //7.2 Gửi mail xác nhận kích hoạt và thông báo đăng ký thành công
            Mail::to($res->input('email'))->send(new SendMail('Xác nhận thông tin địa chỉ email tại Gemingear.vn', $message));
            return response()->json(['success' => 'Đăng ký thành công vui lòng kiểm tra email của bạn <a href="https://mail.google.com/mail/u/0/#inbox">Tại đây</a>']);
        } else {
            return response()->json(['success' => 'Đăng ký thất bại! Xin kiểm tra lại']);
        }
    }


//
    public function sendMailForgotPass(Request $request)
    {
        $user = User::where('email', $request->email)->first();
//Tạo một email cho quên mật khẩu với nọi dung mail và token
        $passwordReset = PasswordReset::updateOrCreate([
            'email' => $user->email,
        ], [
            'token' => Str::random(60),
        ]);
//        Tạo nội dung email thông qua mảng
        $message = array(
            'token' => $passwordReset->token,
            'email' => $request->input('email'),
        );
//        Nếu gửi thành công
        if ($passwordReset) {
//            $user->notify(new Customer($passwordReset->token));
            Mail::to($request->input('email'))->send(new SendTokenMail('Xác nhận thông tin địa chỉ email tại Gemingear.vn', $message));
            return response()->json(['success' => 'Đăng ký thành công vui lòng kiểm tra email của bạn']);

        }
//        Nếu thất bại sẽ xuất hiện thông báo dưới
        return response()->json([
            'message' => 'We have e-mailed your password reset link!'
        ]);

    }

    public function reset(Request $request)
    {
        //Lấy token dưới database
        $token = $request->token;
        // Kiểm tra có token không
        $passwordReset = PasswordReset::where('token', $token)->first();
        if($passwordReset==null){
            $request->session()->flash('wrong', 'Vui lòng kiểm tra lại thông tin');
            return redirect('/');
        }
//      Thời gian token tồn tại
        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();

            return response()->json([
                'message' => 'This password reset token is invalid.',
            ], 422);
        }
//        Kiếm user có email đó
        $user = User::where('email', $passwordReset->email)->firstOrFail();
        $newPassword = Hash::make($request->password);
        $updatePasswordUser = $user->update(['password' => $newPassword]);
//        Xoá token cho passwordReset
        $passwordReset->delete();
        $request->session()->flash('forgotsuccess', 'Thay đổi mật khẩu thành công');
        return redirect('/');
    }
    public function update($email)
    {
        $where = array('email' => $email);
        //9. Cập nhật trạng thái kích hoạt cho tài khoản và chuyển đến trang chủ
        if ($this->users->updateInfo($where, array('active' => 1))) {
            return redirect::to('http://127.0.0.1:8000/');
        } else {
            return redirect()->back();
        }
    }
}
