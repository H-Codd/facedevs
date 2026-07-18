<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller as BaseController;

class PostController extends BaseController
{
    private $loggerUser;
    public function __construct()
    {
        $this->middleware('auth:api');

        $this->loggerUser = Auth::user();
    }
}
