<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class OauthTokenCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->session()->get('oauth_token.access_token');


        if (!$token) {
            abort(401, 'Unauthorized: Invalid token');
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->get(env('OAUTH_SERVER_URL') . '/api/user');

            if ($response->failed()) {
                abort(401, 'Unauthorized: Invalid token');
            }

            $userData = $response->json();
            if (!isset($userData['id'])) {
                abort(401, 'Unauthorized: Invalid token');
            }

            $user = User::where('identity', $userData['identity'])->first();
            if($user){
                return $next($request);
            }else{
                return redirect('/logout');
            }

        } catch (\Exception $e) {
            return redirect('/logout');
        }
    }
}
