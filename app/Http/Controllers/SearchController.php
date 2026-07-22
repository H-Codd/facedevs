<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SearchController extends Controller implements HasMiddleware
{
    /**
     * Define os middlewares do controller.
     */
    public static function middleware(): array
    {
        return [
            'auth:api',
        ];
    }

    public function search(Request $request) {
        $array = ['error' => '', 'users' => ''];
        $txt= $request->input('txt');

        if($txt){
            $userList = User::where('name', 'like', '%'.$txt.'%')->get();
            foreach($userList as $userItem){
                $array['users'] = [
                    'id' => $userItem['id'],
                    'name' => $userItem['name'],
                    'avatar' => url('media/avatars/'.$userItem['avatar'])
                ];
            }
        } else{
            return['error'=> 'Digite Algo'];
        }

        return $array;
    }
}