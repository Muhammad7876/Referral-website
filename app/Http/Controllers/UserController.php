<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\str;
use App\Models\User;
use App\Models\Network;

use Mail;
use Illuminate\Support\Facades\URL;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class UserController extends Controller
{
    public function login(){
        return view('register');
    }
    //
    public function loadRegister(){
        return view('register');
    }

    public function registered(Request $request){
        $request->validate([
            'name' => 'required|string|min:2',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|min:6|confirmed'
        ]);

        $referralCode = Str::random(10);
        $token = Str::random(50);
        if(isset($request->referral_code)){
           $userData = User::where('referral_code',$request->referral_code)->get();
           if(count($userData) > 0){
           $user_id = User::insertGetId([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'referral_code' => $referralCode,
                'remember_token' => $token,
            ]);
            Network::insert([
                'referral_code' => $request->referral_code,
                'user_id' => $user_id,
                'parent_user_id' => $userData[0]['id'],
            ]);
           }
           else{
            return back()->with('error','Please enter Valid Referral Code!');
           }
        }else{
            User::insert([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'referral_code' => $referralCode,
                'remember_token' => $token,
            ]);
        }

        $domain = URL::to('/');
        $url = $domain.'/referral-register?ref='.$referralCode;
        $data['url'] = $url;
        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data['password'] = $request->password;
        $data['title'] = 'Registered';

        Mail::send('emails.registerMail',['data' => $data], function($message) use ($data){
            $message->to($data['email'])->subject($data['title']);
        });

        // verification mail send
        $url = $domain.'/email-verification/'.$token;
        
        $data['url'] = $url;
        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data['title'] = 'Referral Verification Email';

        Mail::send('emails.verifyMail',['data' => $data],function($message) use ($data){
            $message->to($data['email'])->subject($data['title']);  
        });
        return back()->with('success',"Your Registeration has been Successfull & Please verify your mail" );
    }

    public function loadReferralRegister(Request $request){
        if(isset($request->ref)){
            $referral = $request->ref;
            $userData = User::where('referral_code',$referral)->get();

            if(count($userData) > 0){
                return view('referralRegister',compact('referral'));
            }else{
                return view('404');
            }
        }else{
            return redirect('/');
        }
    }

    public function emailVerification($token){

        $userData = User::where('remember_token',$token)->get();

        if(count($userData) > 0){
            if($userData[0]['is_verified'] == 1){
                return view('verified',['message' => 'Your email is already verified']);
            }

            User::where('id',$userData[0]['id'])->update([
                'is_verified' => 1,
                'email_verified_at' => date('Y-m-d H:i:s'),
            ]);
            return view('verified',['message' => 'Your'.$userData[0]['email'].'mail verified Successful']);
        }else{
            return view('verified',['message','404 Page not found!']);
        }
    }

    public function loadLogin(){
        return view('login');
    }

    public function userLogin(Request $request){
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required',
        ]);

        $userData = User::where('email',$request->email)->first();
        if(!empty($userData)){
            if($userData->is_verified == 0){
                return back()->with('error','Please verify your mail');
            }
        }

        $userCredential = $request->only('email','password');
        if(Auth::attempt($userCredential)){
            return redirect('/dashboard');
        }else{
            return back()->with('error','Username & Password is incorrect!');
        }
    }

    public function loadDashboard(){
        
        $networkCount = Network::where('parent_user_id',Auth::user()->id)->orWhere('user_id',Auth::user()->id)->count();
        $networkData = Network::with('user')->where('parent_user_id',Auth::user()->id)->get();
        
        $shareComponent = \Share::page(
            URL::to('/').'/referral-register?ref='.Auth::user()->referral_code,
            'Share and Earn Points by Referral Link',
        )
        ->facebook()
        ->twitter()
        ->linkedin()
        ->telegram()
        ->whatsapp()
        ->reddit();

        return view('dashboard',compact(['networkCount','networkData','shareComponent']));
    }
    
    public function logout(Request $request){
        
        $request->session()->flush();
        Auth::logout();
        return redirect('/');
    }

    public function referralTrack(){
        $dateLabels = [];
        $dateData = [];

        for($i = 30; $i >= 0; $i--){
            $dateLabels[] = Carbon::now()->subDays($i)->format('d-m-Y');
            
            $dateData[] = Network::whereDate('created_at',Carbon::now()->subDays($i)->format('Y-m-d'))
            ->where('parent_user_id',Auth::user()->id)->count();
        
        }

        $dateLabels = json_encode($dateLabels);
        $dateData = json_encode($dateData);


        return view('referralTrack',compact(['dateLabels','dateData']));
    }

    public function deleteAccount(Request $request){
        try{
            User::where('id',Auth::user()->id)->delete();
            $request->session()->flush();
            Auth::logout();
            return response()->json(['success' => true]);
            
        }catch (\Exception $e){
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }
}









