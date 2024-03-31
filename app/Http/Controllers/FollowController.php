<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    public function getFollowing()
    {
        $user = Auth::user();

        $following = Follow::where('follower_id', $user->id)
            ->with('following')
            ->get()->pluck('following');

        if ($following->isEmpty()) {
            return response()->json([
                "message" => "User not found"
            ], 200);
        }

        return response()->json([
            "following" => $following
        ], 200);
    }

    public function follow(Request $request, $username)
    {
        $user = Auth::user();

        $userToFollow = User::where('username', $username)->first();

        if (!$userToFollow) {
            return response()->json([
                "message" => "User not found"
            ], 404);
        }

        if ($user->id === $userToFollow->id) {
            return response()->json([
                "message" => "You are not allowed to follow yourself"
            ], 422);
        }

        $isFollowing = Follow::where('follower_id', $user->id)
            ->where('following_id', $userToFollow->id)
            ->exists();

        if ($isFollowing) {
            return response()->json([
                "message" => "You are already followed"
            ], 422);
        }

        Follow::create([
            'follower_id' => $user->id,
            'following_id' => $userToFollow->id,
            'is_acccepted' => false
        ]);

        return response()->json([
            "message" => "Follow success",
            "status" => "following | requested"
        ], 200);
    }

    public function unfollow(Request $request, $username)
    {
        $user = Auth::user();

        $userToUnfollow = User::where('username', $username)->first();

        if (!$userToUnfollow) {
            return response()->json([
                "message" => "User not found"
            ], 404);
        }

        $isFollowing = Follow::where('follower_id', $user->id)
            ->where('following_id', $userToUnfollow->id)
            ->first();

        if (!$isFollowing) {
            return response()->json([
                "message" => "You are not following the user"
            ], 422);
        }

        $isFollowing->delete();

        return response()->json([
            "message" => "unfollow deleted successfully"
        ], 204);
    }
}
