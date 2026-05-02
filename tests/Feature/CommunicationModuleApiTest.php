<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Tests\TestCase;

class CommunicationModuleApiTest extends TestCase
{
    public function test_guest_cannot_access_communication_endpoints(): void
    {
        $this->getJson('/api/v1/chat-threads')->assertUnauthorized();
        $this->getJson('/api/v1/forum-threads')->assertUnauthorized();
        $this->getJson('/api/v1/notifications')->assertUnauthorized();
        $this->getJson('/api/v1/virtual-sessions')->assertUnauthorized();
        $this->getJson('/api/v1/offline-packages')->assertUnauthorized();
    }

    public function test_key_communication_routes_are_registered(): void
    {
        $routes = app('router')->getRoutes();

        $routes->match(Request::create('/api/v1/chat-threads', 'GET'));
        $routes->match(Request::create('/api/v1/forum-threads', 'GET'));
        $routes->match(Request::create('/api/v1/notifications', 'GET'));
        $routes->match(Request::create('/api/v1/virtual-sessions', 'GET'));
        $routes->match(Request::create('/api/v1/offline-packages', 'GET'));

        $this->assertTrue(true);
    }
}
