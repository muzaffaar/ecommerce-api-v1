<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::all();
        return response()->json($tags);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:tags,name|max:255',
        ]);

        $tag = Tag::create($request->only('name'));

        return response()->json($tag, 201);
    }

    public function show(Tag $tag)
    {
        return response()->json($tag);
    }

    public function update(Request $request, Tag $tag)
    {
        $request->validate([
            'name' => 'required|unique:tags,name,' . $tag->id . '|max:255',
        ]);

        $tag->update($request->only('name'));

        return response()->json($tag);
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();
        return response()->json(null, 204);
    }

    public function attachTagToProduct(Request $request, Product $product)
    {
        $request->validate([
            'tag_id' => 'required|exists:tags,id',
        ]);

        $product->tags()->syncWithoutDetaching($request->tag_id);

        return response()->json(['message' => 'Tag attached to product successfully'], 200);
    }

    public function detachTagFromProduct(Request $request, Product $product)
    {
        $request->validate([
            'tag_id' => 'required|exists:tags,id',
        ]);

        $product->tags()->detach($request->tag_id);

        return response()->json(['message' => 'Tag detached from product successfully'], 200);
    }
}
