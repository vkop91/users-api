<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\User\StoreUser;
use App\Http\Requests\API\User\UpdateUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\UserCollection;
use App\Http\Services\UserService;

class UserController extends Controller
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * UserController constructor.
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return UserCollection
     */
    public function index()
    {
        return new UserCollection($this->userService->getUsers());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreUser $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreUser $request)
    {
        $validatedRequestData = $request->validated();
        $this->userService->createUser($validatedRequestData);

        return response()->json([
            'message' => __('api.user.add')
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return $this->userNotFoundResponse();
        }

        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateUser $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUser $request, int $id)
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return $this->userNotFoundResponse();
        }

        $validatedRequestData = $request->validated();
        $this->userService->updateUser($validatedRequestData);

        return response()->json([
            'message' => __('api.user.update')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return $this->userNotFoundResponse();
        }

        $this->userService->deleteUser();

        return response()->json([
            'message' => __('api.user.delete')
        ]);
    }

    /**
     * Not founded resource
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function userNotFoundResponse()
    {
        return response()->json([
            'message' => __('api.user.not_founded')
        ], 404);
    }
}
