<?php

namespace Tests\Coverage;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Tests\TestCase;

class WebRoutesCoverageTest extends TestCase
{
    /**
     * A basic test to make sure all routes have minimum one test file.
     *
     * @return void
     */
    public function test_if_all_web_routes_have_test_file()
    {
        Artisan::call('route:list --json --env=production');

        collect(json_decode(Artisan::output()))
            ->filter(function ($route) {
                $isNotApiRoute  = ! Str::startsWith($route->uri, 'api');
                $isNotDevRoute  = ! Str::startsWith($route->uri, '_');
                $isGetMethod    = $route->method === 'GET|HEAD';

                return $isNotApiRoute && $isNotDevRoute && $isGetMethod ;
            })
            ->map(function ($route) {
                $fullFileName = app()->basePath();
                $fullFileName .= '/tests/Feature/Routes/Web/';
                $fullFileName .= $this->getWebRouteTestName($route);
                $fullFileName .= '.php';

                return $fullFileName;
            })
            ->each(function ($fileName) {
                $this->assertFileExists($fileName, 'run "php artisan app:generate-routes-tests"');
            });
    }

    /**
     * @param $route
     * @return string
     */
    private function getWebRouteTestName($route): string
    {
        $routeName = $route->uri . 'Test';

        $routeName = str_replace('-', '_', $routeName);
        $routeName = str_replace('.', '_', $routeName);
        $routeName = str_replace('{', '', $routeName);
        $routeName = str_replace('}', '', $routeName);

        return implode('/', collect(explode('/', $routeName))
            ->map(function ($part) {
                return Str::ucfirst($part);
            })
            ->toArray());
    }
}
