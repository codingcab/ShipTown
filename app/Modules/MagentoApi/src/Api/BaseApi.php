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
            Log::error('MAGENTO2API GET '. $path, [
                'exception_code'  => $e->getCode(),
                'exception_message' => $e->getMessage(),
                'url' => $url,
                'path' => $path,
                'parameters' => $parameters,
            ]);

            return null;
        }

        if ($response->failed()) {
            Log::error('MAGENTO2API GET '. $path, [
                'status' => $response->status(),
                'reason' => $response->reason(),
                'url' => $url,
                'path' => $path,
                'json' => $response->json(),
                'parameters' => $parameters,
            ]);

            return $response;
        }

        Log::debug('MAGENTO2API GET '. $path, [
            'status' => $response->status(),
            'reason' => $response->reason(),
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
            Log::error('MAGENTO2API POST '. $path, [
                'exception_code'  => $e->getCode(),
                'exception_message' => $e->getMessage(),
                'url' => $url,
                'path' => $path,
                'parameters' => $parameters,
            ]);

            return null;
        }

        if ($response->failed()) {
            Log::warning('MAGENTO2API POST '. $path, [
                'status' => $response->status(),
                'reason' => $response->reason(),
                'url' => $url,
                'path' => $path,
                'json' => $response->json(),
                'parameters' => $parameters,
            ]);

            return $response;
        }

        Log::debug('MAGENTO2API POST '. $path, [
            'status' => $response->status(),
            'reason' => $response->reason(),
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
            Log::error('MAGENTO2API PUT '. $path, [
                'exception_code'  => $e->getCode(),
                'exception_message' => $e->getMessage(),
                'url' => $url,
                'path' => $path,
                'parameters' => $parameters,
            ]);

            return null;
        }

        if ($response->failed()) {
            Log::error('MAGENTO2API PUT '. $path, [
                'status' => $response->status(),
                'reason' => $response->reason(),
                'url' => $url,
                'path' => $path,
                'json' => $response->json(),
                'parameters' => $parameters,
            ]);

            return $response;
        }

        Log::debug('MAGENTO2API PUT '. $path, [
            'status' => $response->status(),
            'reason' => $response->reason(),
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
            Log::error('MAGENTO2API DELETE '. $path, [
                'exception_code'  => $e->getCode(),
                'exception_message' => $e->getMessage(),
                'url' => $url,
                'path' => $path,
                'parameters' => $parameters,
            ]);

            return null;
        }

        if ($response->failed()) {
            Log::error('MAGENTO2API DELETE '. $path, [
                'status' => $response->status(),
                'reason' => $response->reason(),
                'url' => $url,
                'path' => $path,
                'json' => $response->json(),
                'parameters' => $parameters,
            ]);

            return $response;
        }

        Log::debug('MAGENTO2API DELETE '. $path, [
            'status' => $response->status(),
            'reason' => $response->reason(),
            'url' => $url,
            'path' => $path,
            'json' => $response->json(),
            'parameters' => $parameters,
        ]);

        return $response;
    }
}
