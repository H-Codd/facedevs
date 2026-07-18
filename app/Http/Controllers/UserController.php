<?php

namespace App\Http\Controllers;

use App\Models\User;
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

}
