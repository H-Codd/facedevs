<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller as BaseController;

class PostController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function like(int $id) {
        $user = User::find(Auth::id());
        $postExists = Post::find($id);

        if($postExists){
            $isLiked = PostLike::where('id_post', $id)->where('id_user',$user['id'])->count();
            if($isLiked > 0){
                $pl = PostLike::where('id_post', $id)->where('id_user', $user['id'])->first();
                $pl->delete();
                $isLiked = false;
            } else{
                $newPostLike = new PostLike();
                $newPostLike->id_post = $id;
                $newPostLike->id_user = $user['id'];
                $newPostLike->created_at = date('Y-m-d H:i:s');
                $newPostLike->save();

                $isLiked = true;
            }
            $likeCount = PostLike::where('id_post', $id)->count();
            return ['likedCount' => $likeCount];
        }else{
            return['error' => 'Post não existe'];
        }

    }
    public function comment(Request $request, $id) {
        $user = User::find(Auth::id());

        $txt = $request->input('txt');

        $postExists = Post::find($id);
        if($postExists){
            if($txt){
                $newComment = new PostComment();
                $newComment->id_post = $id;
                $newComment->id_user = $user['id'];
                $newComment->created_at = date('Y-m-d H:i:s');
                $newComment->body = $txt;
                $newComment->save();
                return['newPost' => $newComment];
            } else {
                return['erro' => 'Não enviou uma mensagem'];
            }
        }
        return['error'=>''];
    }
}
