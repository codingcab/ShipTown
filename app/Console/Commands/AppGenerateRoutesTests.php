<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

/**
 *
 */
class AppGenerateRoutesTests extends Command
{
    protected $signature = 'app:generate-routes-tests';

    protected $description = 'Generates tests for all not test covered routes';

    public function handle(): int
    {
        $except = collect(['telescope']);
        $this->generateApiRoutesTestsFiles($except);
        $this->generateWebRoutesTestsFiles($except);

        return 0;
    }

    private function generateApiRoutesTestsFiles(Collection $except): void
    {
        Artisan::call('route:list --json --path=/api/ --env=production');

        $routes = collect(json_decode(Artisan::output()));

        $routes->filter(function ($route) use ($except) {
            return $except->every(function ($exceptedRoute) use ($route) {
                return ! Str::startsWith($route->uri, $exceptedRoute);
            });
        })
        ->each(function ($route) {
            $testName = $this->getApiRouteTestName($route);

            $fullFileName = app()->basePath();
            $fullFileName .= '/tests/Feature/';
            $fullFileName .= $testName;
            $fullFileName .= '.php';

            if (! file_exists($fullFileName)) {
                Artisan::call('app:make-test '.$testName.' --stub=test.controller');

                dd($fullFileName);
            }
        });
    }

    /**
     *
     */
    private function generateWebRoutesTestsFiles(Collection $except): void
    {
        Artisan::call('route:list --json --env=production');

        $routes = collect(json_decode(Artisan::output()))
            ->filter(function ($route) use ($except) {
                $isNotExcluded = $except->every(function ($exceptedRoute) use ($route) {
                    return ! Str::startsWith($route->uri, $exceptedRoute);
                });

                $isNotApiRoute = ! Str::startsWith($route->uri, 'api');
                $isGetMethod = $route->method === 'GET|HEAD';
                $isNotDevRoute = ! Str::startsWith($route->uri, '_');

                return $isNotExcluded & $isNotApiRoute && $isGetMethod && $isNotDevRoute;
            });


        $routes->each(function ($route) {
            $testName = $this->getWebRouteTestName($route);

            $fullFileName = app()->basePath();
            $fullFileName .= '/tests/Feature/';
            $fullFileName .= $testName;
            $fullFileName .= '.php';

            if (! file_exists($fullFileName)) {
                $this->comment($testName);
                Artisan::call('app:make-test '.$testName.' --stub=test.web_route');
                $this->info(Artisan::output());
            }
        });
    }

    /**
     * @param $route
     *
     * @return string
     */
    public function getApiRouteTestName($route): string
    {
        $methodName = Str::after($route->action, '@');

        $routeName = $route->uri .'/'.$methodName.'Test';

        $routeName = str_replace('-', '_', $routeName);
        $routeName = str_replace('.', '_', $routeName);
        $routeName = str_replace('{', '', $routeName);
        $routeName = str_replace('}', '', $routeName);
        $routeName = Str::camel($routeName);

        return implode('/', collect(explode('/', $routeName))->map(function ($part) {
            return Str::ucfirst($part);
        })->toArray());
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
        $routeName = Str::camel($routeName);

        return implode('/', collect(explode('/', $routeName))->map(function ($part) {
            return Str::ucfirst($part);
        })->toArray());
    }
}
