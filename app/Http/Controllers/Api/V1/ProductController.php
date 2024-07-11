<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Requests\ProductSearchRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with(['category', 'tags', 'images', 'variations'])->get();
        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {
        $this->authorize('create', Product::class);
        // Validate the incoming request using ProductRequest

        // Create a new product instance
        $product = new Product();

        // Assign product attributes
        $product->name = $request->input('name');
        $product->description = $request->input('description');
        $product->price = $request->input('price');
        $product->stock = $request->input('stock');
        $product->category_id = $request->input('category_id');
        $product->slug = Str::slug($request->input('name'));

        // Save the product
        $product->save();

        // Handle variations creation if provided
        if ($request->has('variations')) {
            foreach ($request->input('variations') as $variationData) {
                $variation = new ProductVariation();
                $variation->product_id = $product->id;
                $variation->type = $variationData['type'];
                $variation->value = $variationData['value'];
                $variation->price = $variationData['price'];
                $variation->save();
            }
        }

        // Handle images creation if provided
        if ($request->has('images')) {
            foreach ($request->input('images') as $imageData) {
                $image = new ProductImage();
                $image->product_id = $product->id;
                $image->url = $imageData['url'];
                $image->is_primary = $imageData['is_primary'] ?? false;
                $image->save();
            }
        }

        // Handle tags assignment if provided
        if ($request->has('tags')) {
            $product->tags()->sync($request->input('tags'));
        }

        Cache::flush();

        // Return a JSON response with the newly created product and a 201 status code
        return response()->json($product, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $slug)
    {
        try {
            $product = Product::with(['category', 'tags', 'images', 'variations'])
            ->where('slug', $slug)
            ->firstOrFail();
            return response()->json($product);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Product not found.'], 404);
        }
    }

    public function search(ProductRequest $request)
    {
        $validated = $request->validated();

        $products = Product::query();

        if ($validated['name'] ?? false) {
            $products->where('name', 'LIKE', "%{$validated['name']}%");
        }

        if ($validated['description'] ?? false) {
            $products->where('description', 'LIKE', "%{$validated['description']}%");
        }

        if ($validated['category_id'] ?? false) {
            $products->where('category_id', $validated['category_id']);
        }

        if ($validated['tags'] ?? false) {
            $products->whereHas('tags', function($q) use ($validated) {
                $q->whereIn('name', $validated['tags']);
            });
        }

        if ($validated['price_min'] ?? false) {
            $products->where('price', '>=', $validated['price_min']);
        }

        if ($validated['price_max'] ?? false) {
            $products->where('price', '<=', $validated['price_max']);
        }

        if ($validated['rating_min'] ?? false) {
            $products->where('rating', '>=', $validated['rating_min']);
        }

        if ($validated['brand'] ?? false) {
            $products->where('brand', 'LIKE', "%{$validated['brand']}%");
        }

        if ($validated['size'] ?? false) {
            $products->where('size', 'LIKE', "%{$validated['size']}%");
        }

        if ($validated['color'] ?? false) {
            $products->where('color', 'LIKE', "%{$validated['color']}%");
        }

        if ($validated['availability'] ?? false) {
            $products->where('availability', $validated['availability']);
        }

        if ($validated['sort_by'] ?? false && $validated['sort_order'] ?? false) {
            $products->orderBy($validated['sort_by'], $validated['sort_order']);
        }

        $products = $products->paginate(16);

        return response()->json($products);
    }


    /**
     * Update the specified product in storage.
     *
     * @param  \App\Http\Requests\ProductRequest  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(ProductRequest $request, $slug)
    {
        // Validate the incoming request using ProductRequest
        
        $product = Product::where('slug', 'LIKE', $slug)->firstOrFail();
        
        $this->authorize('update', $product);

        // Update product attributes
        $product->name = $request->input('name');
        $product->description = $request->input('description');
        $product->price = $request->input('price');
        $product->stock = $request->input('stock');
        $product->category_id = $request->input('category_id');
        $product->slug = Str::slug($request->input('name'));

        // Save the updated product
        $product->save();

        // Handle variations update if provided
        if ($request->has('variations')) {
            foreach ($request->input('variations') as $variationData) {
                if (isset($variationData['id'])) {
                    // Update existing variation
                    $variation = ProductVariation::findOrFail($variationData['id']);
                    $variation->update([
                        'type' => $variationData['type'],
                        'value' => $variationData['value'],
                        'price' => $variationData['price'],
                    ]);
                } else {
                    // Create new variation
                    $variation = new ProductVariation();
                    $variation->product_id = $product->id;
                    $variation->type = $variationData['type'];
                    $variation->value = $variationData['value'];
                    $variation->price = $variationData['price'];
                    $variation->save();
                }
            }
        }

        // Handle images update if provided
        if ($request->has('images')) {
            foreach ($request->input('images') as $imageData) {
                if (isset($imageData['id'])) {
                    // Update existing image
                    $image = ProductImage::findOrFail($imageData['id']);
                    $image->update([
                        'url' => $imageData['url'],
                        'is_primary' => $imageData['is_primary'] ?? false,
                    ]);
                } else {
                    // Create new image
                    $image = new ProductImage();
                    $image->product_id = $product->id;
                    $image->url = $imageData['url'];
                    $image->is_primary = $imageData['is_primary'] ?? false;
                    $image->save();
                }
            }
        }

        // Handle tags assignment if provided
        if ($request->has('tags')) {
            $product->tags()->sync($request->input('tags'));
        }

        Cache::flush();

        // Return a JSON response with the updated product
        return response()->json($product, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        // Delete associated variations if any
        if ($product->variations()->exists()) {
            $product->variations()->delete();
        }

        // Delete associated images if any
        if ($product->images()->exists()) {
            $product->images()->delete();
        }

        // Detach associated tags if any
        $product->tags()->detach();

        // Delete the product itself
        $product->delete();

        Cache::flush();

        // Return a JSON response indicating success
        return response()->json(['message' => 'Product and associated data deleted successfully'], 200);
    }
}
