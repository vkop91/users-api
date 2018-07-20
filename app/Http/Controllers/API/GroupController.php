<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Group\StoreGroup;
use App\Http\Requests\API\Group\UpdateGroup;
use App\Http\Resources\Group as GroupResource;
use App\Http\Resources\GroupCollection;
use App\Http\Services\GroupService;

class GroupController extends Controller
{
    /**
     * @var GroupService
     */
    protected $groupService;

    /**
     * GroupController constructor.
     * @param GroupService $groupService
     */
    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return GroupCollection
     */
    public function index()
    {
        return new GroupCollection($this->groupService->getGroups());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreGroup $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreGroup $request)
    {
        $validatedRequestData = $request->validated();
        $this->groupService->createGroup($validatedRequestData);

        return response()->json([
            'message' => __('api.group.add')
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
        $group = $this->groupService->getGroupById($id);

        if (!$group) {
            return $this->groupNotFoundResponse();
        }

        return new GroupResource($group);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateGroup $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateGroup $request, int $id)
    {
        $group = $this->groupService->getGroupById($id);

        if (!$group) {
            return $this->groupNotFoundResponse();
        }

        $validatedRequestData = $request->validated();
        $this->groupService->updateGroup($validatedRequestData);

        return response()->json([
            'message' => __('api.group.update')
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
        $group = $this->groupService->getGroupById($id);

        if (!$group) {
            return $this->groupNotFoundResponse();
        }

        $this->groupService->deleteGroup();

        return response()->json([
            'message' => __('api.group.delete')
        ]);
    }

    /**
     * Not founded resource
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function groupNotFoundResponse()
    {
        return response()->json([
            'message' => __('api.group.not_founded')
        ], 404);
    }
}
