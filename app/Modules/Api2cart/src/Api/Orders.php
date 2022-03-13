<?php

namespace App\Modules\Api2cart\src\Api;

use App\Modules\Api2cart\src\Exceptions\RequestException;
use Exception;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

/**
 * Class Orders.
 */
class Orders extends Entity
{
    /**
     * @param string $store_key
     * @param array $params
     *
     * @return array|null
     * @throws GuzzleException
     */
    public static function get(string $store_key, array $params): ?array
    {
        try {
            $response = Client::GET($store_key, 'order.list.json', $params);
        } catch (ConnectException $connectException) {
            Log::warning('Failed to connect to API2CART', [
                $connectException->getMessage()
            ]);
            return null;
        }

        if ($response->isSuccess()) {
            Log::debug('Fetched orders', [
                'source' => 'API2CART',
                'count' => $response->getResult()['orders_count'],
            ]);

            return $response->getResult()['order'];
        }

        return null;
    }

    /**
     * @param string $store_key
     * @param array $params
     * @return RequestResponse
     * @throws GuzzleException
     */
    public static function list(string $store_key, array $params): RequestResponse
    {
        return Client::GET($store_key, 'order.list.json', $params);
    }

    /**
     * @param $store_key
     * @param $params
     * @return RequestResponse
     * @throws GuzzleException
     */
    public static function update($store_key, $params): RequestResponse
    {
        return Client::POST($store_key, 'order.update.json', $params);
    }

    /**
     * @param $store_key
     * @param $params
     * @return RequestResponse
     * @throws GuzzleException
     */
    public static function statuses($store_key, $params): RequestResponse
    {
        return Client::GET($store_key, 'order.status.list.json', $params);
    }
}
