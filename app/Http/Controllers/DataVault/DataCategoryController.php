<?php

namespace App\Http\Controllers\DataVault;

use App\Http\Controllers\Controller;
use App\Models\DataCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Data Categories",
 *     description="API Endpoints for managing data categories"
 * )
 */
class DataCategoryController extends Controller
{
    /**
     * Display a listing of data categories
     * 
     * @OA\Get(
     *     path="/api/data-categories",
     *     summary="Get all data categories for authenticated user",
     *     tags={"Data Categories"},
     *     security={{"jwt":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Categories retrieved successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="color", type="string"),
     *                 @OA\Property(property="icon", type="string"),
     *                 @OA\Property(property="personal_data_count", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $categories = DataCategory::where('user_id', $request->user()->id)
            ->withCount('personalData')
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    /**
     * Store a newly created data category
     * 
     * @OA\Post(
     *     path="/api/data-categories",
     *     summary="Create new data category",
     *     tags={"Data Categories"},
     *     security={{"jwt":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Passwords", description="Category name"),
     *             @OA\Property(property="description", type="string", example="Website and application passwords", description="Category description"),
     *             @OA\Property(property="color", type="string", example="#EF4444", description="Category color (hex)"),
     *             @OA\Property(property="icon", type="string", example="lock", description="Category icon")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Category created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
        ]);

        $category = DataCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color ?? '#3B82F6',
            'icon' => $request->icon ?? 'folder',
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Category created successfully',
            'data' => $category,
        ], 201);
    }

    /**
     * Display the specified data category
     * 
     * @OA\Get(
     *     path="/api/data-categories/{id}",
     *     summary="Get specific data category by ID",
     *     tags={"Data Categories"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Category ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="color", type="string"),
     *             @OA\Property(property="icon", type="string"),
     *             @OA\Property(property="personal_data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     )
     * )
     */
    public function show(Request $request, DataCategory $dataCategory): JsonResponse
    {
        // Ensure user owns this category
        if ($dataCategory->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $dataCategory->load(['personalData' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }]);

        return response()->json($dataCategory);
    }

    /**
     * Update the specified data category
     * 
     * @OA\Put(
     *     path="/api/data-categories/{id}",
     *     summary="Update data category",
     *     tags={"Data Categories"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Category ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Passwords"),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="color", type="string", example="#10B981"),
     *             @OA\Property(property="icon", type="string", example="shield")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Category updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, DataCategory $dataCategory): JsonResponse
    {
        // Ensure user owns this category
        if ($dataCategory->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
        ]);

        $dataCategory->update($request->only(['name', 'description', 'color', 'icon']));

        return response()->json([
            'message' => 'Category updated successfully',
            'data' => $dataCategory,
        ]);
    }

    /**
     * Remove the specified data category
     * 
     * @OA\Delete(
     *     path="/api/data-categories/{id}",
     *     summary="Delete data category",
     *     tags={"Data Categories"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Category ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Category deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot delete category with associated data"
     *     )
     * )
     */
    public function destroy(Request $request, DataCategory $dataCategory): JsonResponse
    {
        // Ensure user owns this category
        if ($dataCategory->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if category has associated data
        if ($dataCategory->personalData()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with associated data. Please move or delete the data first.',
            ], 422);
        }

        $dataCategory->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }
} 