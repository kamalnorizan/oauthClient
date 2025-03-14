<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class OAuthController extends Controller
{
    public function login(Request $request) {
        if(Auth::check()){
            return redirect('/home');
        }

        $request->session()->put('oauth_state', $state = Str::random(40));

        $query = http_build_query([
            'client_id' => env('OAUTH_CLIENT_ID'),
            'redirect_uri' => env('OAUTH_REDIRECT_URI'),
            'response_type' => 'code',
            'state' => $state,
        ]);

        return redirect(env('OAUTH_SERVER_URL') . '/oauth/authorize?' . $query);
    }

    public function callback(Request $request) {
        if($request->error){
            return redirect('/');
        }

        $state = $request->session()->pull('oauth_state');

        throw_unless(
            strlen($state) > 0 && $state === $request->state,
            InvalidArgumentException::class
        );

        $response = Http::asForm()->post(env('OAUTH_SERVER_URL') . '/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => env('OAUTH_CLIENT_ID'),
            'client_secret' => env('OAUTH_CLIENT_SECRET'),
            'redirect_uri' => env('OAUTH_REDIRECT_URI'),
            'code' => $request->code,
        ]);
        $res = $response->json();
        $request->session()->put('oauth_token', $token = $response->json());
        $oauthUser = Http::withToken($res['access_token'])->get(env('OAUTH_SERVER_URL') . '/api/user')->json();

        $user = User::where('identity', $oauthUser['identity'])->first();
        if($user){
            Auth::login($user);
        }else{
            abort(401, 'Unauthorized: No access for this application');
        }

        return redirect('/home');
    }

    public function logout(Request $request) {

        $token = $request->session()->get('oauth_token.access_token');
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->post(env('OAUTH_SERVER_URL') . '/api/logout');

        $request->session()->forget('oauth_token');
        Auth::logout();
        return redirect('/');
    }

    public function ssologin(Request $request) {
        $token = $request->get('token');

        $oauthUser = Http::withToken($token)->get(env('OAUTH_SERVER_URL') . '/api/user')->json();

        $user = User::where('identity', $oauthUser['identity'])->first();
        if($user){
            $request->session()->put('oauth_token.access_token', $token);
            Auth::login($user);
        }else{
            abort(401, 'Unauthorized: No access for this application');
        }

        return redirect('/home');
    }
}
