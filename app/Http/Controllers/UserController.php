<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * GET /api/users
     * Returns list of users with name, email, and phone.
     */
    public function index(): JsonResponse
    {
        $users = User::all()->map(function ($u) {
            return [
                'name' => $u->name,
                'email' => $u->email,
                'phone' => $u->phone ?? null,
            ];
        });

        return response()->json(['data' => $users]);
    }
}
