<?php

namespace Tests\Unit\Application\Services;

use App\Application\Services\TwitchUserService;
use App\Domain\Interfaces\TwitchAPI\TwichUserRepositoryInterface;
use Mockery;
use PHPUnit\Framework\TestCase;

class TwitchUserServiceTest extends TestCase
{
    private TwitchUserService $service;
    private TwichUserRepositoryInterface $repository;
    protected function setUp(): void
    {
        //        parent::setUp();
        //        $this->repository = Mockery::mock(TwichUserRepositoryInterface::class);
        //        $this->service = new TwitchUserService($this->repository);
    }

    /**
     * @test
     * @throws \Exception
     */
    //    public function getsTwitchUserInfoGivenId()
    //    {
    //        //hay que crear un fake user, ya que la info de twitch va cambiando
    //        $streamerId = 39518378;
    //        $userInfo = [
    //            'httpCode' => 200,
    //            'user' => json_encode([
    //                'data' => [
    //                    [
    //                        "id" => "39518378",
    //                        "login" => "sirmaza",
    //                        "display_name" => "SirMaza",
    //                        "type" => "",
    //                        "broadcaster_type" => "partner",
    //                        "description" =>
    //                            "Ey soy Maza, antes competía en Valorant y ahora me dedico a crear contenido...",
    //                        "profile_image_url" =>
    //                              "https://static-cdn.jtvnw.net/xxx-profile_image-300x300.png",
    //                        "offline_image_url" =>
    //                              "https://static-cdn.jtvnw.net/xxx-channel_offline_image-1920x1080.jpeg",
    //                        "view_count" => 0,
    //                        "created_at" => "2013-01-20T14:14:33Z"
    //                    ]
    //                ]
    //            ])
    //        ];
    //
    //        $expectedUser = [
    //            "id" => "39518378",
    //            "login" => "sirmaza",
    //            "display_name" => "SirMaza",
    //            "type" => "",
    //            "broadcaster_type" => "partner",
    //            "description" =>
    //                "Ey soy Maza, antes competía en Valorant y ahora me dedico a crear contenido e intentar
    //                  dar lo mejor de mi mismo a diario, podéis contactarme en sirmaza@suppagency.gg",
    //            "profile_image_url" =>
    //                "https://static-cdn.jtvnw.net/jtv_user_pictures/cc594a1b-4ace-4cbd-9846-f871f329bd39-profile_image-300x300.png",
    //            "offline_image_url" =>
    //                "https://static-cdn.jtvnw.net/jtv_user_pictures/c51e93da-12a5-4e86-baea-666b27e1c482-channel_offline_image-1920x1080.jpeg",
    //            "view_count" => 0,
    //            "created_at" => "2013-01-20T14:14:33Z"
    //        ];
    //
    //        $this->repository->expects('getUser')
    //            ->with($streamerId)
    //            ->andReturns($userInfo);
    //
    //        $result = $this->service->getUserById($streamerId);
    //
    //        $this->assertEquals($expectedUser, $result);
    //    }
}
