<?php

namespace Tests\Integration\Controllers;

use Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    /**
     * @test
     * @throws \Exception
     */
    public function gets400WhenEmailParameterIsMissing(): void
    {
        $response = $this->call(
            'POST',
            '/register'
        );

        $response->assertStatus(400);
        $response->assertJson([
            'error' => "The email is mandatory"
        ]);
    }

    /**
     * @test
     */
    public function gets400WhenEmailParameterIsNull(): void
    {
        $response = $this->call(
            'POST',
            '/register',
            ['email' => null]
        );

        $response->assertStatus(400);
        $response->assertJson([
            'error' => "The email is mandatory"
        ]);
    }

    /**
     * @test
     */
    public function gets400WhenInvalidEmailIsGiven(): void
    {
        $response = $this->call(
            'POST',
            '/register',
            ['email' => 'abc']
        );

        $response->assertStatus(400);
        $response->assertJson([
            'error' => "The email must be a valid email address"
        ]);
    }

    /**
     * @test
     */
    public function gets200WhenValidEmailIsGiven(): void
    {
        $response = $this->call(
            'POST',
            '/register',
            ['email' => 'ainhoa@gmail.com']
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'api_key'
        ]);
    }
}
