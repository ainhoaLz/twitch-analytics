<?php

namespace Tests\Integration\Controllers;

use Tests\TestCase;

class GetTopsOfTheTopsControllerTest extends TestCase
{
    /**
     * @test
     * @throws \Exception
     */
    public function gets400WhenSinceValueIsBelowZero(): void
    {
        $response = $this->call(
            'GET',
            '/analytics/topsofthetops',
            ['since' => '-1'],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer 7cea4198cc833c8a3ac906080134d318dad625ea9da562367d848b1813ad35da']
        );

        $response->assertStatus(400);
        $response->assertJson([
            'error' => "Invalid or missing 'since' parameter."
        ]);
    }

    /**
     * @test
     * @throws \Exception
     */
    public function gets400WhenSinceValueIsInvalid(): void
    {
        $response = $this->call(
            'GET',
            '/analytics/topsofthetops',
            ['since' => 'a'],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer 7cea4198cc833c8a3ac906080134d318dad625ea9da562367d848b1813ad35da']
        );

        $response->assertStatus(400);
        $response->assertJson([
            'error' => "Invalid or missing 'since' parameter."
        ]);
    }

    /**
     * @test
     */
    public function gets401WithInvalidToken(): void
    {
        $response = $this->call(
            'GET',
            'analytics/topsofthetops',
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
    public function gets200WhenSinceParameterIsMissing(): void
    {
        $response = $this->call(
            'GET',
            '/analytics/topsofthetops',
            [],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer 7cea4198cc833c8a3ac906080134d318dad625ea9da562367d848b1813ad35da'],
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            [
                'game_id',
                'game_name',
                'user_name',
                'total_videos',
                'total_views',
                'most_viewed_title',
                'most_viewed_views',
                'most_viewed_duration',
                'most_viewed_created_at',
            ],
        ]);
    }

    /**
     * @test
     */
    public function gets200WhenSinceValueIsValid(): void
    {
        $response = $this->call(
            'GET',
            '/analytics/topsofthetops',
            ['since' => '1000'],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer 7cea4198cc833c8a3ac906080134d318dad625ea9da562367d848b1813ad35da'],
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            [
                'game_id',
                'game_name',
                'user_name',
                'total_videos',
                'total_views',
                'most_viewed_title',
                'most_viewed_views',
                'most_viewed_duration',
                'most_viewed_created_at',
            ],
        ]);
    }
}
