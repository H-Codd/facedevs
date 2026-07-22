<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\UserRelation;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class UserController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:api');

    }

    public function update(Request $request)
    {
        $user = User::find(Auth::id());

        $validated = $request->validate([
            'name'      => 'nullable|string|max:255',
            'email'     => 'nullable|email|unique:users,email,' . $user->id,
            'password'  => 'nullable|confirmed',
            'birthdate' => 'nullable|date',
            'city'      => 'nullable|string|max:255',
            'work'      => 'nullable|string|max:255',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->fill($validated);
        $user->save();

        return response()->json([
            'message' => 'Usuário atualizado com sucesso',
            'user'    => $user
        ]);
    }

    public function updateAvatar(Request $request) {
        $user = User::find(Auth::id());
        $allowedTypes = ['image/jpg', 'image/jpeg', 'image/png' ];

        $image = $request->file('avatar');

        if($image){
            if(in_array($image->getClientMimeType(), $allowedTypes)){

                $filename = md5(time().rand(0, 999)).'.jpg';

                $destPath = public_path("/media/avatars");

                $manager = ImageManager::usingDriver(new Driver);
                $img = $manager->decode($image->getRealPath())->resize(200, 200)->save($destPath.'/'.$filename);

                $user->avatar = $filename;
                $user->save();

                return['url'=> url('/media/avatars'.$filename)] ; 

            }else{
                return['error' => 'Arquivo não encontrado!'];
            }
        } else {
            return ['error' => 'Arquivo não enviado!'];
        }
    }

    public function updateCover(Request $request) {
        $user = User::find(Auth::id());
        $allowedTypes = ['image/jpg', 'image/jpeg', 'image/png' ];

        $image = $request->file('cover');

        if($image){
            if(in_array($image->getClientMimeType(), $allowedTypes)){

                $filename = md5(time().rand(0, 999)).'.jpg';

                $destPath = public_path("/media/covers");

                $manager = ImageManager::usingDriver(new Driver);
                $img = $manager->decode($image->getRealPath())->resize(810, 310)->save($destPath.'/'.$filename);

                $user->cover = $filename;
                $user->save();

                return['url'=> url('/media/covers'.$filename)] ; 

            }else{
                return['error' => 'Arquivo não encontrado!'];
            }
        } else {
            return ['error' => 'Arquivo não enviado!'];
        }
    }

    public function read($id = false) {
        $user = User::find(Auth::id());
        if($id){
            $info = User::find($id);
            if(!$info) {
                return['error' => 'Usuário inexistente!'];
            }

        }else{
            $info = $user;
            return ['data' => $info];
        }

        $info['avatar'] = url('media/avatars/'.$info['avatar']);
        $info['cover'] = url('media/covers/'.$info['cover']);

        $info['me'] = ($info['id'] == $user['id']) ?  true : false;

        $dateFrom = new \DateTime($info['birthdate']);
        $dateTo = new \DateTime('today');
        $info['age'] = $dateFrom->diff($dateTo)->y;

        $info['followers'] = UserRelation::where('user_to', $info['id'])->count();
        $info['following'] = UserRelation::where('user_from', $info['id'])->count();
       
        $info['photoCount'] = Post::where('id_user', $info['id'])->where('type', 'photo')->count();

        $hasRelation = UserRelation::where('user_from', $user['id'])->where('user_to', $info['id'])->count();
        $info['isFollowing'] = ($hasRelation > 0) ? true : false;
        return ['data' => $info];
    }

    public function follow(int $id) {
        $user = User::find(Auth::id());
        if($id == $user['id']){
            return['error' => 'Você nãp pode seguir a si mesmo'];
        }
        $userExists = User::find($id);
        if($userExists) {
            $relation = UserRelation::where('user_from', $user['id'])->where('user_to', $id)->first();

            if($relation) {
                $relation->delete;
            } else {
                $newRelation = new UserRelation();
                $newRelation->user_from = $user['id'];
                $newRelation->user_to = $id;
                $newRelation->save();
            }
        }
        return ['error'=>''];
    }

    public function followers($id) {
        $userExists = User::find($id);
        if($userExists) {
            $followers = UserRelation::where('user_to', $id)->get();
            $following = UserRelation::where('user_from', $id)->get();

            $array['followers'] = [];
            $array['following'] = [];

            foreach($followers as $item) {
                $user = User::find($item['user_from']);
                $array['followers'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'avatar' => url('media/avatars/'.$user['avatar']),
                ];
            } 
            foreach($following as $item) {
                $user = User::find($item['user_from']);
                $array['following'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'avatar' => url('media/avatars/'.$user['avatar']),
                ];
            } 
            return $array;

        } else {
            return['error'=> 'Usuário inexistente!'] ;
        }
        return['error' => ''];
    }
}
