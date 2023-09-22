<?php

namespace App\Modules\MagentoApi\src\Api;

use App\Modules\MagentoApi\src\Models\MagentoConnection;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 *
 */
class BaseApi
{
    private string $baseUrl;
    private string $apiAccessToken;

    private string $version = 'V1';

    public function __construct(MagentoConnection $magentoConnection)
    {
        $this->baseUrl = $magentoConnection->base_url;
        $this->apiAccessToken = $magentoConnection->api_access_token;
    }

    public function get($path, $parameters = []): ?Response
    {
        $url = implode('/', [$this->baseUrl, 'rest/default', $this->version, $path]);

        try {
            $response = Http::withToken($this->apiAccessToken)->acceptJson()->get($url, $parameters);
        } catch (Exception $e) {
            Log::error(implode(' ', [
                'MAGENTO2API GET',
                $path,
                $e->getCode()
            ]), [
                'response' => $e->getMessage(),
                'url' => $url,
                'path' => $path,
                'parameters' => $parameters,
            ]);

            return null;
        }

        if ($response->failed()) {
            Log::error(implode(' ', [
                'MAGENTO2API GET',
                $path,
                $response->status(),
                $response->reason()
            ]), [
                'response' => implode(' ', [$response->status(), $response->reason()]),
                'url' => $url,
                'path' => $path,
                'json' => $response->json(),
                'parameters' => $parameters,
            ]);

            return $response;
        }

        Log::debug(implode(' ', [
            'MAGENTO2API GET',
            $path,
            $response->status(),
            $response->reason()
        ]), [
            'response' => implode(' ', [$response->status(), $response->reason()]),
            'url' => $url,
            'path' => $path,
            'json' => $response->json(),
            'parameters' => $parameters,
        ]);

        return $response;
    }

    public function post($path, $parameters = []): ?Response
    {
        $url = implode('/', [$this->baseUrl, 'rest/default', $this->version, $path]);

        try {
            $response = Http::withToken($this->apiAccessToken)->post($url, $parameters);
        } catch (Exception $e) {
            Log::error(implode(' ', [
                'MAGENTO2API POST',
                $path,
                $e->getCode()
            ]), [
                'response' => $e->getMessage(),
                'url' => $url,
                'path' => $path,
                'parameters' => $parameters,
            ]);

            return null;
        }

        if ($response->failed()) {
            Log::warning(implode(' ', [
                'MAGENTO2API POST',
                $path,
                $response->status(),
                $response->reason()
            ]), [
                'response' => implode(' ', [$response->status(), $response->reason()]),
                'url' => $url,
                'path' => $path,
                'json' => $response->json(),
                'parameters' => $parameters,
            ]);

            return $response;
        }

        Log::debug(implode(' ', [
            'MAGENTO2API POST',
            $path,
            $response->status(),
            $response->reason()
        ]), [
            'response' => implode(' ', [$response->status(), $response->reason()]),
            'url' => $url,
            'path' => $path,
            'json' => $response->json(),
            'parameters' => $parameters,
        ]);

        return $response;
    }

    public function put($path, $parameters = []): ?Response
    {
        $url = implode('/', [$this->baseUrl, 'rest/default', $this->version, $path]);

        try {
            $response = Http::withToken($this->apiAccessToken)->put($url, $parameters);
        } catch (Exception $e) {
            Log::error(implode(' ', [
                'MAGENTO2API PUT',
                $path,
                $e->getCode()
            ]), [
                'response' => $e->getMessage(),
                'url' => $url,
                'path' => $path,
                'parameters' => $parameters,
            ]);

            return null;
        }

        if ($response->failed()) {
            Log::error(implode(' ', [
                'MAGENTO2API PUT',
                $path,
                $response->status(),
                $response->reason()
            ]), [
                'response' => implode(' ', [$response->status(), $response->reason()]),
                'url' => $url,
                'path' => $path,
                'json' => $response->json(),
                'parameters' => $parameters,
            ]);

            return $response;
        }

        Log::debug(implode(' ', [
            'MAGENTO2API PUT',
            $path,
            $response->status(),
            $response->reason()
        ]), [
            'response' => implode(' ', [$response->status(), $response->reason()]),
            'url' => $url,
            'path' => $path,
            'json' => $response->json(),
            'parameters' => $parameters,
        ]);

        return $response;
    }

    public function delete($path, $parameters = []): ?Response
    {
        $url = implode('/', [$this->baseUrl, 'rest/default', $this->version, $path]);

        try {
            $response = Http::withToken($this->apiAccessToken)->delete($url, $parameters);
        } catch (Exception $e) {
            Log::error(implode(' ', [
                'MAGENTO2API DELETE',
                $path,
                $e->getCode()
            ]), [
                'response' => $e->getMessage(),
                'url' => $url,
                'path' => $path,
                'parameters' => $parameters,
            ]);

            return null;
        }


        if ($response->failed()) {
            Log::error(implode(' ', [
                'MAGENTO2API DELETE',
                $path,
                $response->status(),
                $response->reason()
            ]), [
                'response' => implode(' ', [$response->status(), $response->reason()]),
                'url' => $url,
                'path' => $path,
                'json' => $response->json(),
                'parameters' => $parameters,
            ]);

            return $response;
        }

        Log::debug(implode(' ', [
            'MAGENTO2API DELETE',
            $path,
            $response->status(),
            $response->reason()
        ]), [
            'response' => implode(' ', [$response->status(), $response->reason()]),
            'url' => $url,
            'path' => $path,
            'json' => $response->json(),
            'parameters' => $parameters,
        ]);

        return $response;
    }
}
