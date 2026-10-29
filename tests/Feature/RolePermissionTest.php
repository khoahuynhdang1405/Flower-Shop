<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_and_permission_are_linked_through_pivot(): void
    {
        $staff = Role::factory()->staff()->create();
        Role::factory()->create(['slug' => 'other']);
        $permission = Permission::create(['name' => 'Xem đơn hàng', 'slug' => 'orders.view', 'group_name' => 'orders']);

        $staff->permissions()->attach($permission);

        $this->assertDatabaseHas('role_has_permissions', ['role_id' => $staff->id, 'permission_id' => $permission->id]);
        $this->assertTrue($staff->permissions()->sole()->is($permission));
        $this->assertTrue($permission->roles()->sole()->is($staff));
    }
}
