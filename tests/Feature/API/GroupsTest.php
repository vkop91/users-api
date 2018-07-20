<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Group;

class GroupsTest extends TestCase
{
    public function testApiRequsets()
    {
        $fakeGroup = factory(Group::class)->make();

        $this->addInvalidItem();
        $this->addSuccessItem($fakeGroup);

        $group = Group::where('name', $fakeGroup->name)->with('users')->first();
        $this->getList($group);

        $notExsistItemId = $group->id + 10;
        $this->getNotExsistItem($notExsistItemId);
        $this->getItem($group);
        $this->updateItemInvalid($group);
        $this->updateItemSuccess($group);
        $this->removeNotExsistItem($notExsistItemId);
        $this->removeItemSuccess($group);
    }

    /**
     * Add invalid group.
     *
     * @return void
     */
    private function addInvalidItem()
    {
        $response = $this->json('POST', '/api/groups/');
        $response
            ->assertStatus(422)
            ->assertJson([
                "message" => "The given data was invalid.",
            ]);
    }

    /**
     * Add group.
     *
     * @param Group $group
     */
    private function addSuccessItem(Group $group)
    {
        $response = $this->json('POST', '/api/groups/', ['name' => $group->name]);
        $response
            ->assertStatus(200)
            ->assertJson([
                "message" => __('api.group.add'),
            ]);
        $this->assertDatabaseHas('groups', [
            'name' => $group->name
        ]);
    }

    /**
     * Get groups list.
     *
     * @param Group $group
     */
    private function getList(Group $group)
    {
        $response = $this->json('GET', '/api/groups/');
        $response
            ->assertStatus(200)
            ->assertJsonFragment($this->getItemResponseData($group));
    }

    /**
     * Get groups item.
     *
     * @return void
     */
    private function getNotExsistItem(int $groupId)
    {
        $response = $this->json('GET', '/api/groups/' . $groupId);
        $response
            ->assertStatus(404)
            ->assertJson([
                "message" => __('api.group.not_founded'),
            ]);
    }

    /**
     * Get groups item.
     *
     * @param Group $group
     */
    private function getItem(Group $group)
    {
        $response = $this->json('GET', '/api/groups/' . $group->id);
        $response
            ->assertStatus(200)
            ->assertJsonFragment($this->getItemResponseData($group));
    }

    /**
     * Update group invalid.
     *
     * @param Group $group
     */
    private function updateItemInvalid(Group $group)
    {
        $response = $this->json('PUT', '/api/groups/' . $group->id, ['name' => '']);
        $response
            ->assertStatus(422)
            ->assertJson([
                "message" => "The given data was invalid.",
            ]);
    }

    /**
     * Update group success.
     *
     * @param Group $group
     */
    private function updateItemSuccess(Group $group)
    {
        $group->name = 'Update ' . $group->name;
        $response = $this->json('PUT', '/api/groups/' . $group->id, ['name' => $group->name]);
        $response
            ->assertStatus(200)
            ->assertJson([
                "message" => __('api.group.update'),
            ]);
        $this->assertDatabaseHas('groups', [
            'id'   => $group->id,
            'name' => $group->name
        ]);
    }

    /**
     * Delete group.
     *
     * @param int $groupId
     */
    private function removeNotExsistItem(int $groupId)
    {
        $response = $this->json('DELETE', "/api/groups/$groupId");
        $response
            ->assertStatus(404)
            ->assertJson([
                "message" => __('api.group.not_founded'),
            ]);
    }

    /**
     * Delete group.
     *
     * @param Group $group
     */
    private function removeItemSuccess(Group $group)
    {
        $response = $this->json('DELETE', '/api/groups/' . $group->id);
        $response
            ->assertStatus(200)
            ->assertJson([
                "message" => __('api.group.delete'),
            ]);
        $this->assertNull(Group::find($group->id));
    }

    /**
     * Item for json response
     *
     * @param Group $group
     * @return array
     */
    private function getItemResponseData(Group $group)
    {
        return [
            'id'   => $group->id,
            'name' => $group->name,
            'users' => []
        ];
    }
}
