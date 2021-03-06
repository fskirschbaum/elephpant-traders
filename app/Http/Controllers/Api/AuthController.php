<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Controllers\Controller;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * @var Response
     */
    private $response;

    /**
     * @var UserRepository
     */
    private $repository;

    public function __construct(Response $response, UserRepository $repository)
    {
        $this->response = $response;
        $this->repository = $repository;
    }

    public function register(RegisterRequest $request)
    {
        $this->repository->create($request);

        return $this->response->setStatusCode(201)->setContent('User created');
    }

    public function create(LoginRequest $request)
    {
        try {
            if (Auth::attempt(['email' => $request->getEmail(), 'password' => $request->getPassword()])) {
                Auth::login(User::findByEmail($request->getEmail())->first(), true);
                $token = JWTAuth::attempt($request->only('email', 'password'), [
                    'exp' => Carbon::now()->addWeek()->timestamp,
                ]);
            }

        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Could not authenticate',
            ], 500);
        }
        if (!$token) {
            return response()->json([
                'error' => 'Could not authenticate',
            ], 401);
        } else {
            $data = [];
            $meta = [];
            $data['user'] = $request->user()->email();
            $meta['token'] = $token;
            return response()->json([
                'data' => $data,
                'meta' => $meta
            ]);
        }
    }

    public function destroy()
    {

    }
}
