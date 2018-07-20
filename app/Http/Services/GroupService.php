<?php

namespace App\Http\Services;

use App\Group;
use Illuminate\Support\Facades\Hash;

class GroupService
{
    /**
     * @var Group
     */
    protected $group;

    /**
     * GroupService constructor.
     * @param Group $group
     */
    public function __construct(Group $group)
    {
        $this->group = $group;
    }

    /**
     * Returns groups
     *
     * @return Group[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getGroups()
    {
        return Group::all();
    }

    /**
     * Create group
     *
     * @param array $data
     * @return Group
     */
    public function createGroup(array $data): Group
    {
        $this->group->create($data);

        return $this->group;
    }

    /**
     * Find group by id
     *
     * @param int $id
     * @return Group|null
     */
    public function getGroupById(int $id)
    {
        $this->group = Group::find($id);

        return $this->group;
    }

    /**
     * Update group
     *
     * @param array $data
     * @return Group
     */
    public function updateGroup(array $data): Group
    {
        $this->group->update($data);

        return $this->group;
    }

    /**
     * Delete group
     */
    public function deleteGroup()
    {
        $this->group->delete();
    }
}