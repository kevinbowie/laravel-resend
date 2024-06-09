<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::get('/redirect', function (Request $request) {
    $scope = $request->get('scope', '');
    $request->session()->put('state', $state = Str::random(40));

    $query = http_build_query([
        'client_id' => '9c3cf9a0-4025-49b2-b198-fc4c6bc517d7', // Replace with Client ID
        'redirect_uri' => 'http://localhost/auth/callback',
        'response_type' => 'code',
        'scope' => $scope,
        'state' => $state,
    ]);

    return redirect('http://127.0.0.1:81/oauth/authorize?' . $query);
});

Route::get('/auth/callback', function (Request $request) {
    // in local you will get error, so you need do this all in POSTMAN, don't forget to copy the code from redirect url query params
    $state = $request->session()->pull('state');
 
    throw_unless(
        strlen($state) > 0 && $state === $request->state,
        InvalidArgumentException::class,
        'Invalid state value.'
    );

    $response = Http::asForm()->post('http://localhost:81/oauth/token', [
        'grant_type' => 'authorization_code',
        'client_id' => '9c3cf9a0-4025-49b2-b198-fc4c6bc517d7', // Replace with Client ID
        'client_secret' => '8FJeMZ0BO3djXEy8me6sAmBeVuOLUS0OUxS2PDEW', // Replace with client secret
        'redirect_uri' => 'http://localhost/auth/callback',
        'code' => $request->code,
    ]);

    session()->put('token', $response->json());
    return redirect('/todos');
});

Route::get('/todos', function() {
    $response = Http::withToken(session()->get('token.access_token'))
        ->acceptJson()
        ->get('http://127.0.0.1:81/api/todos');

    return $response;
});