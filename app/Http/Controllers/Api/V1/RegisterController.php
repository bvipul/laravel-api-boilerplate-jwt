<?php

namespace App\Http\Controllers\Api\V1;

use Config;
use JWTAuth;
use Validator;
use App\Models\User\User;
use Illuminate\Http\Request;
use App\Repositories\UserRepository;

class RegisterController extends APIController
{
    protected $repository;

    /**
     * __construct.
     *
     * @param $repository
     */
    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Register User.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name'                  => 'required',
            'email'                 => 'required|email|unique:users',
            'password'              => 'required|min:6',
            'password_confirmation' => 'required|same:password',
        ]);

        if ($validation->fails()) {
            return $this->throwValidation($validation->messages()->first());
        }

        $user = $this->repository->create($request->all());

        if (!Config::get('boilerplate.register.release_token')) {
            return $this->respondCreated([
                'message'  => trans('api.messages.registeration.success'),
            ]);
        }

        $token = JWTAuth::fromUser($user);

        return $this->respondCreated([
            'message'   => trans('api.messages.registeration.success'),
            'token'     => $token,
        ]);
    }
}
