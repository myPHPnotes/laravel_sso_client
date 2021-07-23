<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get("/sso/login", function(Request $request) {
    $request->session()->put("state", $state =  Str::random(40));
    $query = http_build_query([
        "client_id" => "92e180b3-a8e1-415c-9a2b-c8a4af6be76f",
        "redirect_uri" => "http://127.0.0.1:8080/callback",
        "response_type" => "code",
        "scope" => "view-user ",
        "state" => $state
    ]);
    return redirect("http://127.0.0.1:8000/oauth/authorize?" . $query);
})->name("sso.login");


Route::get("/callback", function (Request $request) {
    $state = $request->session()->pull("state");

    throw_unless(strlen($state) > 0 && $state == $request->state, InvalidArgumentException::class);

    $response = Http::asForm()->post(
        "http://127.0.0.1:8000/oauth/token",
        [
        "grant_type" => "authorization_code",
        "client_id" => "92e180b3-a8e1-415c-9a2b-c8a4af6be76f",
        "client_secret" => "fY4TVEj1BmFfrq2w5cKzaGRcJkhcW4bSqiabz5ci",
        "redirect_uri" => "http://127.0.0.1:8080/callback",
        "code" => $request->code
    ]);
    $request->session()->put($response->json());
    return redirect("/authuser");
});
Route::get("/authuser", function(Request $request) {
    $access_token = $request->session()->get("access_token");
    $response = Http::withHeaders([
        "Accept" => "application/json",
        "Authorization" => "Bearer " . $access_token
    ])->get("http://127.0.0.1:8000/api/user");
    $userObject = $response->json();
    try {
        $email = $userObject['email'];
    } catch (\Throwable $th) {
        return redirect(route("login"))->withError("Failed to get user information!");
    }
    $user = User::where("email", $email)->first();
    if (!$user) {
        $user = new User;
        $user->name = $userObject['name'];
        $user->email = $userObject['email'];
        $user->email_verified_at = $userObject['email_verified_at'];
        $user->save();
    }
    Auth::login($user);
    return redirect(route("home"));
});
Auth::routes(['register' => false,  'reset' => false]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
