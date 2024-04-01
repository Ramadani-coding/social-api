<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function getUsers()
    {
        $user = Auth::user();

        $usersNotFollow = User::whereDoesntHave('followers', function ($query) use ($user) {
            $query->where('follower_id', $user->id)->where('is_acccepted', true);
        })
            ->where('id', '!=', $user->id)
            ->get();

        return response()->json([
            "users" => $usersNotFollow
        ], 200);
    }

    public function getDetailUser(Request $request, $username)
    {
        $user = Auth::user();

        $detailUser = User::where('username', $username)->first();


        if (!$detailUser) {
            return response()->json([
                "message" => "User not found"
            ], 404);
        }

        $postCount = Post::where('user_id', $detailUser->id)->count();
        $followersCount = Follow::where('following_id', $detailUser->id)->where('is_acccepted', true)->count();
        $followingCount = Follow::where('follower_id', $detailUser->id)->where('is_acccepted', true)->count();

        $isYourAccount = false;
        if ($user && $user->id === $detailUser->id) {
            $isYourAccount = true;
        }

        if ($detailUser->is_private) {
            $posts = "This account is private";
        } else {
            $posts = Post::with('postAttachments')->where('user_id', $detailUser->id)->get();
        }

        if ($postCount < 1) {
            $posts = "No posts yet";
        }

        if ($postCount < 1 && $isYourAccount === true) {
            $posts = "Capture moments with friends";
        }

        $followingStatus = 'not-following';

        if ($user) {
            $following = Follow::where('follower_id', $user->id)
                ->where('following_id', $detailUser->id)
                ->first();

            if ($following) {
                if ($following->is_acccepted) {
                    $followingStatus = 'following';
                } else {
                    $followingStatus = 'requested';
                }
            }
        }


        return response()->json([
            "id" => $detailUser->id,
            "full_name" => $detailUser->full_name,
            "username" => $detailUser->username,
            "bio" => $detailUser->bio,
            "is_private" => $user->is_private,
            "is_your_account" => $isYourAccount,
            "posts_count" => $postCount,
            "followers_count" => $followersCount,
            "following_count" => $followingCount,
            "following_status" => $followingStatus,
            "posts" => $posts,
        ], 200);
    }
}
