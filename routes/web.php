<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\DetailController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\SubjectController;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\RateController;
use App\Http\Controllers\FavouriteController;

Auth::routes();

Route::get('/', [HomeController::class,'index']);
Route::get('/home', [HomeController::class,'index'])->name('home');
Route::get('/search/{text}',[SearchController::class,'search']);
Route::get('/search/result/{text}',[SearchController::class,'search_result']);

// Ajax
Route::get('/find/subjects',[CategoryController::class , 'subjects']);
Route::get('/find/category/{id}',[CategoryController::class,'categories']);
Route::get('/find/subcategory/{id}',[CategoryController::class,'subcategories']);
Route::get('/rate/{user_id}/{product_id}/{rate}',[RateController::class , 'rateProduct']);

//zarin pal
Route::get('order','siteController@order');
Route::post('shop','siteController@add_order');

Route::get('/favourites',[FavouriteController::class , 'index'])->name('favourites');
//Route::get('/result',[ProductController::class,'result']);
Route::get('/product/{id}',[ProductController::class,'show'])->name('product.show');
Route::get('/category/{id}',[ProductController::class,'category']);

Route::prefix('order')->group(function() {

   Route::get('/address',[OrderController::class,'select_address'])->middleware('auth')->name('order.selectAddress');
   Route::post('/address',[OrderController::class,'create'])->middleware('auth')->name('order.create');
   Route::get('/check',[OrderController::class,'check'])->middleware('auth')->name('order.check');
   Route::get('/done/{order_id}',[OrderController::class,'order_done'])->middleware('auth')->name('order_done');

});

Route::prefix('profile')->middleware('auth')->group(function() {

    Route::get('', function (){
        return view('profile');
    })->name('profile');

    Route::get('/edit', [ProfileController::class, 'edit'])->name('user.edit');
    Route::put('/update', [ProfileController::class, 'update'])->name('user.update');
    Route::get('/addresses', [AddressController::class, 'addresses'])->name('addresses');
    Route::post('/addresses', [AddressController::class, 'create'])->name('addresses.create');
    Route::get('/addresses/edit/{id}', [AddressController::class, 'edit'])->name('addresses.edit');
    Route::put('/addresses/{id}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{id}', [AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::get('/change_password', [ProfileController::class, 'changePassword'])->name('change_password');

});

Route::prefix('/cart')->middleware('auth')->group(function (){

    Route::get('',[CartController::class , 'cart'])->name('cart');
    Route::get('/{product_id}/{user_id}',[CartController::class,'add']);
    Route::delete('/{product_id}',[CartController::class,'delete'])->name('delete_from_cart');
    Route::get('/count/{product_id}/{count}',[CartController::class,'counter']);

});

Route::prefix('/favourite')->middleware('auth')->group(function (){

    Route::get('/{product_id}/{user_id}',[FavouriteController::class,'add']);
    Route::delete('/{product_id}',[FavouriteController::class,'delete'])->name('delete');

});

Route::prefix('/admin')->group(function() {

    Route::get('/login','Auth\AdminLoginController@showLoginForm')->name('admin.login');
    Route::post('/login', 'Auth\AdminLoginController@login')->name('admin.login.submit');
    Route::get('logout', 'Auth\AdminLoginController@logout')->name('admin.logout');
    Route::get('', 'Auth\AdminController@index')->name('admin.dashboard');

}) ;

Route::prefix('/admin')->middleware('auth:admin')->group(function (){

    Route::prefix('/product')->group(function (){

        Route::delete('/{id}',[ProductController::class,'destroy'])->name('product.destroy');
        Route::get('',[ProductController::class,'index'])->name('product.index');
        Route::post('',[ProductController::class,'store'])->name('product.store');
        Route::get('/create',[ProductController::class,'create'])->name('product.create');
        Route::get('/{id}/edit',[ProductController::class,'edit'])->name('product.edit');
        Route::get('/image/create/{id}',[ImageController::class,'create'])->name('admin.image.create');
        Route::post('/image',[ImageController::class,'store'])->name('admin.image.store');
        Route::get('/image/{id}',[ImageController::class,'show'])->name('admin.image.show');
        Route::get('/image/{order}/{id}',[ImageController::class,'destroy'])->name('admin.image.destroy');

    });

    Route::prefix('/details')->group(function (){

        Route::get('',[DetailController::class,'index'])->name('admin.detail.index');
        Route::post('',[DetailController::class,'store'])->name('admin.detail.store');
        Route::delete('/{id}',[DetailController::class,'destroy'])->name('admin.detail.destroy');
        Route::get('/{id}/{number}',[DetailController::class,'show'])->name('admin.detail.show');

    });

    Route::prefix('/subject')->group(function (){

        Route::get('',[SubjectController::class,'index'])->name('admin.subject.index');
        Route::post('',[SubjectController::class,'store'])->name('admin.subject.store');
        Route::get('/{id}/edit',[SubjectController::class,'edit']);
        Route::put('/{id}',[SubjectController::class,'update'])->name('admin.subject.update');
        Route::delete('/{id}',[SubjectController::class,'destroy'])->name('admin.subject.destroy');

    });

    Route::prefix('/category')->group(function (){

        Route::get('',[CategoryController::class,'index'])->name('admin.category.index');
        Route::post('',[CategoryController::class,'store'])->name('admin.category.store');
        Route::get('/{id}/edit',[CategoryController::class,'edit'])->middleware('auth:admin');
        Route::put('/{id}',[CategoryController::class,'update'])->name('admin.category.update');
        Route::delete('/{id}',[CategoryController::class,'destroy'])->name('admin.category.destroy');

    });

    Route::prefix('/subcategory')->group(function (){

        Route::get('',[SubCategoryController::class,'index'])->name('admin.subcategory.index');
        Route::post('',[SubCategoryController::class,'store'])->name('admin.subcategory.store');
        Route::get('/{id}/edit',[SubCategoryController::class,'edit']);
        Route::put('/{id}',[SubCategoryController::class,'update'])->name('admin.subcategory.update');
        Route::delete('/{id}',[SubCategoryController::class,'destroy'])->name('admin.subcategory.destroy');

    });

    Route::prefix('/orders')->group(function (){

        Route::get('/new',[OrderController::class,'new'])->name('admin.orders.new');
        Route::get('/onway',[OrderController::class,'onway'])->name('admin.orders.onway');
        Route::get('/deliverd',[OrderController::class,'deliverd'])->name('admin.orders.deliverd');

    });

    Route::prefix('/order')->group(function (){

        Route::get('/show/{order_id}',[OrderController::class,'show'])->name('admin.orders.show');
        Route::get('/send/{order_id}',[OrderController::class,'send'])->name('admin.orders.send');
        Route::get('/deliver/{order_id}',[OrderController::class,'deliver'])->name('admin.orders.deliver');

    });
});
