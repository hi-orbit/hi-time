<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TagController extends Controller
{
    /**
     * List tags.
     *
     * Filters: customer_id, name (partial match).
     * Customer users only see tags for customers they are assigned to.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'name' => 'nullable|string|max:255',
        ]);

        /** @var User $user */
        $user = Auth::user();

        $query = Tag::query();

        if ($user->isCustomer()) {
            $query->whereIn('customer_id', $user->accessibleCustomerIds());
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $validated['customer_id']);
        }

        if ($request->filled('name')) {
            $query->where('name', 'like', '%'.$validated['name'].'%');
        }

        $tags = $query->orderBy('name')->get();

        return response()->json(['data' => $tags->map(fn (Tag $tag) => $this->tagPayload($tag))->values()]);
    }

    /**
     * Create a tag.
     *
     * Customer users may only create tags for customers they are assigned to.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string',
            'customer_id' => 'nullable|exists:customers,id',
        ], [
            'color.regex' => 'The color must be a hexadecimal color code (e.g. #3B82F6).',
        ]);

        /** @var User $user */
        $user = Auth::user();

        $customerId = $validated['customer_id'] ?? null;

        if ($user->isCustomer() && ! $user->accessibleCustomerIds()->contains($customerId)) {
            abort(403, 'You do not have access to this customer.');
        }

        // Names are unique per customer scope (mirrors the web tag selector).
        $duplicate = Tag::where('name', $validated['name'])
            ->when(
                $customerId !== null,
                fn ($q) => $q->where('customer_id', $customerId),
                fn ($q) => $q->whereNull('customer_id')
            )
            ->exists();

        if ($duplicate) {
            $message = $customerId !== null
                ? 'A tag with this name already exists for this customer.'
                : 'A tag with this name already exists.';

            return response()->json([
                'message' => $message,
                'errors' => ['name' => [$message]],
            ], 422);
        }

        $tag = Tag::create([
            'name' => $validated['name'],
            'color' => $validated['color'] ?? '#3B82F6',
            'description' => $validated['description'] ?? null,
            'customer_id' => $customerId,
        ]);

        return response()->json(['data' => $this->tagPayload($tag)], 201);
    }

    /**
     * Build the JSON payload for a tag.
     */
    private function tagPayload(Tag $tag): array
    {
        return [
            'id' => $tag->id,
            'name' => $tag->name,
            'color' => $tag->color,
            'description' => $tag->description,
            'customer' => $tag->customer ? ['id' => $tag->customer->id, 'name' => $tag->customer->name] : null,
            'created_at' => $tag->created_at?->toIso8601String(),
            'updated_at' => $tag->updated_at?->toIso8601String(),
        ];
    }
}
