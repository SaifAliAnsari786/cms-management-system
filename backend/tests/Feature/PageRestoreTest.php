<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PageRestoreTest extends TestCase
{
    public function test_restore_route_is_registered_for_pages(): void
    {
        $routes = Route::getRoutes();
        $restoreRoute = collect($routes)->first(fn ($route) => $route->uri() === 'api/pages/{page}/restore');

        $this->assertNotNull($restoreRoute);
        $this->assertContains('POST', $restoreRoute->methods());
        $this->assertSame('App\\Http\\Controllers\\Api\\PageController@restore', $restoreRoute->getActionName());
    }
}
