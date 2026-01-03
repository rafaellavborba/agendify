<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;


class RegisteredUserController extends Controller
{
    public function store(Request $request)
    {
       $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:6'],
            'company_name' => ['required', 'string', 'max:255'],
        ]);

        return DB::transaction(function () use ($validated) {

            $tenant = Tenant::create([
                'name' => $validated['company_name'],
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'tenant_id' => $tenant->id,
            ]);

            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'tenant' => $tenant,
                'user' => $user,
                'token' => $token,
            ], 201);
        });
    }
}
