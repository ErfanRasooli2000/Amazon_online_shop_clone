<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        for($id=1 ; $id<5 ; $id++)
        {
            $products[$id] = DB::table('products')
                ->leftJoin('sub_categories' , 'products.subcategory_id' , 'sub_categories.id')
                ->leftJoin('categories' , 'sub_categories.category_id' , 'categories.id')
                ->leftJoin('images' , 'products.id' , 'images.product_id')
                ->leftjoin(DB::raw('(select f.product_id as product_id , COUNT(*) as count from favourites f GROUP by f.product_id) AS q') , 'products.id' , 'q.product_id')
                ->where('categories.id' , '=' , $id)
                ->where('images.order' , '=' , 1)
                ->orderByDesc('count')
                ->limit(3)
                ->get();
        }

        return view('index',
            [
                'cellphone' => $products[1],
                'laptop' => $products[2] ,
                'display' => $products[3],
                'speaker' => $products[4]
            ]);

        //TODO : refactor next slide for top of the home page
    }
}
