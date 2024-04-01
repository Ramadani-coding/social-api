<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    public function getFollowers(Request $request, $username)
    {
        $user = Auth::user();

        $follower = User::where('username', $username)->first();

        $followers = Follow::where('following_id', $follower->id)
            ->where('is_acccepted', true)
            ->with('follower') // Ambil data pengguna pengikut
            ->get()
            ->pluck('follower');

        return response()->json([
            "followers" => $followers
        ], 200);
    }

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

        if ($userToFollow->is_private === 0) {
            $isAcccepted = true;
        } else {
            $isAcccepted = false;
        }

        if ($isAcccepted) {
            $status = "following";
        } else {
            $status = "requested";
        }

        Follow::create([
            'follower_id' => $user->id,
            'following_id' => $userToFollow->id,
            'is_acccepted' => $isAcccepted
        ]);

        return response()->json([
            "message" => "Follow success",
            "status" => $status
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

    public function acceptFollow(Request $request, $username)
    {
        $user = Auth::user();

        $userToAccept = User::where('username', $username)->first();

        if (!$userToAccept) {
            return response()->json([
                "message" => "User not found"
            ], 404);
        }

        $isFollowing = Follow::where('follower_id', $userToAccept->id)
            ->where('following_id', $user->id)
            ->first();

        if (!$isFollowing) {
            return response()->json([
                "message" => "The user is not following you"
            ], 422);
        }

        if ($isFollowing->is_acccepted) {
            return response()->json([
                "message" => "Follow request is already accepted"
            ], 422);
        }

        $isFollowing->update([
            'is_acccepted' => true
        ]);

        return response()->json([
            "message" => "Follow request accepted"
        ], 200);
    }
}
