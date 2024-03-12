<?php

namespace App\Modules\ActiveOrdersInventoryReservations\src\Http\Controllers\Api\Modules;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryReservationsConfigurationIndexRequest;
use App\Http\Requests\InventoryReservationsConfigurationUpdateRequest;
use App\Modules\ActiveOrdersInventoryReservations\src\Http\Resources\ConfigurationResource;
use App\Modules\ActiveOrdersInventoryReservations\src\Models\Configuration;

class ActiveOrdersInventoryReservationsController extends Controller
{
    public function index(InventoryReservationsConfigurationIndexRequest $request): ConfigurationResource
    {
        $configuration = Configuration::query()->firstOrCreate();

        return new ConfigurationResource($configuration);
    }

    public function update(InventoryReservationsConfigurationUpdateRequest $request, $id): ConfigurationResource
    {
        $configuration = Configuration::findOrFail($id);
        $configuration->update($request->validated());

        return new ConfigurationResource($configuration);
    }
}
