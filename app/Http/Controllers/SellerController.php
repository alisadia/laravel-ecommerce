<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as RulesPassword;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class SellerController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|regex:/^[A-Za-z ]*$/',
            'email' => 'required|string|email',
            'password' => ['required', 'string', RulesPassword::min(8)->mixedcase()->numbers()->symbols()->uncompromised()],
            'username' => 'required|string|alpha_dash|max:255',
            'phone' => 'required|string',
            'address' => 'required|string|max:255',
            'shopname' => 'required|string|max:255',

        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ],
                401
            );
        }
        DB::beginTransaction();
        try {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->username = $request->username;
            $user->user_type = 2;
            $user->phone = $request->phone;
            $user->status = 'Pending';
            $user->address = $request->address;
            $user->shopname = $request->shopname;
            $user->save();


            DB::commit();

            return response()->json(
                [
                    "status" => true,
                    "result" => "data saved",
                    'token' => $user->createToken("API TOKEN")->plainTextToken
                ],
                200
            );
        } catch (\Exception $e) {

            DB::rollback();
            return response()->json(
                [
                    "status" => false,
                    "result" => "data not saved",
                    "error" => $e->getMessage()
                ],
                500
            );
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => ['required', 'string']
        ]);

        if ($validator->fails()) {
            $arr = [
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ];
            return response()->json(
                $arr,
                401
            );
        }

        DB::beginTransaction();

        try {
            if (Auth::attempt($request->only(['email', 'password']))) {
                $seller = Auth::user();
                $token = $seller->createToken('API Token')->plainTextToken;
                // Log::info('this attempt', $seller);
                DB::commit();
                $arr = [
                    "status" => true,
                    "result" => "Logged in sucessfully",
                    'token' => $token,
                ];
                return response()->json(
                    $arr,
                    200
                );
            }
        } catch (\Exception $e) {

            DB::rollback();
            $arr = [
                "status" => false,
                "result" => "Credentials not valid",
                "error" => $e->getMessage(),
            ];
            return response()->json(
                [
                    $arr,
                ],
                404
            );
        }
    }

    // display seller in admin dashboard
    public function display_seller()
    {
        $sellers = User::where('user_type', 2)->select('id', 'name', 'username', 'email', 'status', 'shopname', 'address')->get();
        return $sellers;
    }

    // admin activate deactivate sellers dpending on the status of seller

    public function activate($id)
    {
        $seller = User::find($id);  // retrive seller id from database

        if ($seller->status === 'Inactive' || $seller->status === 'Pending') {

            $seller->status = 'Active';
            $seller->save();

            return response()->json([
                'message' => 'Seller is active now',
            ], 200);
        } else {
            return response()->json([
                'message' => 'Seller cannot be activated because it is already active',
            ], 400);
        }
    }

    public function deactivate($id)
    {
        $seller = User::find($id);

        if ($seller->status === 'Active' || $seller->status === 'Pending') {
            $seller->status = 'Inactive';
            $seller->save();

            return response()->json([
                'message' => 'Seller is deactivated now',
            ]);
        } else {
            return response()->json([
                'message' => 'Seller cannot be deactivated because it is already inactive ',
            ], 400);
        }
    }



    public function add_product(Request $request)
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

    public function show_seller_products($sellerId)
    {
        $seller = User::find($sellerId);
        if (!$seller) {
            return response()->json(['error' => 'Seller not found'], 404);
        }

        $sellerProducts = Product::where('seller_id', $seller->id)
            ->join('users', 'product.seller_id', '=', 'users.id')
            ->select('product.title', 'product.description', 'product.image', 'product.price', 'users.name', 'users.shopname')
            ->get();
        foreach ($sellerProducts as $product) {
            $product->image_url = asset($product->image);
        }

        return $sellerProducts;
    }
}
