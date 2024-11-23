<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;

class AuthController extends Controller{

    public function welcome(){
        return Inertia::render('Welcome',[
            'canLogin' => Auth::check()
        ]);
    }
    
    public function login(){
        return Inertia::render('Auth/Login');
    }

    public function authenticate(Request $request){
        $credentials = $request->validate([
            'email'     => ['required', 'email'],
            'password'  => ['required'],
        ]);

        $remember_me = !is_null($request->remember) && $request->remember == 'on' ? true : false; 
        $user        = User::where('email', $request->email)->first();

        if ($user && $user->type != 'student' && Auth::attempt(['email' => $request->email, 'password' => $request->password], $remember_me)) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }
}
