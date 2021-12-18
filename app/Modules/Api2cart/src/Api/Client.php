<?php

namespace App\Modules\Api2cart\src\Api;

use App\Modules\Api2cart\src\Exceptions\RequestException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class Client
{
    /**
     * @param string $store_key
     * @param string $uri
     * @param array  $params
     *
     * @throws RequestException
     *
     * @return RequestResponse
     */
    public static function GET(string $store_key, string $uri, array $params): RequestResponse
    {
        $query = [
            'api_key'   => self::getApiKey(),
            'store_key' => $store_key,
        ];

        $query = array_merge($query, $params);

        try {
            $response = new RequestResponse(
                self::getGuzzleClient()->get($uri, ['query' => $query])
            );
        } catch (GuzzleException $exception) {
            Log::warning('Api2cart: GET failed', [
                'uri' => $uri,
                'exception' => [
                    'code' => $exception->getCode(),
                    'message' => $exception->getMessage(),
                ],
                'query' => $query
            ]);
        }


        // hide sensitive information
        $query['api_key'] = '***';
        $query['store_key'] = '***';

        // log query
        logger('GET', [
            'uri'      => $uri,
            'query'    => $query,
            'response' => [
                'status_code' => $response->getResponseRaw()->getStatusCode(),
                'content' => $response->getAsJson(),
            ],
        ]);

        if ($response->isNotSuccess()) {
            Log::warning('Api2cart: GET failed', [
                'uri' => $uri,
                'response' => [
                    'code' => $response->getReturnCode(),
                    'message' => $response->getReturnMessage(),
                ],
                'query' => $query
            ]);
            throw new RequestException($response->getReturnMessage(), $response->getReturnCode());
        }

        return $response;
    }

    /**
     * @param string $store_key
     * @param string $uri
     * @param array  $data
     *
     * @throws RequestException|GuzzleException
     *
     * @return RequestResponse
     */
    public static function POST(string $store_key, string $uri, array $data): RequestResponse
    {
        $query = [
            'api_key'   => self::getApiKey(),
            'store_key' => $store_key,
        ];

        $response = new RequestResponse(
            self::getGuzzleClient()->post($uri, [
                'query' => $query,
                'json'  => $data,
            ])
        );

        if ($response->isNotSuccess()) {
            throw new RequestException($response->getReturnMessage(), $response->getReturnCode());
        }

        // hide sensitive information
        $query['api_key'] = '***';
        $query['store_key'] = '***';

        // log query
        logger('POST', [
            'uri'      => $uri,
            'query'    => $query,
            'json'     => $data,
            'response' => [
                'status_code' => $response->getResponseRaw()->getStatusCode(),
            ],
        ]);

        return $response;
    }

    /**
     * @param string $store_key
     * @param string $uri
     * @param array  $params
     *
     * @throws GuzzleException
     *
     * @return RequestResponse
     */
    public static function DELETE(string $store_key, string $uri, array $params): RequestResponse
    {
        $query = [
            'api_key'   => self::getApiKey(),
            'store_key' => $store_key,
        ];

        $query = array_merge($query, $params);

        $response = self::getGuzzleClient()->delete($uri, ['query' => $query]);

        return new RequestResponse($response);
    }

    /**
     * @return GuzzleClient
     */
    public static function getGuzzleClient(): GuzzleClient
    {
        return new GuzzleClient([
            'base_uri'   => 'https://api.api2cart.com/v1.1/',
            'timeout'    => 60,
            'exceptions' => true,
        ]);
    }

    /**
     * @return string
     */
    public static function getApiKey(): string
    {
        return config('app.api2cart_api_key');
    }
}
