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

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|regex:/^[A-Za-z ]*$/',
            'email' => 'required|string|email',
            'password' => ['required', 'string', RulesPassword::min(8)->mixedcase()->numbers()->symbols()->uncompromised()]
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
            if (Auth::attempt($request->only(['email', 'password']))) {
                $user = Auth::user();
                $token = $user->createToken('API Token')->plainTextToken;
                DB::commit();
                return response()->json(
                    [
                        "status" => true,
                        "result" => "Logged in sucessfully",
                        'user_type' => $user->user_type,
                        'token' => $token,

                    ],
                    200
                );
            }
        } catch (\Exception $e) {

            DB::rollback();
            return response()->json(
                [
                    "status" => false,
                    "result" => "Credentials not valid",
                ],
                404
            );
        }
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        $user->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully'
        ]);
    }
    public function display_user(Request $request)
    {
        return $request->user();
    }

    public function add_product(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
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

            $image = $request->file('image');
            $uniqueName = uniqid() . '.' . $image->extension();
            $image->move(public_path('images'), $uniqueName);

            $product = new Product();
            $product->title = $request->title;
            $product->description = $request->description;
            $product->price = $request->price;
            $product->category = $request->category;
            $product->image = 'images/' . $uniqueName;
            $product->save();


            $response = [
                "status" => true,
                "result" => "Product added",
            ];

            DB::commit();

            return response()->json($response, 200);
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


    public function display_product()
    {
        // check if user is seller then display only that products which are added by him

        if (auth()->check() && auth()->user()->user_type == 2) {
            $sellerId = auth()->user()->id;
            $products = Product::select('product.id', 'product.title', 'product.description', 'product.image', 'product.category', 'product.price')
                ->where('product.seller_id', $sellerId)
                ->get();
        }
        //checks if user is customer then dispaly all products added by many sellers

        else {
            $products = Product::select('product.id', 'product.title', 'product.description', 'product.image', 'product.category', 'product.price', 'users.name', 'product.seller_id')
                ->join('users', 'product.seller_id', '=', 'users.id')
                ->get();
        }
        // iterates over each product,adds an image_url property to it.this contains the URL of the product image derived from the image attribute in the database.

        foreach ($products as $product) {
            $product->image_url = asset($product->image);
        }

        return $products;
    }


    // delete product
    public function destroy($id)
    {
        $product = Product::find($id);

        if ($product) {
            $product->delete();
        } else {

            return response()->json(['message' => 'Product not found.'], 404);
        }

        return  response()->json(['message' => 'Product deleted sucessfully.'], 200);
    }

    public function addToCart(Request $request, $id)
    {

        $customer = Auth::user();  // retrieves currently authenticated user
        $product = Product::find($id); // fetches product on the provided id
        // Log::info($product);


        DB::beginTransaction();
        try {
            $cart = new Cart();
            $cart->product_title = $product->title;
            $cart->price = $product->price;
            $cart->quantity = $request->quantity;
            $cart->customer_id = $customer->id;
            $cart->save();

            // Log::info($cart->quantity);
            DB::commit();
            return response()->json(
                [
                    'message' => 'Product added to cart successfully'
                ],
                200
            );
        } catch (\Exception $e) {

            DB::rollback();

            return response()->json([
                "status" => false,
                "message" => "Failed to add product to cart",
                "error" => $e->getMessage()
            ], 500);
        }
    }
    public function viewCart()
    {
        $user = Auth::user(); // retrieves currently authenticated user

        $cartItems = Cart::where('customer_id', $user->id)->get(['product_title', 'price', 'quantity', 'id']);

        return $cartItems;
    }
    public function removeFromCart($productid)
    {
        $user = Auth::user();

        $cartItem = Cart::where('customer_id', $user->id)
            ->where('id', $productid)
            ->first();

        if (!$cartItem) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found in cart',
            ], 404);
        }

        $cartItem->delete();

        return response()->json([
            'status' => true,
            'message' => 'Product removed from cart successfully',
        ], 200);
    }
}
