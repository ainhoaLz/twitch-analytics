<?php

namespace Tests\Integration\Controllers;

use Exception;
use Tests\TestCase;

class TokenControllerTest extends TestCase
{
    /**
     * @test
     * @throws Exception
     */
    public function gets400WhenEmailParameterIsMissing(): void
    {
        $response = $this->call(
            'POST',
            '/token',
            [
                'api_key' => 'abcd123'
            ]
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
            '/token',
            [
                'email' => null,
                'api_key' => 'abcd123'
            ]
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
            '/token',
            [
                'email' => 'abc',
                'api_key' => 'abcd123'
            ]
        );
        $response->assertStatus(400);
        $response->assertJson([
            'error' => "The email must be a valid email address"
        ]);
    }

    /**
     * @test
     * @throws Exception
     */
    public function gets400WhenApiKeyParameterIsMissing(): void
    {
        $response = $this->call(
            'POST',
            '/token',
            [
                'email' => 'ainhoa@gmail.com',
            ]
        );
        $response->assertStatus(400);
        $response->assertJson([
            'error' => "The api_key is mandatory"
        ]);
    }

    /**
     * @test
     */
    public function gets200WhenEmailAndApiKeyAreValid(): void
    {
        $response = $this->call(
            'POST',
            '/token',
            [
                'email' => 'validEmail@gmail.com',
                'api_key' => 'fd2900f5bbef470ba0bd4da8ba803109'
            ]
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'token'
        ]);
    }
}
