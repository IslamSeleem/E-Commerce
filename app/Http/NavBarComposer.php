<?php

namespace App\Http;

use App\Cart;
use Illuminate\View\View;
use DB;
use Illuminate\Support\Facades\Auth;
use Session;

class NavBarComposer
{
    public function compose(View $view) {

        $categories = DB::table('categorey')->get();
        $subcategories = DB::table('supcategorey')->get();


        $view->with(['categories'=>$categories,'subcategories'=>$subcategories]);
    }
}