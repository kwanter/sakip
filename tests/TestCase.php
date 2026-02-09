<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Indicates whether the default seeder should run before each test.
     */
    protected $seeder = true;

    /**
     * Create a user with a specific role.
     */
    protected function createUserWithRole(string $roleName)
    {
        $role = \Spatie\Permission\Models\Role::firstOrCreate(
            ['name' => $roleName],
            ['guard_name' => 'web']
        );

        $user = \App\Models\User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
