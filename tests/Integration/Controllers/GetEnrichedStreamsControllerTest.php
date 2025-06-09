<?php

namespace Tests\Integration\Controllers;

use Tests\TestCase;

class GetEnrichedStreamsControllerTest extends TestCase
{
    /**
     * @test
     * @throws \Exception
     */
    public function gets400WhenLimitParameterIsMissing(): void
    {
        $response = $this->call(
            'GET',
            '/analytics/streams/enriched',
            [],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer 7cea4198cc833c8a3ac906080134d318dad625ea9da562367d848b1813ad35da']
        );

        $response->assertStatus(400);
        $response->assertJson([
            'error' => "Invalid 'limit' parameter."
        ]);
    }

    /**
     * @test
     */
    public function gets401WithInvalidToken(): void
    {
        $response = $this->call(
            'GET',
            'analytics/streams/enriched',
            ['limit' => '5'],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer invalid_token']
        );

        $response->assertStatus(401);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function gets400WhenLimitParameterIsInvalid(): void
    {
        $response = $this->call(
            'GET',
            '/analytics/streams/enriched',
            ['limit' => 'invalid'],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer 7cea4198cc833c8a3ac906080134d318dad625ea9da562367d848b1813ad35da']
        );

        $response->assertStatus(400);
        $response->assertJson([
            'error' => "Invalid 'limit' parameter."
        ]);
    }

    /**
     * @test
     */
    public function gets200WhenLimitIsValid(): void
    {
        $response = $this->call(
            'GET',
            '/analytics/streams/enriched',
            ['limit' => '5'],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer 7cea4198cc833c8a3ac906080134d318dad625ea9da562367d848b1813ad35da']
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'stream_id',
                'user_id',
                'user_name',
                'viewer_count',
                'title',
                'user_display_name',
                'profile_image_url',
            ],
        ]);
    }
}
