<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use Illuminate\Http\JsonResponse;

/**
 * Class ManifestController.
 */
class ManifestController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $config = Configuration::first();

        return response()->json([
            'name'       => 'Product Management ' . $config->business_name,
            'short_name' => 'ShipTown ' . $config->business_name,
            "description" => "Helps businesses increase profits by simplifying and automating order fulfilment process",
            "categories" => ["ecommerce", "business", "productivity", "utilities"],
            'icons'      => [
                [
                    'src'   => '/img/icons/android-chrome-192x192.png',
                    'sizes' => '192x192',
                    'type'  => 'image/png',
                ],
                [
                    'src'   => '/img/icons/android-chrome-512x512.png',
                    'sizes' => '512x512',
                    'type'  => 'image/png',
                ],
            ],
            'start_url'        => '/index.php',
            'display'          => 'standalone',
            'background_color' => '#348fdc',
            'theme_color'      => '#348fdc',
        ]);
    }
}
