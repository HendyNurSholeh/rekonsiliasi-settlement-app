<?php

namespace Tests\Feature\Controllers\User;

use Tests\Support\FeatureTestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ProfileFeatureTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create([
            'password' => Hash::make('oldpassword123')
        ]);

        // Login user
        $this->actingAs($this->user);
    }

    public function testPasswordUpdateValidationFailure()
    {
        $response = $this->post('/profile', [
            'old_password' => 'oldpassword123',
            'new_password' => 'weak', // Invalid password
            'conf_password' => 'weak'
        ]);

        $response->assertStatus(302); // Redirect back
        $response->assertSessionHasErrors(['new_password']);
    }

    public function testPasswordUpdateWrongOldPassword()
    {
        $response = $this->post('/profile', [
            'old_password' => 'wrongpassword',
            'new_password' => 'NewPassword123!',
            'conf_password' => 'NewPassword123!'
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('error', 'Password lama tidak valid');
    }

    public function testPasswordUpdateSuccess()
    {
        $response = $this->post('/profile', [
            'old_password' => 'oldpassword123',
            'new_password' => 'NewPassword123!',
            'conf_password' => 'NewPassword123!'
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('success', 'Anda berhasil logout!');

        // Verify password was updated
        $this->user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $this->user->password));
    }

    public function testProfileView()
    {
        $response = $this->get('/profile');

        $response->assertStatus(200);
        $response->assertSee('Profile');
        $response->assertSee($this->user->name);
        $response->assertSee($this->user->username);
    }
}
<parameter name="filePath">c:\Users\lenovo\project_hendy\test\rekonsiliasi-settlement-app\tests\feature\Controllers\User\ProfileFeatureTest.php
