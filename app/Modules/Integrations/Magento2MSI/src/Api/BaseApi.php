<?php

namespace App\Modules\Integrations\Magento2MSI\src\Api;

use Grayloon\Magento\Api\AbstractApi;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

class BaseApi extends AbstractApi
{
    protected function get($path, $parameters = []): ?Response
    {
        try {
            $response = parent::get($path, $parameters);
        } catch (\Exception $e) {
            Log::error(implode(' ', [
                'MAGENTO2API GET',
                $path,
                $e->getMessage()
            ]), [
                'response' => $e->getMessage(),
                'url' => $this->constructRequest() . $path,
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
                'url' => $this->constructRequest() . $path,
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
            'url' => $this->constructRequest() . $path,
            'path' => $path,
            'json' => $response->json(),
            'parameters' => $parameters,
        ]);

        return $response;
    }

    protected function post($path, $parameters = []): ?Response
    {
        try {
            $response = parent::post($path, $parameters);
        } catch (\Exception $e) {
            Log::error(implode(' ', [
                'MAGENTO2API POST',
                $path,
                $e->getMessage()
            ]), [
                'response' => $e->getMessage(),
                'url' => $this->constructRequest() . $path,
                'path' => $path,
                'parameters' => $parameters,
            ]);

            return null;
        }


        if ($response->failed()) {
            Log::error(implode(' ', [
                'MAGENTO2API POST',
                $path,
                $response->status(),
                $response->reason()
            ]), [
                'response' => implode(' ', [$response->status(), $response->reason()]),
                'url' => $this->constructRequest() . $path,
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
            'url' => $this->constructRequest() . $path,
            'path' => $path,
            'json' => $response->json(),
            'parameters' => $parameters,
        ]);

        return $response;
    }

    protected function put($path, $parameters = [])
    {
        try {
            $response = parent::put($path, $parameters);
        } catch (\Exception $e) {
            Log::error(implode(' ', [
                'MAGENTO2API PUT',
                $path,
                $e->getMessage()
            ]), [
                'response' => $e->getMessage(),
                'url' => $this->constructRequest() . $path,
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
                'url' => $this->constructRequest() . $path,
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
            'url' => $this->constructRequest() . $path,
            'path' => $path,
            'json' => $response->json(),
            'parameters' => $parameters,
        ]);

        return $response;
    }

    protected function delete($path, $parameters = [])
    {
        try {
            $response = parent::delete($path, $parameters);
        } catch (\Exception $e) {
            Log::error(implode(' ', [
                'MAGENTO2API DELETE',
                $path,
                $e->getMessage()
            ]), [
                'response' => $e->getMessage(),
                'url' => $this->constructRequest() . $path,
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
                'url' => $this->constructRequest() . $path,
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
            'url' => $this->constructRequest() . $path,
            'path' => $path,
            'json' => $response->json(),
            'parameters' => $parameters,
        ]);

        return $response;
    }
}
