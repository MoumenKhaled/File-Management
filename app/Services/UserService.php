<?php
// app/Services/UserService.php
namespace App\Services;
use App\Models\User;
use App\Models\ForgetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Mail\RegisterUserMail;
use App\Mail\ForgottenPassword;
use Illuminate\Support\Facades\Mail;

class UserService
{
    public function registerUser($userData)
{
        $email=User::where('email', $userData['email'])->first();
        $username=User::where('user_name', $userData['user_name'])->first();
        if ($email){
          return ['message'=>'This email has already been taken'];
        }
        else if ($username){
            return ['message'=>'This username has already been taken'];
        }
        else {
        // Create a new user in the database
        $user = new User();
        $user->user_name = $userData['user_name'];
        $user->email =  $userData['email'];
        $user->password = Hash::make($userData['password']);
        $user->verification_code = rand(100000, 999999);
        $user->save();

        // Send verification email
        Mail::to($user->email)->send(new RegisterUserMail($user,$user->verification_code));

        $token = $user->createToken('apiToken')->plainTextToken;
        $user=[
            'user' => $user,
            'token' => $token
        ];
        }
        return $user ;
}
    public function activate_email_service($userData)
{
        $user = User::where([
         ['verification_code', $userData['code']],
         ['id',auth()->id()]
        ])->first();
        if ($user) {
            $user->email_verified_at = now();
            $user->save();
            return ['message'=>'The account has been activated successfully'];
        } else {
            return ['message'=> 'Error in the entered code, please try again'];
        }
}
    public function loginUser($userData)
{
        $user = User::where('email', $userData['email'])->first();
        if (!$user) return ['message'=>"This account doesn't exist"];
        else if ($user){
            if(Hash::check($userData['password'], $user->password)) {
            $token = $user->createToken('apiToken')->plainTextToken;
            $user = [
                'user' => $user,
                'token' => $token
             ];
             return $user;
        }
        else return ['message'=>"The password is incorrect"];
    }
}
    public function logoutUser($userData)
{
        $userData->delete();
        return response()->json(['message' => 'Successfully logged out']);
}
    public function forgotPasswordservice($userData)
{
        $user=User::where('email',$userData['email'])->first();
        if($user)
        {
            $Password=ForgetPassword::updateOrCreate(
                ['email'=>$userData['email']],
                    [
                        'email'=>$userData['email'],
                        'token'=>rand(100000, 999999),
                    ]
                    );
          Mail::to($user->email)->send(new ForgottenPassword($Password));
          return ['message'=> 'confirmation code has been sent successfully'];
        }
        else
        {
            return ['message'=> 'This email does not exist'];
        }
}
    public function forgot_Password_check_code_service($userData)
{
        $code=$userData['code'];
        $checkReset=ForgetPassword::where([
             'token'=>$code,
         ])->first();

         if(!$checkReset)
         {
            $response['message']= 'Error in the entered code, please try again';
         }
         else $response['message']= 'The code is correct';
         return $response;
}
    public function update_password_service($data)
{
       $code=$data['code'];
        $checkReset=ForgetPassword::where([
            'token'=>$data['code'],
        ])->first();
        if ($checkReset){
        $user=user::where('email',$checkReset->email)->first();
        if(!$user)
        {
            return ['message'=> 'user not found'];
        }
        else {
        $user->password=bcrypt($data['password']);
        $user->save();
        $checkReset->delete();
        return ['message'=>'Reset Password Successfully!'];
        }
        }
        return ['message'=>'This email does not exist'];
}
}
