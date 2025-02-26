<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class ValidateOAuthToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Unauthorized: Token missing'], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->get(env('OAUTH_SERVER_URL') . '/api/user');

            if ($response->failed()) {
                return response()->json(['error' => 'Unauthorized: Invalid token'], Response::HTTP_UNAUTHORIZED);
            }

            $userData = $response->json();
            if (!isset($userData['id'])) {
                return response()->json(['error' => 'Unauthorized: Invalid identity'], Response::HTTP_UNAUTHORIZED);
            }

            $user = User::where('identity', $userData['identity'])->first();
            if($user){
                Auth::login($user);
            }else{
                return response()->json(['error' => 'Unauthorized: No access for this application'], Response::HTTP_UNAUTHORIZED);
            }
            return $next($request);
        } catch (\Exception $e) {
            dd($e);
            return response()->json(['error' => 'Unauthorized: Token verification failed'], Response::HTTP_UNAUTHORIZED);
        }
    }
}
