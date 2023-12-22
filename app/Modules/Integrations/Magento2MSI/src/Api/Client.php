<?php

namespace App\Modules\Integrations\Magento2MSI\src\Api;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Client
{
    public static function get($token, $url, $parameters = []): ?Response
    {
        try {
            $response = Http::withToken($token)->get($url, $parameters);
        } catch (Exception $e) {
            Log::error('MAGENTO2API GET', [
                'url' => $url,
                'response' => $e->getMessage(),
                'parameters' => $parameters,
            ]);

            return null;
        }

        if ($response->failed()) {
            Log::error('MAGENTO2API GET', [
                'response' => implode(' ', [$response->status(), $response->reason()]),
                'url' => $url,
                'json' => $response->json(),
                'parameters' => $parameters,
            ]);

            return $response;
        }

        Log::debug('MAGENTO2API GET', [
            'response' => implode(' ', [$response->status(), $response->reason()]),
            'url' => $url,
            'json' => $response->json(),
            'parameters' => $parameters,
        ]);

        return $response;
    }

    public static function post($token, $url, $parameters = []): ?Response
    {
        try {
            $response = Http::withToken($token)->post($url, $parameters);
        } catch (Exception $e) {
            Log::error('MAGENTO2API POST', [
                'response' => $e->getMessage(),
                'url' => $url,
                'parameters' => $parameters,
            ]);

            return null;
        }

        if ($response->failed()) {
            Log::error('MAGENTO2API POST', [
                'response' => implode(' ', [$response->status(), $response->reason()]),
                'url' => $url,
                'json' => $response->json(),
                'parameters' => $parameters,
            ]);

            return $response;
        }

        Log::debug('MAGENTO2API POST', [
            'response' => implode(' ', [$response->status(), $response->reason()]),
            'url' => $url,
            'json' => $response->json(),
            'parameters' => $parameters,
        ]);

        return $response;
    }

    public static function put($token, $url, $parameters = []): ?Response
    {
        try {
            $response = Http::withToken($token)->put($url, $parameters);
        } catch (Exception $e) {
            Log::error('MAGENTO2API PUT', [
                'response' => $e->getMessage(),
                'url' => $url,
                'parameters' => $parameters,
            ]);

            return null;
        }

        if ($response->failed()) {
            Log::error('MAGENTO2API PUT', [
                'response' => implode(' ', [$response->status(), $response->reason()]),
                'url' => $url,
                'json' => $response->json(),
                'parameters' => $parameters,
            ]);

            return $response;
        }

        Log::debug('MAGENTO2API PUT', [
            'response' => implode(' ', [$response->status(), $response->reason()]),
            'url' => $url,
            'json' => $response->json(),
            'parameters' => $parameters,
        ]);

        return $response;
    }

    public static function delete($token, $url, $parameters = []): ?Response
    {
        try {
            $response = Http::withToken($token)->delete($url, $parameters);
        } catch (Exception $e) {
            Log::error('MAGENTO2API DELETE', [
                'response' => $e->getMessage(),
                'url' => $url,
                'parameters' => $parameters,
            ]);

            return null;
        }

        if ($response->failed()) {
            Log::error('MAGENTO2API DELETE', [
                'response' => implode(' ', [$response->status(), $response->reason()]),
                'url' => $url,
                'json' => $response->json(),
                'parameters' => $parameters,
            ]);

            return $response;
        }

        Log::debug('MAGENTO2API DELETE', [
            'response' => implode(' ', [$response->status(), $response->reason()]),
            'url' => $url,
            'json' => $response->json(),
            'parameters' => $parameters,
        ]);

        return $response;
    }
}
