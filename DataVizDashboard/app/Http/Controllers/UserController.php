<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function register(Request $req)
    {
        $req->validate([
            'name' => 'required|string|regex:/^[a-zA-Z\s]+$/',
            'email' => 'required|string|email|max:30|unique:users',
            'password' => 'required|string|min:8',
        ]);

        
        $user = User::create([
            'name' => $req->name,
            'email' => $req->email,
            'password' => Hash::make($req->password),
        ]);

        return view('signin', ['signin']);
    }
    public function login(Request $req)
    {
    $req->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $req->email)->first();

    if ($user && Hash::check($req->password, $user->password)) {
        $req->session()->put('user', $user->name);
        $req->session()->put('user_id', $user->id); 
        return redirect('dashboard');
    } else {
        return redirect()->back()->withInput($req->only('email'))->with('error', 'Invalid email or password');
    }
    }   
}
