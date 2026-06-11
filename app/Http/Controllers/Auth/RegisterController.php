<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        $branches    = Branch::where('is_active', true)->orderBy('sort_order')->get();
        $departments = Department::where('is_active', true)->orderBy('branch_id')->orderBy('sort_order')->get();
        $roles       = collect(User::ROLES)->except(['ceo', 'commercial_director']);
        return view('auth.register', compact('branches', 'departments', 'roles'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users',
            'password'      => 'required|min:8|confirmed',
            'role'          => 'required|in:' . implode(',', array_keys(User::ROLES)),
            'branch_id'     => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'role'          => $request->role,
            'branch_id'     => $request->branch_id,
            'department_id' => $request->department_id,
            'is_active'     => false,
        ]);

        return redirect()->route('login')->with('success', 'Регистрация успешна. Ожидайте активации вашей учётной записи администратором.');
    }
}
