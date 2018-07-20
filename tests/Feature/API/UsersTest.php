<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Group;
use App\Http\Services\UserService;
use App\Http\Services\GroupService;

class UsersTest extends TestCase
{
    /**
     * Service for work with users
     *
     * @var UserService
     */
    private $userService;

    /**
     * Service for work with groups
     *
     * @var GroupService
     */
    private $groupService;

    /**
     * SetUp test
     */
    public function setUp()
    {
        parent::setUp();
        $this->groupService = new GroupService(new Group());
        $this->userService = new UserService(new User(), $this->groupService);
    }

    /**
     * Test requests usersAPI
     */
    public function testApiRequsets()
    {
        $fakeUser = $this->makeFakeData();

        // requests
        // add
        $this->addInvalidItem();
        $this->addSuccessItem($fakeUser);
        $user = $this->userService->getUserByName($fakeUser->first_name);
        $this->assertNotNull($user);

        // get
        $this->getList($user);
        $this->getNotExsistItem($user->id + 10);
        $this->getItem($user);

        // update
        $this->updateItemInvalid($user);
        $newFakeUser = factory(User::class)->make();
        $user->first_name = $newFakeUser->first_name;
        $user->last_name = $newFakeUser->last_name;
        $user->email = $newFakeUser->email;
        $this->updateItemSuccess($user);

        // remove
        $this->removeNotExsistItem($user->id + 10);
        $this->removeItemSuccess($user);

        // remove fake group
        $this->clearFakeData();
    }

    /**
     * Make fake data for tests
     *
     * @return mixed
     */
    private function makeFakeData()
    {
        $fakeUser = factory(User::class)->make();
        $fakeGroup = factory(Group::class)->make();
        $group = $this->groupService->createGroup($fakeGroup->toArray());
        $fakeUser->group_id = $group->id;
        $fakeUser->group_name = $group->name;

        return $fakeUser;
    }

    /**
     * Delete fake date
     */
    private function clearFakeData()
    {
        $this->groupService->deleteGroup();
    }

    /**
     * Add invalid user.
     *
     * @return void
     */
    private function addInvalidItem()
    {
        $response = $this->json(
            'POST',
            '/api/users/',
            [
                'first_name' => '',
                'email' => 'test email',
                'group_id' => 'no'
            ]
        );
        $response
            ->assertStatus(422)
            ->assertJson(["message" => "The given data was invalid."])
            ->assertJsonFragment(["The first name field is required."])
            ->assertJsonFragment(["The password field is required."])
            ->assertJsonFragment(["The email must be a valid email address."])
            ->assertJsonFragment(["The group id must be an integer."]);
    }

    /**
     * Add user.
     *
     * @param User $user
     */
    private function addSuccessItem(User $user)
    {
        $response = $this->json(
            'POST',
            '/api/users/',
            [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'password' => $user->password,
            ]
        );
        $response
            ->assertStatus(200)
            ->assertJson([
                "message" => "User has been added.",
            ]);
        $this->assertDatabaseHas('users', [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'state' => 'active'
        ]);
    }

    /**
     * Get users list.
     *
     * @param User $user
     */
    private function getList(User $user)
    {
        $response = $this->json('GET', '/api/users/');
        $response
            ->assertStatus(200)
            ->assertJsonFragment($this->getItemResponseData($user));
    }

    /**
     * Get users item.
     *
     * @return void
     */
    private function getNotExsistItem(int $userId)
    {
        $response = $this->json('GET', '/api/users/' . $userId);
        $response
            ->assertStatus(404)
            ->assertJson([
                "message" => "User not founded.",
            ]);
    }

    /**
     * Get users item.
     *
     * @param User $user
     */
    private function getItem(User $user)
    {
        $response = $this->json('GET', '/api/users/' . $user->id);
        $response
            ->assertStatus(200)
            ->assertJsonFragment($this->getItemResponseData($user));
    }

    /**
     * Update user invalid.
     *
     * @param User $user
     */
    private function updateItemInvalid(User $user)
    {
        $response = $this->json(
            'PUT',
            '/api/users/' . $user->id,
            [
                'first_name' => '',
                'email' => 'test email',
                'state' => 'non active'
            ]
        );
        $response
            ->assertStatus(422)
            ->assertJson([
                "message" => "The given data was invalid.",
            ])
            ->assertJsonFragment([
                "The first name field must have a value.",
            ])
            ->assertJsonFragment([
                "The email must be a valid email address.",
            ]);
    }

    /**
     * Update user success.
     *
     * @param User $user
     */
    private function updateItemSuccess(User $user)
    {
        $response = $this->json(
            'PUT',
            '/api/users/' . $user->id,
            [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'state' => 'non active'
            ]
        );
        $response
            //->assertStatus(200)
            ->assertJson([
                "message" => "User has been updated.",
            ]);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'state' => 'active'
        ]);
    }

    /**
     * Delete user.
     *
     * @param int $userId
     */
    private function removeNotExsistItem(int $userId)
    {
        $response = $this->json('DELETE', "/api/users/$userId");
        $response
            ->assertStatus(404)
            ->assertJson([
                "message" => "User not founded.",
            ]);
    }

    /**
     * Delete user.
     *
     * @param User $user
     */
    private function removeItemSuccess(User $user)
    {
        $response = $this->json('DELETE', '/api/users/' . $user->id);
        $response
            ->assertStatus(200)
            ->assertJson([
                "message" => "User has been deleted.",
            ]);
        $this->assertNull(User::find($user->id));
    }

    /**
     * Item for json response
     *
     * @param User $user
     * @return array
     */
    private function getItemResponseData(User $user)
    {
        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'state' => $user->state,
            'creation_date' => $user->created_at->toDateString(),
            'groups' => $user->groups->pluck('name', 'id')
        ];
    }
}
