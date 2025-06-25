<?php

namespace App\Http\Controllers\DataVault;

use App\Http\Controllers\Controller;
use App\Models\PersonalData;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Crypt;

/**
 * @OA\Tag(
 *     name="Personal Data",
 *     description="API Endpoints for managing personal data"
 * )
 */
class PersonalDataController extends Controller
{
    /**
     * Display a listing of personal data
     * 
     * @OA\Get(
     *     path="/api/personal-data",
     *     summary="Get all personal data for authenticated user",
     *     tags={"Personal Data"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filter by category ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="favorites",
     *         in="query",
     *         description="Filter by favorites only",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search in title, description, or tags",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Personal data retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="total", type="integer")
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
        $query = PersonalData::where('user_id', $request->user()->id)
            ->with('category');

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by favorites
        if ($request->boolean('favorites')) {
            $query->favorites();
        }

        // Search functionality
        if ($request->has('search')) {
            $query->search($request->search);
        }

        $personalData = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($personalData);
    }

    /**
     * Store a newly created personal data
     * 
     * @OA\Post(
     *     path="/api/personal-data",
     *     summary="Create new personal data",
     *     tags={"Personal Data"},
     *     security={{"jwt":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","data_type","data"},
     *             @OA\Property(property="title", type="string", example="Gmail Password", description="Title of the data"),
     *             @OA\Property(property="description", type="string", example="My Gmail account password", description="Optional description"),
     *             @OA\Property(property="data_type", type="string", enum={"password","note","card","account","other"}, example="password", description="Type of data"),
     *             @OA\Property(property="data", type="string", example="mySecurePassword123!", description="The actual data (will be encrypted)"),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="string"), example={"email","google"}, description="Tags for categorization"),
     *             @OA\Property(property="is_favorite", type="boolean", example=true, description="Mark as favorite"),
     *             @OA\Property(property="category_id", type="integer", example=1, description="Category ID")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Personal data created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Personal data created successfully"),
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'data_type' => 'required|string|in:password,note,card,account,other',
            'data' => 'required|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'is_favorite' => 'boolean',
            'category_id' => 'nullable|exists:data_categories,id',
        ]);

        $personalData = PersonalData::create([
            'title' => $request->title,
            'description' => $request->description,
            'data_type' => $request->data_type,
            'encrypted_data' => Crypt::encryptString($request->data),
            'tags' => $request->tags ?? [],
            'is_favorite' => $request->boolean('is_favorite', false),
            'category_id' => $request->category_id,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Personal data created successfully',
            'data' => $personalData->load('category'),
        ], 201);
    }

    /**
     * Display the specified personal data
     * 
     * @OA\Get(
     *     path="/api/personal-data/{id}",
     *     summary="Get specific personal data by ID",
     *     tags={"Personal Data"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Personal data ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Personal data retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="data_type", type="string"),
     *             @OA\Property(property="decrypted_data", type="string"),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="is_favorite", type="boolean"),
     *             @OA\Property(property="category", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Personal data not found"
     *     )
     * )
     */
    public function show(Request $request, PersonalData $personalData): JsonResponse
    {
        // Ensure user owns this data
        if ($personalData->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $personalData->load('category');
        
        // Decrypt the data for display
        $personalData->decrypted_data = Crypt::decryptString($personalData->encrypted_data);

        return response()->json($personalData);
    }

    /**
     * Update the specified personal data
     * 
     * @OA\Put(
     *     path="/api/personal-data/{id}",
     *     summary="Update personal data",
     *     tags={"Personal Data"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Personal data ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Gmail Password"),
     *             @OA\Property(property="description", type="string", example="Updated description"),
     *             @OA\Property(property="data_type", type="string", enum={"password","note","card","account","other"}),
     *             @OA\Property(property="data", type="string", example="newSecurePassword456!"),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="is_favorite", type="boolean"),
     *             @OA\Property(property="category_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Personal data updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Personal data updated successfully"),
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
    public function update(Request $request, PersonalData $personalData): JsonResponse
    {
        // Ensure user owns this data
        if ($personalData->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'data_type' => 'sometimes|required|string|in:password,note,card,account,other',
            'data' => 'sometimes|required|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'is_favorite' => 'boolean',
            'category_id' => 'nullable|exists:data_categories,id',
        ]);

        $updateData = $request->only(['title', 'description', 'data_type', 'tags', 'is_favorite', 'category_id']);
        
        if ($request->has('data')) {
            $updateData['encrypted_data'] = Crypt::encryptString($request->data);
        }

        $personalData->update($updateData);

        return response()->json([
            'message' => 'Personal data updated successfully',
            'data' => $personalData->load('category'),
        ]);
    }

    /**
     * Remove the specified personal data
     * 
     * @OA\Delete(
     *     path="/api/personal-data/{id}",
     *     summary="Delete personal data",
     *     tags={"Personal Data"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Personal data ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Personal data deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Personal data deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function destroy(Request $request, PersonalData $personalData): JsonResponse
    {
        // Ensure user owns this data
        if ($personalData->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $personalData->delete();

        return response()->json([
            'message' => 'Personal data deleted successfully',
        ]);
    }

    /**
     * Search personal data
     * 
     * @OA\Get(
     *     path="/api/personal-data/search/{query}",
     *     summary="Search personal data",
     *     tags={"Personal Data"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="query",
     *         in="path",
     *         description="Search query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search results",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="total", type="integer")
     *         )
     *     )
     * )
     */
    public function search(Request $request, string $query): JsonResponse
    {
        $personalData = PersonalData::where('user_id', $request->user()->id)
            ->search($query)
            ->with('category')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($personalData);
    }

    /**
     * Get personal data by category
     * 
     * @OA\Get(
     *     path="/api/personal-data/category/{category}",
     *     summary="Get personal data by category name",
     *     tags={"Personal Data"},
     *     security={{"jwt":{}}},
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         description="Category name",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Personal data by category",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="total", type="integer")
     *         )
     *     )
     * )
     */
    public function getByCategory(Request $request, string $category): JsonResponse
    {
        $personalData = PersonalData::where('user_id', $request->user()->id)
            ->whereHas('category', function ($query) use ($category) {
                $query->where('name', 'like', "%{$category}%");
            })
            ->with('category')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($personalData);
    }

    /**
     * Export personal data
     * 
     * @OA\Get(
     *     path="/api/personal-data/export",
     *     summary="Export all personal data",
     *     tags={"Personal Data"},
     *     security={{"jwt":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Personal data exported successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="exported_at", type="string", format="date-time")
     *         )
     *     )
     * )
     */
    public function export(Request $request): JsonResponse
    {
        $personalData = PersonalData::where('user_id', $request->user()->id)
            ->with('category')
            ->get()
            ->map(function ($item) {
                return [
                    'title' => $item->title,
                    'description' => $item->description,
                    'data_type' => $item->data_type,
                    'data' => Crypt::decryptString($item->encrypted_data),
                    'tags' => $item->tags,
                    'is_favorite' => $item->is_favorite,
                    'category' => $item->category?->name,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            });

        return response()->json([
            'data' => $personalData,
            'exported_at' => now(),
        ]);
    }

    /**
     * Import personal data
     * 
     * @OA\Post(
     *     path="/api/personal-data/import",
     *     summary="Import personal data",
     *     tags={"Personal Data"},
     *     security={{"jwt":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"data"},
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="data_type", type="string"),
     *                     @OA\Property(property="data", type="string"),
     *                     @OA\Property(property="description", type="string"),
     *                     @OA\Property(property="tags", type="array", @OA\Items(type="string")),
     *                     @OA\Property(property="is_favorite", type="boolean")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Import completed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="imported_count", type="integer"),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     )
     * )
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'data' => 'required|array',
            'data.*.title' => 'required|string|max:255',
            'data.*.data_type' => 'required|string|in:password,note,card,account,other',
            'data.*.data' => 'required|string',
        ]);

        $imported = 0;
        $errors = [];

        foreach ($request->data as $index => $item) {
            try {
                PersonalData::create([
                    'title' => $item['title'],
                    'description' => $item['description'] ?? null,
                    'data_type' => $item['data_type'],
                    'encrypted_data' => Crypt::encryptString($item['data']),
                    'tags' => $item['tags'] ?? [],
                    'is_favorite' => $item['is_favorite'] ?? false,
                    'category_id' => $item['category_id'] ?? null,
                    'user_id' => $request->user()->id,
                ]);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$index}: " . $e->getMessage();
            }
        }

        return response()->json([
            'message' => "Imported {$imported} items successfully",
            'imported_count' => $imported,
            'errors' => $errors,
        ]);
    }
} 