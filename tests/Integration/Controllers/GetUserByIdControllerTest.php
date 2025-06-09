<?php

namespace Tests\Integration\Controllers;

use Tests\TestCase;

class GetUserByIdControllerTest extends TestCase
{
    /**
     * @test
     * @throws \Exception
     */
    public function gets400WhenIdParameterIsMissing(): void
    {
        $response = $this->call(
            'GET',
            '/analytics/user',
            ['id' => ''],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer 7cea4198cc833c8a3ac906080134d318dad625ea9da562367d848b1813ad35da']
        );

        $response->assertStatus(400);
        $response->assertJson([
            'error' => "Invalid or missing 'id' parameter."
        ]);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function gets400WhenIdParameterIsInvalid(): void
    {
        $response = $this->call(
            'GET',
            '/analytics/user',
            ['id' => 'a'],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer 7cea4198cc833c8a3ac906080134d318dad625ea9da562367d848b1813ad35da']
        );

        $response->assertStatus(400);
        $response->assertJson([
            'error' => "Invalid or missing 'id' parameter."
        ]);
    }

    /**
     * @test
     */
    public function gets401WithInvalidToken(): void
    {
        $response = $this->call(
            'GET',
            'analytics/user',
            ['id' => '39518378'],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer invalid_token']
        );

        $response->assertStatus(401);
    }

    /**
     * @test
     */
    public function gets200WhenIdParameterIsValid(): void
    {
        $response = $this->call(
            'GET',
            'analytics/user',
            ['id' => '39518378'],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer 7cea4198cc833c8a3ac906080134d318dad625ea9da562367d848b1813ad35da']
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'login',
            'display_name',
            'type',
            'broadcaster_type',
            'description',
            'profile_image_url',
            'offline_image_url',
            'view_count',
            'created_at',
        ]);
    }
}
