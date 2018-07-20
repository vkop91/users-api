<?php

namespace App\Http\Services;

use App\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Services\GroupService;

class UserService
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var GroupService
     */
    protected $groupService;

    /**
     * GroupService constructor.
     * @param User $user
     */
    public function __construct(User $user, GroupService $groupService)
    {
        $this->user = $user;
        $this->groupService = $groupService;
    }

    /**
     * Returns uninstalled users
     *
     * @return User[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getUsers()
    {
        return User::all();
    }

    /**
     * Create user
     *
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User
    {
        $data['password'] = Hash::make($data['password']);

        if (isset($data['group_id'])) {
            $group = $this->groupService->getGroupById($data['group_id']);
            $group->users()->create($data);
        } else {
            $this->user->create($data);
        }

        return $this->user;
    }

    /**
     * Find user by id
     *
     * @param int $id
     * @return User|null
     */
    public function getUserById(int $id)
    {
        $this->user = User::with('groups')->find($id);

        return $this->user;
    }

    /**
     * Find user by name
     *
     * @param string $name
     * @return User|null
     */
    public function getUserByName(string $name)
    {
        $this->user = User::where('first_name', $name)->with('groups')->first();

        return $this->user;
    }

    /**
     * Update user
     *
     * @param array $data
     * @return User
     */
    public function updateUser(array $data): User
    {
        $this->user->update($data);

        return $this->user;
    }

    /**
     * Delete user
     */
    public function deleteUser()
    {
        $this->user->delete();
    }
}