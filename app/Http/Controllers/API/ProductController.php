<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Product;

// use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        // Mengambil semua produk dari database
        $products = Product::latest()->paginate(10);
        return response()->json(new ProductCollection($products), Response::HTTP_OK);
    }

    public function store(ProductRequest $request){
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('images', 'public');
        } elseif ($request->image && is_string($request->image)) {
            $data['image'] = $request->image; // URL
        }

        $product = Product::create($data);
        return response()->json([
            'status'   => true,
            'message'  => 'Produk Berhasil Ditambahkan',
            'data'     => new ProductResource($product)
        ], Response::HTTP_CREATED);
    }

    public function show($id){
        $product = Product::findOrFail($id);
        return response()->json([
            'status'   => true,
            'message'  => 'Detail Produk',
            'data'     => new ProductResource($product)
        ], Response::HTTP_OK);
    }

    public function update(ProductRequest $request, $id){
        $product = Product::findOrFail($id);
        $data = $request->validated();

        if ($request->hasFile('image')) {
            // Delete old image if it's a file
            if ($product->image && !filter_var($product->image, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('images', 'public');
        } elseif ($request->image && is_string($request->image)) {
            // Delete old image if it's a file
            if ($product->image && !filter_var($product->image, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->image; // URL
        }

        $product->update($data);
        return response()->json([
            'status'   => true,
            'message'  => 'Produk Berhasil Diupdate',
            'data'     => new ProductResource($product)
        ], Response::HTTP_OK);
    }

    public function destroy(Product $product){
        $product->delete();
        return response()->json([
            'status'   => true,
            'message'  => 'Produk Berhasil Dihapus',
            'data'     => new ProductResource($product)
        ], Response::HTTP_OK);

    }
}


