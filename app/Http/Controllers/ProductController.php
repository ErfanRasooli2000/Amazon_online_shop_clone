<?php

namespace App\Http\Controllers;
use App\Models\AboutProduct;
use App\Models\Category;
use App\Models\Detail;
use App\Models\Favourite;
use App\Models\SubCategory;
use App\Models\Subject;
use App\Models\Product;
use App\Models\Image;
use App\Models\Rate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function create()
    {
        return view('admin.add_product' , [
            'products' => Product::orderBy('id','desc')->limit(5)->get()
        ]);
    }

    public function show($id)
    {
        $product = Product::where('id',$id)->get()->firstOrFail();

        $details = DB::table('detail_product')->where('product_id',$id)->join('details','details.id','=','detail_product.detail_id')->get();

        if(Auth::user())
        {
            $user = Auth::user();
            $rate = Rate::where('product_id',$id)->where('user_id',$user->id)->first()->rate ?? 0;
        }

        return view('product',
            [
                'product'=>$product,
                'images'=>$product->images,
                'rates'=>$product->rates,
                'details'=>$details,
                'abouts'=>$product->about_product,
                'rate'=>$rate ?? 0
            ]);
    }

    public function index()
    {
        return view('admin.all_products', [
            'products' => Product::all()
        ]);
    }

    public function edit($id)
    {
        $product = Product::findorfail($id);

        $sub_category = $product->subcategory;
        $category = $sub_category->category;
        $subject = $category->subject;

        return view('admin.edit_product' , ['product' => $product,
            'subjects'=> Subject::all(),
            'categories' => Category::where('subject_id',$subject->id)->get(),
            'subcategories' => SubCategory::where('category_id',$category->id)->get(),
            'abouts' => AboutProduct::where('product_id',$id)->get(),
            'details' => DB::table('detail_product')->where('product_id',$id)->join('details','details.id','=','detail_product.detail_id')->get(),
            'alldetails' => Detail::where('subcategory_id',$sub_category->id)->get()
        ]);
    }
    public function destroy($id)
    {
        Product::where('id',$id)->delete();

        return view('admin.all_products',[
            'products' => Product::all()
        ]);
    }
    public function category($id)
    {
        //TODO : Option For Scrolling

        $favourites = $this->getTop($id , 'favourites');
        $rates = $this->getTop($id , 'rates');

        $sales = DB::table('categories')->select('*')
            ->leftJoin('sub_categories' , 'sub_categories.category_id','=' , 'categories.id')
            ->leftJoin('products' , 'products.subcategory_id','=','sub_categories.id')
            ->leftJoin('images','images.product_id','=','products.id')
            ->where('images.order',1)->where('categories.id',$id)
            ->orderBy('sales','DESC')->take(4)->get();

        $sub_categories = SubCategory::where('category_id',$id)->get();

        return view('category',compact('sub_categories','sales','rates','favourites'));
    }

    public function store(Request $request)
    {
        $product = Product::create($request -> validate([
            'title'=>'required|min:5|max:100',
            'full_name'=>'required|min:5|max:200',
            'price'=>'required|integer',
            'quantity'=>'required|integer|min:0',
            'discount'=>'required|integer|min:0|max:100',
            'subcategory_id'=>'required|exists:sub_categories,id'
        ]));

        for($i=1 ; $i<=$request->input('detailcounter') ; $i++)
        {
            DB::table('detail_product')->insert([
                'value'=>$request->input('detailvalue'.$i),
                'detail_id'=>$request->input('detail'.$i),
                'product_id'=>$product->id
            ]);
        }
        for($i=1 ; $i<=$request->input('aboutcounter') ; $i++)
        {
            DB::table('about_products')->insert([
                'text'=>$request->input('about'.$i),
                'product_id'=>$product->id
            ]);
        }
        return redirect(route('admin.image.create',['id'=>$product->id]));
    }


    private function getTop($id , $table) :object
    {
        return DB::table('categories')->select('*' , DB::raw('products.id as product_id'))
            ->leftJoin('sub_categories' , 'sub_categories.category_id','=' , 'categories.id')
            ->leftJoin('products' , 'products.subcategory_id','=','sub_categories.id')
            ->leftJoin('images','images.product_id','=','products.id')
            ->leftJoin(DB::raw('(SELECT f.product_id as product_id , COUNT(*) as counts from '.$table.' f group by f.product_id) as q') , 'q.product_id' , '=','products.id')
            ->where('images.order',1)->where('categories.id',$id)
            ->orderByDesc('q.counts')->take(4)->get();
    }

    public function result()
    {
        $products = DB::table('products')
            ->join('rates','products.id','=','rates.product_id')->get()->all();
        $image = Image::select('*')->get()->all();
        $images = array();

        $num = 1;
        foreach ($image as $img)
        {
            if($num==$img->product_id)
            {
                $images[$num]=$img->file;
                $num++;
            }
        }
        return view('result',['products'=>$products ,'image'=>$images]);
    }
}
