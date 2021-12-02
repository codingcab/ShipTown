<?php

namespace App\Modules\DpdIreland\src;

use App\Modules\DpdIreland\src\Exceptions\AuthorizationException;
use App\Modules\DpdIreland\src\Models\DpdIreland;
use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Log;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Client.
 */
class Client
{
    const API_URL_LIVE = 'https://papi.dpd.ie';

    /**
     * DPD Pre Production API URL.
     */
    const API_URL_TEST = 'https://pre-prod-papi.dpd.ie';

    /**
     * Authorization endpoint.
     */
    const COMMON_API_AUTHORIZE = '/common/api/authorize';

    /**
     * PreAdvice API Endpoint.
     */
    const COMMON_API_PREADVICE = '/common/api/preadvice';

    /**
     * Cache key name used for caching authorization.
     */
    const AUTHORIZATION_CACHE_KEY = 'dpd.authorization';

    /**
     * @return string
     */
    private static function getBaseUrl(): string
    {
        $config = DpdIreland::firstOrFail();

        return $config->live ? self::API_URL_LIVE : self::API_URL_TEST;
    }

    /**
     * @param string $xml
     *
     * @return string
     * @throws GuzzleException
     */
    public static function postXml(string $xml): string
    {
        $options = [
            'headers' => [
                'Authorization' => 'Bearer '.self::getAuthorizationToken(),
                'Content-Type'  => 'application/xml; charset=UTF8',
                'Accept'        => 'application/xml',
            ],
            'body' => $xml,
        ];

        $response = self::getGuzzleClient()->post(self::COMMON_API_PREADVICE, $options);
        $response_content = $response->getBody()->getContents();

        Log::debug('API REQUEST', [
            'service' => 'DPD-IRL',
            'response' => $response_content,
            'request' => $options
        ]);

        return $response_content;
    }

    /**
     * @return mixed
     */
    private static function getAuthorizationToken()
    {
        $authorizationToken = self::getCachedAuthorization();

        return $authorizationToken['authorization_response']['AccessToken'];
    }

    /**
     * Using cache we will not need to reauthorize every time.
     *
     * @return array
     */
    public static function getCachedAuthorization(): array
    {
        $cachedAuthorization = Cache::get(self::AUTHORIZATION_CACHE_KEY);

        if ($cachedAuthorization) {
            $cachedAuthorization['from_cache'] = true;

            return $cachedAuthorization;
        }

        $authorization = self::getAuthorization();

        Cache::put(self::AUTHORIZATION_CACHE_KEY, $authorization, 86400);

        return $authorization;
    }

    public static function forceAuthorization(): array
    {
        self::clearCache();

        return self::getCachedAuthorization();
    }

    /**
     * @return array
     * @throws AuthorizationException
     */
    private static function getAuthorization(): array
    {
        self::clearCache();

        $config = DpdIreland::firstOrFail();

        $body = [
            'User'     => $config->user,
            'Password' => $config->password,
            'Type'     => 'CUST',
        ];

        $headers = [
            'Authorization' => 'Bearer '.$config->token,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];

        $authorizationResponse = self::getGuzzleClient()->post(self::COMMON_API_AUTHORIZE, [
            'headers' => $headers,
            'json'    => $body,
        ]);

        $authorization = json_decode($authorizationResponse->getBody()->getContents(), true);

        if ($authorization['Status'] === 'FAIL') {
            throw new AuthorizationException($authorization['Code'].' : '.$authorization['Reason']);
        }

        return [
            'from_cache'             => false,
            'authorization_time'     => Carbon::now(),
            'authorization_response' => $authorization,
        ];
    }

    /**
     * @return GuzzleClient
     */
    public static function getGuzzleClient(): GuzzleClient
    {
        return new GuzzleClient([
            'base_uri'   => self::getBaseUrl(),
            'timeout'    => 60,
            'exceptions' => true,
        ]);
    }

    public static function clearCache(): void
    {
        Cache::forget(self::AUTHORIZATION_CACHE_KEY);
    }
}
