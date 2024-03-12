<?php

use App\Modules\InventoryReservations\src\EventServiceProviderBase;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        EventServiceProviderBase::uninstallModule();
    }
};
