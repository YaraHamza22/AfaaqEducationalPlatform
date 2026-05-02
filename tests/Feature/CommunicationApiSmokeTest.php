<?php

namespace Tests\Feature;

use Tests\TestCase;

class CommunicationApiSmokeTest extends TestCase
{
    public function test_phase2_communication_routes_are_registered(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutesByMethod()['GET'] ?? [])
            ->map(fn ($route) => $route->uri());

        $this->assertTrue($routes->contains('api/v1/chat-threads'));
        $this->assertTrue($routes->contains('api/v1/notifications'));
        $this->assertTrue($routes->contains('api/v1/forum-threads'));
        $this->assertTrue($routes->contains('api/v1/virtual-sessions'));
        $this->assertTrue($routes->contains('api/v1/offline-packages'));
        $this->assertTrue($routes->contains('api/v1/super-admin/snapshots/assessment-progress'));
    }
}
