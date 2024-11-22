<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductDealsController;
use App\Http\Controllers\ProductShoppingCartController;
use App\Http\Controllers\StockController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductOrdersController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PasswordResetController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });




// Dashboard


// JWT Authenficiation
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/dashboard', 'App\Http\Controllers\DashboardController@index');
Route::get('/auth', 'App\Http\Controllers\UserController@getAuthenticatedUser');
Route::post('/register', 'App\Http\Controllers\UserController@register');
Route::post('/login', [UserController::class, 'login']);

// Address
Route::get('/user/default-address', 'App\Http\Controllers\UserAddressController@show');
Route::post('/user/create-user-address', 'App\Http\Controllers\UserAddressController@createUser');
Route::post('/user/address', 'App\Http\Controllers\UserAddressController@store');

// Product
Route::get('/products', 'App\Http\Controllers\ProductController@index');
Route::get('/products/{id}', 'App\Http\Controllers\ProductController@show');
Route::get('/product/hot-deal', 'App\Http\Controllers\ProductDealsController@hotDeals');
Route::post('/products', 'App\Http\Controllers\ProductController@store');//10
Route::delete('/products/{id}', 'App\Http\Controllers\ProductController@destroy');//1
Route::put('/products/{id}', 'App\Http\Controllers\ProductController@update');//12

// Product Orders
Route::post('/stripe', 'App\Http\Controllers\ProductOrdersController@stripePost');
Route::post('/product/orders', 'App\Http\Controllers\ProductOrdersController@store');
Route::post('/create-payment-intent', [ProductOrdersController::class, 'createPaymentIntent']);

// Product Categories
Route::delete('/product/categories/{id}', [CategoryController::class, 'destroy']);
Route::put('/product/categories/{id}', [CategoryController::class, 'update']);
Route::get('/product/categories/{id}', [CategoryController::class, 'show']);
Route::get('/product/categories', 'App\Http\Controllers\CategoryController@index');
Route::get('/product/categories/{id}/top-selling', 'App\Http\Controllers\CategoryController@topSelling');
Route::get('/product/categories/{id}/new', 'App\Http\Controllers\CategoryController@newPage');
Route::post('/product/categories', 'App\Http\Controllers\CategoryController@store');

// Product Shopping Cart
Route::get('/product/cart-list/count', 'App\Http\Controllers\ProductShoppingCartController@cartCount');
Route::get('/product/cart-list/', 'App\Http\Controllers\ProductShoppingCartController@index');
Route::post('/product/cart-list', 'App\Http\Controllers\ProductShoppingCartController@store');
Route::post('/product/cart-list/guest', 'App\Http\Controllers\ProductShoppingCartController@guestCart');
Route::put('/product/cart-list/{id}', 'App\Http\Controllers\ProductShoppingCartController@update');
Route::delete('/product/cart-list/{id}', 'App\Http\Controllers\ProductShoppingCartController@destroy');

//Product Wishlist
Route::get('/product/wishlist/count', 'App\Http\Controllers\ProductWishlistController@count');
Route::get('/product/wishlist', 'App\Http\Controllers\ProductWishlistController@index');
Route::post('/product/wishlist', 'App\Http\Controllers\ProductWishlistController@store');
Route::delete('/product/wishlist/{id}', 'App\Http\Controllers\ProductWishlistController@destroy');

// Product Stocks
Route::get('/product/stocks/{id}', 'App\Http\Controllers\StockController@show');
//search
//search
Route::get('/product/search', [ProductController::class, 'search']);

// Newsletter
Route::post('/newsletter', 'App\Http\Controllers\NewsLetterController@store');

Route::post('/abate', 'App\Http\Controllers\AbateController@store');
Route::get('/abate/getAll', 'App\Http\Controllers\AbateController@getAll');
Route::get('/abate/getAbate/{id}', 'App\Http\Controllers\AbateController@getAbateById');
Route::delete('/abate/{id}', 'App\Http\Controllers\AbateController@delete');


Route::post('products', [ProductController::class, 'store']);
Route::post('/create-payment-intent', [ProductOrdersController::class, 'createPaymentIntent']);
//thanh toan vnpay
Route::post('/vnpay_payment',[PaymentController::class,'vnpay_payment']);
Route::get('/users', 'App\Http\Controllers\UserController@show');//8
Route::delete('/users/{id}', 'App\Http\Controllers\UserController@deleteUser');//8
// Route để hiển thị form reset mật khẩu
// routes/web.php
Route::get('password/reset/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/update', [PasswordResetController::class, 'updatePassword'])->name('password.update');
Route::post('/password/email', [PasswordResetController::class, 'sendResetLink']);
