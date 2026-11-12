<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => Category::orderBy('nama')->get(['id', 'nama'])]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['nama' => 'required|string|max:50|unique:categories,nama']);

        return response()->json(['data' => Category::create($data)], 201);
    }

    public function destroy(Category $category): JsonResponse
    {
        abort_if($category->events()->exists(), 409, 'Kategori masih dipakai oleh event.');
        $category->delete();

        return response()->json(['message' => 'Kategori dihapus.']);
    }
}
