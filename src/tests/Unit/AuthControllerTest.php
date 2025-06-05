<?php

namespace Tests\Unit;

use App\Http\Controllers\AuthController;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Repositories\AuthRepository;
use App\Repositories\Interfaces\AuthRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected AuthController $controller;
    protected $mockRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRepository = $this->createMock(AuthRepositoryInterface::class);
        $this->controller = new AuthController(new AuthRepository());
    }

    public function test_registers_user_successfully()
    {
        $request = new RegisterRequest([
            'name' => 'Diogo Santos',
            'email' => 'diogo.santos@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response = $this->controller->register($request);

        $this->assertEquals(201, $response->status());
        $this->assertDatabaseHas('users', ['email' => 'diogo.santos@example.com']);
    }

    public function test_register_fails_for_duplicate_email()
    {
        $existingUser = User::factory()->create([
            'email' => 'duplicate@example.com',
        ]);

        $response = $this->postJson('/api/register', [
            'name' => 'Another User',
            'email' => 'duplicate@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_register_fails_when_passwords_do_not_match()
    {
        $request = new Request([
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'secret',
            'password_confirmation' => 'not_matching',
        ]);

        $response = $this->postJson('/api/register', $request->all());

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_register_fails_with_invalid_email()
    {
        $request = new Request([
            'name' => 'Invalid Email',
            'email' => 'invalid-email',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response = $this->postJson('/api/register', $request->all());

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'wrong@example.com',
            'password' => bcrypt('correct-password'),
        ]);

        $request = new Request([
            'email' => 'wrong@example.com',
            'password' => 'incorrect-password',
        ]);

        $response = $this->postJson('/api/login', $request->all());

        $response->assertStatus(401);
        $response->assertJson(['message' => 'Invalid credentials']);
    }

    public function test_register_fails_with_missing_fields()
    {
        $request = new Request([]); 

        $response = $this->postJson('/api/register', $request->all());

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email', 'password']);
    }
}
