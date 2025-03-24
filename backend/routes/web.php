<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

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
    return ['Laravel' => app()->version()];
});

Route::get('token', function () {
    return view('create-token');
})->middleware('localOnly');

Route::post('/token', function (\Illuminate\Http\Request $request) {
    $email = $request->input('email');
    $user = User::where('email', $email)->first();

    if (! $user) {
        return view('create-token', ['error' => 'User not found']);
    }

    $token = $user->createToken('api_token');

    return view('create-token', ['token' => $token->plainTextToken]);
})->name('create-token')->middleware('localOnly');

require __DIR__.'/auth.php';
