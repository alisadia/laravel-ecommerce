<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as RulesPassword;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Cart;

// class resourceController extends Controller
// {
//     public function __invoke()
//     {
//         return view('login');
//     }
// }
class resourceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return 'hello';
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
       
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $seller = Auth::user();
        if ($seller->status !== 'Active') {
            return response()->json([
                'status' => false,
                'message' => 'You are not active yet. Cannot add products.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 401);
        }

        DB::beginTransaction();
        try {
            $image = $request->file('image');
            $uniqueName = uniqid() . '.' . $image->extension();
            $image->move(public_path('images'), $uniqueName);

            $product = new Product();
            $product->title = $request->title;
            $product->description = $request->description;
            $product->price = $request->price;
            $product->category = $request->category;
            $product->image = 'images/' . $uniqueName;
            $product->seller_id = $seller->id;
            $product->save();

            DB::commit();

            return response()->json([
                "status" => true,
                "message" => "Product added successfully",
            ], 200);
        } catch (\Exception $e) {

            DB::rollback();

            // if image exits in public folder and if transaction failed then remove the image from folder

            if (isset($uniqueName) && file_exists(public_path('images/' . $uniqueName))) {
                unlink(public_path('images/' . $uniqueName));
            }
            return response()->json([
                "status" => false,
                "message" => "Failed to add product",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
