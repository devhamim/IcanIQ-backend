<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\ClassModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;

class AdminController extends Controller
{

    function class_edit($id)
    {
        $class = ClassModel::find($id);
        return $class;
    }
    function admin_register()
    {
        $admin = Admin::count();
        if ($admin != 0) {
            return redirect()->route('admin.login');
        } else {
            return view('backend.layouts.admin_register');
        }
    }
    function admin_login(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('dashboard');
        }
        $ip = $request->ip();
        $admin = Admin::count();
        return view('backend.layouts.admin_login', [
            'admin' => $admin,
            'ip' => $ip,
        ]);
    }
    function admin_store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email',
            'number' => 'required|min:11|numeric',
            'profile' => 'required',
            'password' => 'required|min:8',
        ]);
        $image = $request->profile;
        $image_name = $request->name . rand(1000, 10) . '.' . $image->extension();
        Image::make($image)->save(base_path('public/files/profile/' . $image_name));
        Admin::insert([
            'name' => $request->name,
            'profile' => $image_name,
            'email' => $request->email,
            'number' => $request->number,
            'status' => $request->status,
            'password' => bcrypt($request->password),
            'created_at' => Carbon::now(),
        ]);
        $credentials =  $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials)) {
            return redirect()
                ->route('dashboard')
                ->with('Welcome! Your account has been successfully created!');
        }
    }

    function login_admin(Request $request)
    {
        $request->validate([
            'email' => 'required|max:255',
            'password' => 'required|min:8',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials)) {
            return redirect()->route('dashboard');
        } else {
            return back()->with('err', 'Credentials not matching');
        }
    }

    function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }
    function dashboard()
    {
        return view('backend.home.dashboard');
    }
}
