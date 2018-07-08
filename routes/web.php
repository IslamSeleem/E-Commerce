<?php

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

Route::get('/', 'ProductController@getHome');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/user/{name}' , 'UserController@getProfile');

Route::get('/category/{cat}' , 'ProductController@getCat');
Route::get('/category/{cat}/{scat}' , 'ProductController@getSubCat');
Route::get('/product/{id}' , 'ProductController@getProduct');
Route::get('/quickview/{id}' , 'ProductController@getQuickView');
Route::get('addtowishlist', function () {
    return back()->with('success', 'Product added to wish list');
});
Route::post('addtowishlist' , ['as'=>'Product.addWishList','uses'=>'CartController@addWishList']);

Route::get('addtocart', function () {
    return redirect()->back();
});

Route::post('addtocart' , ['as'=>'Product.addToCart','uses'=>'CartController@addToCart'
]);
Route::get('/addreview/{id}', function () {
    return back()->with('success', 'Review added successfully');
});
Route::post('/addreview/{id}' , 'ProductController@addReview'
);

Route::get('/catg', function () {
    return view('/layouts/catg');
});
Route::get('/shop', 'ProductController@getShop');

Route::get('/singlePro', function () {
    return view('/layouts/singlePro');
});
Route::get('/cart', 'CartController@getCart');
Route::post('cart' , ['as'=>'Card.modifyCart','uses'=>'CartController@modifyCart'
]);

Route::get('/checkout', 'CartController@getCheckout');
Route::post('/checkout', ['as'=>'Checkout.setCheckout','uses'=>'CartController@setCheckout']);

Route::get('/removeCartItem/{id}','CartController@removeCardList');

Route::get('/contact', 'ContactUSController@contactUS');

Route::post('/contact', ['as'=>'contactus.store','uses'=>'ContactUSController@contactUSPost']);

Route::get('/faq', function () {
    return view('/layouts/faq');
});

Route::get('/terms', function () {
    return view('/layouts/terms');
}); 

Route::get('/policy', function () {
    return view('/layouts/policy');
});
Route::get('/about', function () {
    return view('/layouts/about');
});

Route::get('/wishlist','CartController@getWishList');
Route::get('/removewishlist/{id}','CartController@removeWishList');


Route::get('logout', 'Auth\LoginController@logout');

//Social provider routing
Route::get('auth/{provider}', 'Auth\RegisterController@redirectToProvider');
Route::get('auth/{provider}/callback', 'Auth\RegisterController@handleProviderCallback');

Route::get('likeProduct', function () {
    return redirect()->back();
});

Route::post('likeProduct' , ['as'=>'Product.like','uses'=>'ProductController@likeProduct'
]);

Route::get('/cartreview', 'CartController@getReview');
Route::post('/cartreview' , ['as'=>'Product.Review','uses'=>'CartController@cartReview'
]);

Route::get('/skipreview', 'CartController@skipCartReview');

Route::get('/distributors' , 'pageController@getDistro');
Route::get('/user/{name}/order/{orderid}' , 'UserController@getorder');

Route::get('/search', 'ProductController@getSearch');
Route::post('/search' , ['as'=>'Product.Search','uses'=>'ProductController@getSearch'
]);

Route::get('copoun', function () {
    return redirect()->back();
});

Route::post('copoun' , ['as'=>'Cart.copoun','uses'=>'CartController@setCoupon'
]);

Route::get('/editprofile' , 'UserController@edituser');

Route::post('/editprofile', ['as'=>'User.Update','uses'=>'UserController@updateUser']);