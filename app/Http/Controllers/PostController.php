<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $page = $request->input('page', 1);
        $size = $request->input('size', 10);

        $posts = Post::with('user', 'postAttachments')->paginate($size, ['*'], 'page', $page);

        return response()->json([
            "page" => $posts->currentPage() - 1,
            "size" => $size,
            "posts" => $posts
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Start Validasi inputan
        $rules = [
            'caption' => 'required',
            'attachments.*' => 'required|image|mimes:jpg,jpeg,webp,png,gif'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => "Invalid field",
                "errors" => $validator->errors(),
            ], 401);
        }
        // End Validasi inputan

        // Start Insert table posts
        $userId = Auth::id();

        $post = Post::create([
            'caption' => $request->caption,
            'user_id' => $userId
        ]);
        // Start Insret table posts

        // Jalankan perintah ini ketika data berhasil di masukkan kedalam table post
        // untuk insert ke dalam table post_attachments
        if ($post) {
            if ($request->has('attachments')) {
                $images = $request->file('attachments');

                foreach ($images as $image) {
                    $extension = $image->extension();
                    $name = rand() . '.' . $extension;

                    $path = public_path('posts');

                    $image->move($path, $name);

                    PostAttachment::create([
                        'storage_path' => 'posts/' . $name,
                        'post_id' => $post->id
                    ]);
                }
            } else {
                return response()->json([
                    'message' => "Invalid field",
                    "errors" => $validator->errors(),
                ], 401);
            }
        } else {
            return response()->json([
                'message' => "Invalid field",
                "errors" => $validator->errors(),
            ], 401);
        }

        return response()->json([
            'message' => "Create post success",
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {

        $post = Post::withTrashed()->findOrFail($id);

        if ($post->trashed()) {
            return response()->json([
                'message' => "Post not found",
            ], 404);
        }

        if ($post->user_id !== auth()->user()->id) {
            return response()->json([
                'message' => "Forbidden access",
            ], 403);
        }

        $post->delete();

        return response()->json([
            'message' => "Post deleted successfully",
        ], 204);
    }
}
