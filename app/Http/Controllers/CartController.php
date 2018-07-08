<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;

use App\Cart;
use App\Review;
use App\Shipping;
use App\wishList;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Session;
use Redirect;

class cartController extends Controller
{


    function addWishList (Request $request){
        if (Auth::check()) {

            $user = Auth::user();
            $foundbefore = DB::table('wishList')->where([
                ['user_ID', '=', $user->id],
                ['prod_ID', '=', $request->prodD_ID],
            ])->get();




            $wishListprod = new wishList;
            $wishListprod->prod_ID = $request->prodD_ID;
            $wishListprod->user_ID = $user->id;
            $wishListprod->save();



        }
        else{

            return redirect('/login');

        }
    }

    function addToCart(Request $request){
        $product = DB::table('product_details')
            ->join('products', 'product_details.prod_id', '=', 'products.id')
            ->select('products.*','product_details.size as size' , 'product_details.color as color','product_details.id as prodD_id','product_details.amount as maxAmount')->where('product_details.id','=', $request->prodD_ID)
            ->first();
        $product->image = asset($product->image);
        $qty=$request->amount;
        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        $prodD_id = $request->prodD_ID;

        $olditem =false;

        if ( Session::has('cart'))
        {

        if (array_key_exists($prodD_id,$cart->items)){

            $olditem = true;


        }
        }



        $cart->add($request->prod_ID,$request->prodD_ID,$qty,$product);
        $price = $cart->items[$request->prodD_ID];
        $price = $price['price'];
        $quan = $qty;
        $request->session()->put('cart',$cart);
        return (['totalqty'=>$cart->totalqty,'totalPrice'=>$cart->totalPrice,'productUp'=>$product,'price'=>$price,'quan'=>$quan,'olditem'=>$olditem]);

    }

    function getWishList(){


        if (Auth::check()) {

            $user = Auth::user();

            $whishlistProducts=DB::table('wishList')
                ->join('products', 'prod_id', '=', 'products.id')
                ->select('products.*')->where('user_ID', '=', $user->id)
                ->get();

            return view('/layouts/Wishlist')->with(['whishlistProducts'=>$whishlistProducts]);

        }
        else{

            return redirect('/login');

        }

    }


    function removeWishList($id){


        if (Auth::check()) {

            $user = Auth::user();


            DB::table('wishList')->where([
                ['user_ID', '=', $user->id],
                ['prod_ID', '=', $id],
            ])->delete();

            Session::flash('message', 'Product removed successfully from wishList');
            return Redirect::back();
        }
        else{

            return redirect('/login');

        }
    }


    function getCart(){

        $shipping = DB::table('shipping')->get();

        $oldCart = Session::has('cart') ? Session::get('cart') : null;

        if ($oldCart){
            $cart = new Cart($oldCart);

            $cartItems = $cart->items;

            $cartProdDId = array_column ($cartItems,'pordD_id');
            $totalPrice = $cart->totalPrice;

            $cartProducts=DB::table('product_details')
                ->join('products', 'product_details.prod_id', '=', 'products.id')
                ->select('products.*','product_details.size as size' , 'product_details.color as color','product_details.id as prodD_id','product_details.amount as maxAmount')->whereIn('product_details.id', $cartProdDId)
                ->get();
        }
        else{
            $cartProducts=[];
            $totalPrice = 0;
            $cart= null;

        }


        return view('/layouts/cart')->with(['cartProducts'=>$cartProducts,'totalPrice'=>$totalPrice,'cart'=>$cart,'shipping'=>$shipping]);


    }


    function removeCardList(Request $request ,$id){

        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);
        $cart->remove($id);

        $request->session()->put('cart',$cart);



        Session::flash('message', 'Product removed successfully from cart');
        return Redirect::back();

    }

    function modifyCart(Request $request){

        $oldCart = Session::has('cart') ? Session::get('cart') : null;
        $cart = new Cart($oldCart);

        if ($request->val == 1){

            $cart->increment($request->prodD_ID);
            $request->session()->put('cart',$cart);


        }
        else{

            $cart->decrement($request->prodD_ID);
            $request->session()->put('cart',$cart);

        }

        $item = $cart->items[$request->prodD_ID];
        $itemQty = $item['qty'];

        return (['totalqty'=>$cart->totalqty,'totalPrice'=>$cart->totalPrice,'itemQty'=>$itemQty]);

    }

    function getCheckout(){
        if (Auth::check()) {
            if ( Session::has('cart')) {

                $shipping = DB::table('shipping')->get();

                $user = Auth::user();

                $oldCart = Session::has('cart') ? Session::get('cart') : null;

                $cart = new Cart($oldCart);

                $cartItems = $cart->items;

                $cartProdDId = array_column($cartItems, 'pordD_id');

                $totalDiscount = 0;

                foreach ($cartItems as $cartItem) {

                    if ($cartItem['item']->sale == 1) {

                        $totalDiscount = $totalDiscount + ($cartItem['item']->price * ($cartItem['item']->discount / 100));
                    }

                }


                $cartProducts = DB::table('product_details')
                    ->join('products', 'product_details.prod_id', '=', 'products.id')
                    ->select('products.*', 'product_details.size as size', 'product_details.color as color', 'product_details.id as prodD_id', 'product_details.amount as maxAmount')->whereIn('product_details.id', $cartProdDId)
                    ->get();

                return view('/layouts/checkout')->with(['cartProducts' => $cartProducts, 'totalPrice' => $cart->totalPrice, 'cart' => $cart, 'shipping' => $shipping, 'totalDiscount' => $totalDiscount]);

            }
            else{

                return redirect('/');

            }
        }
        else{

            return redirect('/login');

        }

    }

    function setCheckout(Request $request){
        $user = Auth::user();

        $shippedId = DB::table( 'shippedto_info')->where([
            ['user_id', '=', $user->id],
            ['state', '=', $request->gover],
            ['address', '=', $request->address],
            ['phone1', '=', $request->phone],
            ['payment_method', '=', $request->poption],
        ])->get();

        if ($shippedId->isEmpty())
        {







            $shipped = new Shipping;
            $shipped->user_id = $user->id;
            $shipped->name = $request->pname;
            $shipped->country = $request->country;
            $shipped->state = $request->gover;
            $shipped->city = $request->parea;
            $shipped->address = $request->address;
            $shipped->delivered_time = $request->orderTime;
            $shipped->phone1 = $request->phone;
            $shipped->phone2 = $request->aphone;
            $shipped->notes = $request->notes;
            $shipped->payment_method = $request->poption;
            $shipped->save();
        }

        $shippedId = DB::table( 'shippedto_info')->where([
            ['user_id', '=', $user->id],
            ['state', '=', $request->gover],
            ['address', '=', $request->address],
            ['phone1', '=', $request->phone],
            ['payment_method', '=', $request->poption],
        ])->get();


        do{
            $id = mt_rand(1000, 999999999999);
            $order= DB::table('orders')->where( 'id','=',$id)->get();

        }while($order->count()>0);

        DB::table('orders')->insert(['id'=>$id,'user_id'=>$user->id,'total_cost'=>$request->totalcost , 'paid'=>0 ,'status'=>"under revision" , 'shippedTo_id'=> $shippedId[0]->id ]);
        $orderID = DB::table('orders')->where([['total_cost', '=', $request->totalcost],
            ['paid', '=', 0],
            ['status', '=', 'under revision'],
            ['shippedTo_id', '=', $shippedId[0]->id]])->get();


        $oldCart = Session::has('cart') ? Session::get('cart') : null;

        $cart = new Cart($oldCart);

        $cartItems = $cart->items;

        $cartItemsReview = [];



        foreach ($cartItems as $cart)
        {
            DB::table('product_details')->where('id','=',$cart['pordD_id'])->decrement('amount',$cart['qty']);
            DB::table('shippingDetails')->insert(['pro_details_id' => $cart['pordD_id'], 'user_id' => $user->id, 'quantity' => $cart['qty'] , 'order_id'=>$orderID[0]->id ]);
            $cartItemsReview[$cart['pord_id']]=['name'=>$cart['item']->name,'pord_id'=>$cart['pord_id'],'image'=>$cart['item']->image];

        }


        $request->session()->put('review',$cartItemsReview);
        $request->session()->forget('cart');


        DB::table('user_activity')->insert(['activity_id' => 3, 'user_id' => $user->id]);


        Session::flash('message', 'Order has been created successfully 
        Please Review Items First');


        return redirect('/cartreview');



    }

    function getReview ()
    {


        if (Auth::check()) {

            $oldCart = Session::has('review') ? Session::get('review') : null;
            $cartItems=$oldCart;



                return view('/layouts/Review')->with(['cartItems'=>$cartItems]);

            }



    }


    function cartReview(Request $request){

        $oldCart = Session::has('review') ? Session::get('review') : null;
        $cartItems=$oldCart;

        foreach ($cartItems as $item){

            $rate = $request->input('reviewrating'.$item['pord_id']);
            $comment = $request->input('reviewcomment'.$item['pord_id']);
            if($rate <> null & $comment<> null){
                $this->addReview($item['pord_id'],$rate,$comment);
            }


        }

        $request->session()->forget('review');


        return redirect('/');




    }

    function skipCartReview(){



        session()->forget('review');


        return redirect('/');




    }




    function addReview ($id , $rate , $comment)
    {


        if (Auth::check()) {

            $user = Auth::user();

            $checkLike = DB::table('reviews')->where([
                ['user_ID', '=', $user->id],
                ['prod_ID', '=', $id],
            ])->get();

            if ($checkLike->count()>0) {


                return (['reviwed'=>true]);

            }
            else{

                $review = new Review;
                $review->prod_id = $id;
                $review->user_id = $user->id;
                $review->rate_value = $rate;
                $review->comment = $comment;
                $review->save();

                DB::table('user_activity')->insert(['activity_id' => 1, 'user_id' => $user->id]);


                return (['reviwed'=>false]);

            }

        }


    }

    function setCoupon (Request $request){

        $Coupon = DB::table('applyto')
            ->join('sale_coupon', 'applyto.copoun_id', '=', 'sale_coupon.id')
            ->select('applyto.*', 'sale_coupon.*')->where('code','=',$request->Coupon)->get();
        date_default_timezone_set('Africa/Cairo');
        $currentTime = date_default_timezone_get();




        if ($Coupon[0]->start_time <= $currentTime & $Coupon[0]->end_time >= $currentTime ){

            if ($Coupon[0]->used_times < $Coupon[0]->max_use_times){


                $oldCart = Session::has('cart') ? Session::get('cart') : null;

                $cart = new Cart($oldCart);

                $cart->updateCopoun($cart,$Coupon);






            }else{

                $status="exceeded";

            }

        } elseif($Coupon[0]->start_time > $currentTime) {

            $status = "not start yet";

        }
        elseif ($Coupon[0]->end_time < $currentTime){

            $status = "expired";

        }

        return ([$Coupon]);

    }

}
