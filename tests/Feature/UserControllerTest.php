<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that index returns paginated users.
     */
    public function test_index_returns_paginated_users(): void
    {
        // Create some test users
        User::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'email',
                            'created_at',
                            'updated_at',
                        ]
                    ],
                    'per_page',
                    'total',
                ]
            ])
            ->assertJson([
                'success' => true,
            ]);

        $this->assertEquals(5, $response->json('data.total'));
    }

    /**
     * Test that store creates a new user.
     */
    public function test_store_creates_new_user(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/v1/users', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'User created successfully',
                'data' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    /**
     * Test that store validates required fields.
     */
    public function test_store_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/users', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => [
                    'name',
                    'email',
                    'password',
                ]
            ])
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed',
            ]);
    }

    /**
     * Test that store validates email uniqueness.
     */
    public function test_store_validates_email_uniqueness(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $userData = [
            'name' => 'New User',
            'email' => 'existing@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/v1/users', $userData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => [
                    'email',
                ]
            ]);
    }

    /**
     * Test that show returns a specific user.
     */
    public function test_show_returns_specific_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $response = $this->getJson("/api/v1/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'email_verified_at',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => 'Jane Doe',
                    'email' => 'jane@example.com',
                ]
            ]);
    }

    /**
     * Test that show returns 404 for non-existent user.
     */
    public function test_show_returns_404_for_non_existent_user(): void
    {
        $response = $this->getJson('/api/v1/users/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'User not found',
            ]);
    }

    /**
     * Test that update modifies an existing user.
     */
    public function test_update_modifies_existing_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $updateData = [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ];

        $response = $this->putJson("/api/v1/users/{$user->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => 'New Name',
                    'email' => 'new@example.com',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);
    }

    /**
     * Test that update returns 404 for non-existent user.
     */
    public function test_update_returns_404_for_non_existent_user(): void
    {
        $updateData = [
            'name' => 'New Name',
        ];

        $response = $this->putJson('/api/v1/users/999', $updateData);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'User not found',
            ]);
    }

    /**
     * Test that update validates email uniqueness excluding current user.
     */
    public function test_update_validates_email_uniqueness_excluding_current_user(): void
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        // Try to update user1 with user2's email
        $response = $this->putJson("/api/v1/users/{$user1->id}", [
            'email' => 'user2@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => [
                    'email',
                ]
            ]);
    }

    /**
     * Test that update allows same email for current user.
     */
    public function test_update_allows_same_email_for_current_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response = $this->putJson("/api/v1/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => 'test@example.com', // Same email
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User updated successfully',
            ]);
    }

    /**
     * Test that destroy deletes a user.
     */
    public function test_destroy_deletes_user(): void
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/v1/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User deleted successfully',
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    /**
     * Test that destroy returns 404 for non-existent user.
     */
    public function test_destroy_returns_404_for_non_existent_user(): void
    {
        $response = $this->deleteJson('/api/v1/users/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'User not found',
            ]);
    }
}
