<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Models\User;
use App\Models\UserRelation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller as BaseController;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class FeedController extends BaseController
{
        private $loggerUser;
        public function __construct()
        {
            $this->middleware('auth:api');

            $this->loggerUser = Auth::user();
        }

        public function create(Request $request) {
        $user = User::find(Auth::id());

        $validated = $request->validate([
            'type'  => 'required|string|max:255',
            'body'  => 'nullable|string|max:255',
            'photo' => 'nullable|file|image|max:2048', // validação de foto
        ]);

        switch ($validated['type']) {
            case 'text':
                if (empty($validated['body'])) {
                    return ['error' => 'Texto não enviado'];
                }
                break;

            case 'photo':
                if (!$request->hasFile('photo')) {
                    return ['error' => 'Foto não enviada'];
                }
                break;

            default:
                return ['error' => 'Tipo de postagem inexistente'];
        }

        $newPost = new Post();
        $newPost->id_user = $user->id; // salva apenas o ID
        $newPost->type = $validated['type'];
        $newPost->created_at = now();

        if (!empty($validated['body'])) {
            $newPost->body = $validated['body'];
        }

        if ($request->hasFile('photo')) {
            $filename = md5(time().rand(0, 999)).'.jpg';

            $destPath = public_path("/media/uploads");

            $manager = ImageManager::usingDriver(new Driver());
            $img = $manager->decode($validated['photo']->getRealPath())->save($destPath.'/'.$filename);

            $newPost->photo = $filename;

        }

        $newPost->save();

        return response()->json([
            'message' => 'Post criado com sucesso',
            'post'    => $newPost
        ]);
    }
    public function read(Request $request){
        $userId = Auth::id();
        $page = intval($request->input('page', 0));
        $perPage = 2;

        // IDs dos usuários seguidos
        $userList = UserRelation::where('user_from', $userId)->pluck('user_to')->toArray();
        $users = array_merge([$userId], $userList); // inclui o próprio usuário

        // Posts
        $postList = Post::whereIn('id_user', $users)
            ->orderBy('created_at', 'desc')
            ->offset($page * $perPage)
            ->limit($perPage)
            ->get();

        $total = Post::whereIn('id_user', $users)->count();
        $pageCount = ceil($total / $perPage);

        $posts = $this->_postListToObject($postList, $userId);

        return [
            'posts' => $posts,
            'pageCount' => $pageCount,
            'currentPage' => $page
        ];
    }


    private function _postListToObject(mixed $postList, int $loggedUser) {
        foreach($postList as $postKey => $postItem){
            $postList[$postKey]['mine'] = ($postItem['id_user'] == $loggedUser);

            $userInfo = User::find($postItem['id_user']);
            $userInfo['avatar'] = url('media/avatars/'.$userInfo['avatar']);
            $userInfo['cover'] = url('media/covers/'.$userInfo['cover']);
            $postList[$postKey]['user'] = $userInfo;

            $likes = PostLike::where('id_post', $postItem['id'])->count();
            $postLikes[$postKey]['likeCount'] = $likes;

            $isLiked = PostLike::where('id_post', $postItem['id'])->where('id_user', $loggedUser)->count();

            $postList[$postKey]['liked'] = ($isLiked > 0) ? true : false;

            $comments = PostComment::where('id_post', $postItem['id'])->get();
            foreach($comments as $commentKey => $comment){
                $user = User::find($comment['id_user']);
                $user['avatar'] = url('media/avatars' . $userInfo['avatar']);
                $user['cover'] = url('media/covers' . $userInfo['covers']);
                $comments[$commentKey]['user'] = $user;
            }
            $postList[$postKey]['comments'] = $comments;


        }

        return $postList;
    }

}
