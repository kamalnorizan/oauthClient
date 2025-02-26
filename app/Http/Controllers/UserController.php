<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class UserController extends Controller
{

    public function syncUser(Request $request)
    {

        $response = Http::get(env('OAUTH_SERVER_URL') . '/api/syncusers');

        $res = $response->json();
        foreach ($res['users'] as $user) {
            // dd($user);
            $user = User::firstOrCreate([
                'email' => $user['email']
            ], [
                'name' => $user['name'],
                'identity' => $user['identity'],
                'password' => bcrypt(Str::random(16))
            ]);
        }

        return response()->json([
            'message' => 'Users synced successfully'
        ]);

    }
}
