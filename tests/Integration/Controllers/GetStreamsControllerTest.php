<?php

namespace Tests\Integration\Controllers;

use Tests\TestCase;

class GetStreamsControllerTest extends TestCase
{
    /**
     * @test
     */
    public function gets401WithInvalidToken(): void
    {
        $response = $this->call(
            'GET',
            'analytics/streams',
            [],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer invalid_token']
        );

        $response->assertStatus(401);
    }

    /**
     * @test
     */
    public function gets200WhenTokenIsValid(): void
    {
        $response = $this->call(
            'GET',
            '/analytics/streams',
            [],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer 7cea4198cc833c8a3ac906080134d318dad625ea9da562367d848b1813ad35da']
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'title',
                'user_name',
            ],
        ]);
    }
}
