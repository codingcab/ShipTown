<?php

namespace App\Console\Commands;

use App\Mail\OrderMail;
use App\Models\Configuration;
use App\Models\MailTemplate;
use App\Models\NavigationMenu;
use App\Models\Warehouse;
use App\Modules\Automations\src\Actions\Order\SetStatusCodeAction;
use App\Modules\Automations\src\Conditions\Order\IsFullyPackedCondition;
use App\Modules\Automations\src\Conditions\Order\IsFullyPickedCondition;
use App\Modules\Automations\src\Conditions\Order\LineCountEqualsCondition;
use App\Modules\Automations\src\Conditions\Order\StatusCodeEqualsCondition;
use App\Modules\Automations\src\Models\Automation;
use App\Modules\AutoRestockLevels\src\AutoRestockLevelsServiceProvider;
use App\Modules\DataCollector\src\DataCollectorServiceProvider;
use App\Modules\InventoryMovements\src\InventoryMovementsServiceProvider;
use App\Modules\InventoryMovementsStatistics\src\InventoryMovementsStatisticsServiceProvider;
use App\Modules\InventoryQuantityIncoming\src\InventoryQuantityIncomingServiceProvider;
use App\Modules\InventoryReservations\src\EventServiceProviderBase;
use App\Modules\InventoryTotals\src\InventoryTotalsServiceProvider;
use App\Modules\NonInventoryProductTag\src\NonInventoryProductTagServiceProvider;
use App\Modules\QueueMonitor\src\QueueMonitorServiceProvider;
use App\Modules\Slack\src\SlackServiceProvider;
use App\Modules\StocktakeSuggestions\src\StocktakeSuggestionsServiceProvider;
use App\Modules\Telescope\src\TelescopeModuleServiceProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 *
 */
class AppInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs the application and generates encryption keys';

    public function handle(): int
    {
        if (env('PASSPORT_PRIVATE_KEY', '') === '') {
            $this->info('Generating passport keys');
            $this->createPassportKeys();
        }

        if (env('APP_KEY', '') === '') {
            $this->info('Generating application key');
            $this->call('key:generate');
        }

        $this->createDefaultConfigurationRecord();
        $this->createDefaultUserRoles();
        $this->createDefaultNavigationLinks();
        $this->createShipmentConfirmationNotificationTemplate();
        $this->createReadyForCollectionNotificationTemplate();
        $this->createDefaultMailTemplateShipmentNotification();
        $this->createDefaultMailTemplateOversoldProduct();

//        $this->createPaidToPickedAutomation();
        $this->createPaidToCompleteAutomation();
        $this->createPickedToCompleteAutomation();
        $this->createPaidToSingleLineOrdersAutomation();

        \App\Services\ModulesService::updateModulesTable();

        Configuration::create([
            'warehouse_id' => Warehouse::query()->firstOrCreate(['code' => '999'], ['name' => '999'])->id,
        ]);

        StocktakeSuggestionsServiceProvider::installModule();
        AutoRestockLevelsServiceProvider::installModule();
        InventoryQuantityIncomingServiceProvider::installModule();
        DataCollectorServiceProvider::installModule();
        NonInventoryProductTagServiceProvider::installModule();
        QueueMonitorServiceProvider::installModule();
        TelescopeModuleServiceProvider::installModule();
        InventoryMovementsStatisticsServiceProvider::installModule();
        SlackServiceProvider::installModule();
        EventServiceProviderBase::installModule();
        InventoryMovementsServiceProvider::installModule();


        StocktakeSuggestionsServiceProvider::enableModule();
        InventoryMovementsStatisticsServiceProvider::enableModule();
        EventServiceProviderBase::enableModule();
        InventoryMovementsServiceProvider::enableModule();
        InventoryTotalsServiceProvider::enableModule();
        InventoryMovementsServiceProvider::enableModule();

        return 0;
    }

    private function setVariable(string $key, string $value): void
    {
        $path = base_path('.env');

        if (file_exists($path)) {
            file_put_contents($path, str_replace(
                $key . '=' . env($key),
                $key . '=' . $value,
                file_get_contents($path)
            ));
        }
    }

    private function createPassportKeys(): void
    {
        $this->call('passport:keys', [
            '--force' => true,
        ]);

        $this->setVariable('PASSPORT_PRIVATE_KEY', implode('', ['"', Storage::get('oauth-private.key'), '"']));
        $this->setVariable('PASSPORT_PUBLIC_KEY', implode('', ['"', Storage::get('oauth-public.key'), '"']));
        unlink(storage_path('oauth-private.key'));
        unlink(storage_path('oauth-public.key'));

        $this->info("Passport encryption keys generated successfully");
    }

    private function createDefaultNavigationLinks(): void
    {
        NavigationMenu::query()->create([
            'name' => 'Status: paid',
            'url' => '/picklist?order.status_code=paid',
            'group' => 'picklist'
        ]);

        NavigationMenu::query()->create([
            'name' => 'Status: paid',
            'url' => '/autopilot/packlist?status=paid',
            'group' => 'packlist'
        ]);

        NavigationMenu::query()->create([
            'name' => 'Status: picked',
            'url' => '/autopilot/packlist?status=picked',
            'group' => 'packlist'
        ]);
    }

    private function createPaidToCompleteAutomation(): void
    {
        /** @var Automation $automation */
        $automation = Automation::create([
            'name' => '"paid" to "complete"',
            'priority' => 90,
            'enabled' => false,
        ]);

        $automation->conditions()->create([
            'condition_class' => StatusCodeEqualsCondition::class,
            'condition_value' => 'paid'
        ]);

        $automation->conditions()->create([
            'condition_class' => IsFullyPackedCondition::class,
            'condition_value' => 'True'
        ]);

        $automation->actions()->create([
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'complete'
        ]);

        $automation->update(['enabled' => true]);
    }

    private function createPickedToCompleteAutomation(): void
    {
        /** @var Automation $automation */
        $automation = Automation::create([
            'name' => '"picked" to "complete"',
            'priority' => 91,
            'enabled' => false,
        ]);

        $automation->conditions()->create([
            'condition_class' => StatusCodeEqualsCondition::class,
            'condition_value' => 'picked'
        ]);

        $automation->conditions()->create([
            'condition_class' => IsFullyPackedCondition::class,
            'condition_value' => 'True'
        ]);

        $automation->actions()->create([
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'complete'
        ]);

        $automation->update(['enabled' => true]);
    }

    private function createPaidToPickedAutomation(): void
    {
        /** @var Automation $automation */
        $automation = Automation::create([
            'name' => '"paid" to "packing"',
            'priority' => 10,
            'enabled' => false,
        ]);

        $automation->conditions()->create([
            'condition_class' => StatusCodeEqualsCondition::class,
            'condition_value' => 'paid'
        ]);

        $automation->conditions()->create([
            'condition_class' => IsFullyPickedCondition::class,
            'condition_value' => 'True'
        ]);

        $automation->actions()->create([
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'packing'
        ]);

        $automation->update(['enabled' => true]);
    }

    private function createDefaultUserRoles(): void
    {
        Role::findOrCreate('user');
        $admin = Role::findOrCreate('admin');

        $defaultAdminPermissions = ['manage users', 'list users', 'invite users', 'list roles'];

        foreach ($defaultAdminPermissions as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName]);
            $admin->givePermissionTo($permission);
        }
    }

    private function createDefaultConfigurationRecord(): void
    {
        Configuration::create([]);
    }

    private function createDefaultMailTemplateShipmentNotification(): void
    {
        MailTemplate::create([
            'mailable' => \App\Mail\ShipmentConfirmationMail::class,
            'code' =>  'module_shipment_confirmation',
            'subject' => 'Your Order #{{ variables.order.order_number }} has been Shipped!',
            'html_template' => '
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml"
          xmlns="http://www.w3.org/1999/xhtml"
          style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
    <head>
        <meta name="viewport" content="width=device-width" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>We shipped your order!</title>

        <style>img {
            max-width: 100%;
        }
        body {
            -webkit-font-smoothing: antialiased;
            -webkit-text-size-adjust: none;
            width: 100% !important;
            height: 100%;
            line-height: 1.6;
        }
        body {
            background-color: #f6f6f6;
        }
        @media only screen and (max-width: 640px) {
            h1 {
                font-weight: 600 !important; margin: 20px 0 5px !important;
            }
            h2 {
                font-weight: 600 !important; margin: 20px 0 5px !important;
            }
            h3 {
                font-weight: 600 !important; margin: 20px 0 5px !important;
            }
            h4 {
                font-weight: 600 !important; margin: 20px 0 5px !important;
            }
            h1 {
                font-size: 22px !important;
            }
            h2 {
                font-size: 18px !important;
            }
            h3 {
                font-size: 16px !important;
            }
            .container {
                width: 100% !important;
            }
            .content {
                padding: 10px !important;
            }
            .content-wrapper {
                padding: 10px !important;
            }
            .invoice {
                width: 100% !important;
            }
        }
        </style></head>

    <body style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; line-height: 1.6; margin: 0; padding: 0;" bgcolor="#f6f6f6">

    <table class="body-wrap" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; margin: 0; padding: 0;" bgcolor="#f6f6f6">
        <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
            <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;" valign="top"></td>
            <td class="container" width="600" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 0;" valign="top">
                <div class="content" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; max-width: 600px; display: block; margin: 0 auto; padding: 20px;">
                    <table class="main" width="100%" cellpadding="0" cellspacing="0" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-radius: 3px; margin: 0; padding: 0; border: 1px solid #e9e9e9;" bgcolor="#fff">
                        <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                            <td class="content-wrap aligncenter" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 20px;" align="center" valign="top">
                                <table width="100%" cellpadding="0" cellspacing="0" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                    <!-------- logo -------->
    <!--                                <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">-->
    <!--                                    <td class="content-block" style="text-align: center; align-content: center; font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">-->
    <!--                                        <a href="" target="_blank">-->
    <!--                                            <img src="" alt="logo">-->
    <!--                                        </a>-->
    <!--                                    </td>-->
    <!--                                </tr>-->
                                    <!-------- logo -------->
                                    <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                        <td class="content-block" style="text-align: center; align-content: center; font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">
                                            <h2 style="font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, &quot;Lucida Grande&quot;, sans-serif; box-sizing: border-box; font-size: 24px; color: #000; line-height: 1.2; font-weight: 400; margin: 40px 0 0; padding: 0;">
                                                We shipped your order!<br>
                                                #{{ variables.order.order_number }}
                                            </h2>
                                        </td>
                                    </tr>
                                    <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                        <td class="content-block" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">
                                            <table class="invoice" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; text-align: left; width: 80%; margin: 40px auto; padding: 0;">
                                                <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                    <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 5px 0;" valign="top">
                                                        Tracking Information:<br style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;" />
                                                    </td>
                                                </tr>
                                                <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                    <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 5px 0;" valign="top">
                                                        <table class="invoice-items" cellpadding="0" cellspacing="0" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; margin: 0; padding: 0;">

                                                            {{#variables.shipments}}

                                                            <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                                <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" valign="top">
                                                                    {{ carrier }}
                                                                </td>
                                                                <td class="alignright" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" align="right" valign="top">
                                                                    <a href="{{ tracking_url }}" target="_blank">
                                                                        {{ shipping_number }}
                                                                    </a>
                                                                </td>
                                                            </tr>

                                                            {{/variables.shipments}}
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                        <td class="content-block" style="text-align: center; align-content: center; font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">
                                            <p>
                                                Please note that any tracking information above <br>
                                                may not update until this evening.
                                            </p>
                                            <p>
                                                If you have any questions, please feel free <br>
                                                to email us at <a href="mailto:support@ship.town?subject=Order #{{ variables.order.order_number }} Enquiry">no-reply@products.management</a></br>
                                                or call us on <b>+353 (1) 1234567</b><br>
                                            </p>
                                            <p>Thank you again for your business.</p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <div class="footer" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; clear: both; color: #999; margin: 0; padding: 20px;">
                        <table width="100%" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                            <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                <td class="aligncenter content-block" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 12px; margin: 0; padding: 0 0 20px;" align="center" valign="top">
                                    Questions? Email
                                    <a href="mailto:" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 12px; color: #999; text-decoration: underline; margin: 0; padding: 0;">
                                        no-reply@products.management
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </div></div>
            </td>
            <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;" valign="top"></td>
        </tr>
    </table>

    </body>
    </html>
        '
        ]);
    }

    public function createDefaultMailTemplateOversoldProduct(): void
    {
        MailTemplate::create([
            'mailable' => \App\Mail\OversoldProductMail::class,
            'code' => 'module_oversold_product_mail',
            'subject' => 'Product Oversold - ({{ variables.product.name }})',
            'html_template' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
<head>
    <meta name="viewport" content="width=device-width" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Product Oversold!</title>

    <style>img {
        max-width: 100%;
    }
    body {
        -webkit-font-smoothing: antialiased;
        -webkit-text-size-adjust: none;
        width: 100% !important;
        height: 100%;
        line-height: 1.6;
    }
    body {
        background-color: #f6f6f6;
    }
    @media only screen and (max-width: 640px) {
        h1 {
            font-weight: 600 !important; margin: 20px 0 5px !important;
        }
        h2 {
            font-weight: 600 !important; margin: 20px 0 5px !important;
        }
        h3 {
            font-weight: 600 !important; margin: 20px 0 5px !important;
        }
        h4 {
            font-weight: 600 !important; margin: 20px 0 5px !important;
        }
        h1 {
            font-size: 22px !important;
        }
        h2 {
            font-size: 18px !important;
        }
        h3 {
            font-size: 16px !important;
        }
        .container {
            width: 100% !important;
        }
        .content {
            padding: 10px !important;
        }
        .content-wrapper {
            padding: 10px !important;
        }
        .invoice {
            width: 100% !important;
        }
    }
    </style></head>

<body style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; line-height: 1.6; margin: 0; padding: 0;" bgcolor="#f6f6f6">

<table class="body-wrap" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; margin: 0; padding: 0;" bgcolor="#f6f6f6">
    <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
        <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;" valign="top"></td>
        <td class="container" width="600" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 0;" valign="top">
            <div class="content" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; max-width: 600px; display: block; margin: 0 auto; padding: 20px;">
                <table class="main" width="100%" cellpadding="0" cellspacing="0" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-radius: 3px; margin: 0; padding: 0; border: 1px solid #e9e9e9;" bgcolor="#fff">
                    <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                        <td class="content-wrap aligncenter" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 20px;" align="center" valign="top">
                            <table width="100%" cellpadding="0" cellspacing="0" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                <!-------- logo -------->
<!--                                <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">-->
<!--                                    <td class="content-block" style="text-align: center; align-content: center; font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">-->
<!--                                        <a href="" target="_blank">-->
<!--                                            <img src="" alt="logo">-->
<!--                                        </a>-->
<!--                                    </td>-->
<!--                                </tr>-->
                                <!-------- logo -------->
                                <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                    <td class="content-block" style="text-align: center; align-content: center; font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">
                                        <h2 style="font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, &quot;Lucida Grande&quot;, sans-serif; box-sizing: border-box; font-size: 24px; color: #000; line-height: 1.2; font-weight: 400; margin: 40px 0 0; padding: 0;">
                                            Oversold Product Detected<br>
                                        </h2>
                                    </td>
                                </tr>
                                <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                    <td class="content-block" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">
                                        <table class="invoice" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; text-align: left; width: 80%; margin: 40px auto; padding: 0;">
                                            <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 5px 0;" valign="top">
                                                    Product Information:<br style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;" />
                                                </td>
                                            </tr>
                                            <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 5px 0;" valign="top">
                                                    <table class="invoice-items" cellpadding="0" cellspacing="0" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; margin: 0; padding: 0;">

                                                        <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                            <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" valign="top">
                                                                SKU
                                                            </td>
                                                            <td class="alignright" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" align="right" valign="top">
                                                                <a href="/products?search={{ variables.product.sku }}">{{ variables.product.sku }}</a>
                                                            </td>
                                                        </tr>

                                                        <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                            <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" valign="top">
                                                                Name
                                                            </td>
                                                            <td class="alignright" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" align="right" valign="top">
                                                                {{ variables.product.name }}
                                                            </td>
                                                        </tr>

                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                    <td class="content-block" style="text-align: center; align-content: center; font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">
                                        <p>
                                            <!-------- footer -------->
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <div class="footer" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; clear: both; color: #999; margin: 0; padding: 20px;">
                    <table width="100%" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                        <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                            <td class="aligncenter content-block" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 12px; margin: 0; padding: 0 0 20px;" align="center" valign="top">
                                Questions? Email
                                <a href="mailto:" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 12px; color: #999; text-decoration: underline; margin: 0; padding: 0;">
                                    support@products.management
                                </a>
                            </td>
                        </tr>
                    </table>
                </div></div>
        </td>
        <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;" valign="top"></td>
    </tr>
</table>

</body>
</html>'
        ]);
    }

    private function createShipmentConfirmationNotificationTemplate()
    {
        MailTemplate::query()->firstOrCreate(['code' => 'shipment_confirmation'], [
            'mailable' => OrderMail::class,
            'subject' => 'Your Order #{{ variables.order.order_number }} has been Shipped!',
            'html_template' => '
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml"
          xmlns="http://www.w3.org/1999/xhtml"
          style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
    <head>
        <meta name="viewport" content="width=device-width" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>We shipped your order!</title>

        <style>img {
            max-width: 100%;
        }
        body {
            -webkit-font-smoothing: antialiased;
            -webkit-text-size-adjust: none;
            width: 100% !important;
            height: 100%;
            line-height: 1.6;
        }
        body {
            background-color: #f6f6f6;
        }
        @media only screen and (max-width: 640px) {
            h1 {
                font-weight: 600 !important; margin: 20px 0 5px !important;
            }
            h2 {
                font-weight: 600 !important; margin: 20px 0 5px !important;
            }
            h3 {
                font-weight: 600 !important; margin: 20px 0 5px !important;
            }
            h4 {
                font-weight: 600 !important; margin: 20px 0 5px !important;
            }
            h1 {
                font-size: 22px !important;
            }
            h2 {
                font-size: 18px !important;
            }
            h3 {
                font-size: 16px !important;
            }
            .container {
                width: 100% !important;
            }
            .content {
                padding: 10px !important;
            }
            .content-wrapper {
                padding: 10px !important;
            }
            .invoice {
                width: 100% !important;
            }
        }
        </style></head>

    <body style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; line-height: 1.6; margin: 0; padding: 0;" bgcolor="#f6f6f6">

    <table class="body-wrap" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; margin: 0; padding: 0;" bgcolor="#f6f6f6">
        <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
            <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;" valign="top"></td>
            <td class="container" width="600" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 0;" valign="top">
                <div class="content" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; max-width: 600px; display: block; margin: 0 auto; padding: 20px;">
                    <table class="main" width="100%" cellpadding="0" cellspacing="0" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-radius: 3px; margin: 0; padding: 0; border: 1px solid #e9e9e9;" bgcolor="#fff">
                        <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                            <td class="content-wrap aligncenter" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 20px;" align="center" valign="top">
                                <table width="100%" cellpadding="0" cellspacing="0" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                    <!-------- logo -------->
    <!--                                <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">-->
    <!--                                    <td class="content-block" style="text-align: center; align-content: center; font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">-->
    <!--                                        <a href="" target="_blank">-->
    <!--                                            <img src="" alt="logo">-->
    <!--                                        </a>-->
    <!--                                    </td>-->
    <!--                                </tr>-->
                                    <!-------- logo -------->
                                    <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                        <td class="content-block" style="text-align: center; align-content: center; font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">
                                            <h2 style="font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, &quot;Lucida Grande&quot;, sans-serif; box-sizing: border-box; font-size: 24px; color: #000; line-height: 1.2; font-weight: 400; margin: 40px 0 0; padding: 0;">
                                                We shipped your order!<br>
                                                #{{ variables.order.order_number }}
                                            </h2>
                                        </td>
                                    </tr>
                                    <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                        <td class="content-block" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">
                                            <table class="invoice" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; text-align: left; width: 80%; margin: 40px auto; padding: 0;">
                                                <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                    <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 5px 0;" valign="top">
                                                        Tracking Information:<br style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;" />
                                                    </td>
                                                </tr>
                                                <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                    <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 5px 0;" valign="top">
                                                        <table class="invoice-items" cellpadding="0" cellspacing="0" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; margin: 0; padding: 0;">

                                                            {{#variables.shipments}}

                                                            <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                                                <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" valign="top">
                                                                    {{ carrier }}
                                                                </td>
                                                                <td class="alignright" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-top-width: 1px; border-top-color: #eee; border-top-style: solid; margin: 0; padding: 5px 0;" align="right" valign="top">
                                                                    <a href="{{ tracking_url }}" target="_blank">
                                                                        {{ shipping_number }}
                                                                    </a>
                                                                </td>
                                                            </tr>

                                                            {{/variables.shipments}}
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                        <td class="content-block" style="text-align: center; align-content: center; font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">
                                            <p>
                                                Please note that any tracking information above <br>
                                                may not update until this evening.
                                            </p>
                                            <p>
                                                If you have any questions, please feel free <br>
                                                to email us at <a href="mailto:support@ship.town?subject=Order #{{ variables.order.order_number }} Enquiry">no-reply@products.management</a></br>
                                                or call us on <b>+353 (1) 1234567</b><br>
                                            </p>
                                            <p>Thank you again for your business.</p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <div class="footer" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; clear: both; color: #999; margin: 0; padding: 20px;">
                        <table width="100%" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                            <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                <td class="aligncenter content-block" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 12px; margin: 0; padding: 0 0 20px;" align="center" valign="top">
                                    Questions? Email
                                    <a href="mailto:" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 12px; color: #999; text-decoration: underline; margin: 0; padding: 0;">
                                        no-reply@products.management
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </div></div>
            </td>
            <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;" valign="top"></td>
        </tr>
    </table>

    </body>
    </html>
        '
        ]);
    }

    private function createReadyForCollectionNotificationTemplate()
    {
        MailTemplate::query()->firstOrCreate(['code' => 'ready_for_collection_notification'], [
            'mailable' => OrderMail::class,
            'subject' => 'Your Order #{{ variables.order.order_number }} is ready for Collection!',
            'html_template' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml"
          xmlns="http://www.w3.org/1999/xhtml"
          style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
    <head>
        <meta name="viewport" content="width=device-width" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>We shipped your order!</title>

        <style>img {
            max-width: 100%;
        }
        body {
            -webkit-font-smoothing: antialiased;
            -webkit-text-size-adjust: none;
            width: 100% !important;
            height: 100%;
            line-height: 1.6;
        }
        body {
            background-color: #f6f6f6;
        }
        @media only screen and (max-width: 640px) {
            h1 {
                font-weight: 600 !important; margin: 20px 0 5px !important;
            }
            h2 {
                font-weight: 600 !important; margin: 20px 0 5px !important;
            }
            h3 {
                font-weight: 600 !important; margin: 20px 0 5px !important;
            }
            h4 {
                font-weight: 600 !important; margin: 20px 0 5px !important;
            }
            h1 {
                font-size: 22px !important;
            }
            h2 {
                font-size: 18px !important;
            }
            h3 {
                font-size: 16px !important;
            }
            .container {
                width: 100% !important;
            }
            .content {
                padding: 10px !important;
            }
            .content-wrapper {
                padding: 10px !important;
            }
            .invoice {
                width: 100% !important;
            }
        }
        </style></head>

    <body style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; line-height: 1.6; margin: 0; padding: 0;" bgcolor="#f6f6f6">

    <table class="body-wrap" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; margin: 0; padding: 0;" bgcolor="#f6f6f6">
        <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
            <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;" valign="top"></td>
            <td class="container" width="600" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 0;" valign="top">
                <div class="content" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; max-width: 600px; display: block; margin: 0 auto; padding: 20px;">
                    <table class="main" width="100%" cellpadding="0" cellspacing="0" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; border-radius: 3px; margin: 0; padding: 0; border: 1px solid #e9e9e9;" bgcolor="#fff">
                        <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                            <td class="content-wrap aligncenter" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 20px;" align="center" valign="top">
                                <table width="100%" cellpadding="0" cellspacing="0" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                    <!-------- logo -------->
    <!--                                <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">-->
    <!--                                    <td class="content-block" style="text-align: center; align-content: center; font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">-->
    <!--                                        <a href="" target="_blank">-->
    <!--                                            <img src="" alt="logo">-->
    <!--                                        </a>-->
    <!--                                    </td>-->
    <!--                                </tr>-->
                                    <!-------- logo -------->

                                    <!-------- main message -------->
                                    <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                        <td class="content-block" style="text-align: center; align-content: center; font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">
                                            <h2 style="font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, &quot;Lucida Grande&quot;, sans-serif; box-sizing: border-box; font-size: 24px; color: #000; line-height: 1.2; font-weight: 400; margin: 40px 0 0; padding: 0;">
                                                Your order is ready for collection!<br>
                                                #{{ variables.order.order_number }}
                                            </h2>
                                        </td>
                                    </tr>

                                    <!-------- footer -------->
                                    <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                        <td class="content-block" style="text-align: center; align-content: center; font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0 0 20px;" valign="top">
                                            <p>
                                                If you have any questions, please feel free <br>
                                                to email us at <a href="mailto:support@ship.town?subject=Order #{{ variables.order.order_number }} Enquiry">no-reply@products.management</a></br>
                                                or call us on <b>+353 (1) 1234567</b><br>
                                            </p>
                                            <p>Thank you again for your business.</p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <div class="footer" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; width: 100%; clear: both; color: #999; margin: 0; padding: 20px;">
                        <table width="100%" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                            <tr style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;">
                                <td class="aligncenter content-block" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 12px; margin: 0; padding: 0 0 20px;" align="center" valign="top">
                                    Questions? Email
                                    <a href="mailto:" style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 12px; color: #999; text-decoration: underline; margin: 0; padding: 0;">
                                        no-reply@products.management
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </div></div>
            </td>
            <td style="font-family: &quot;Helvetica Neue&quot;, &quot;Helvetica&quot;, Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0; padding: 0;" valign="top"></td>
        </tr>
    </table>

    </body>
    </html>
        '
        ]);
    }

    private function createPaidToSingleLineOrdersAutomation()
    {
        /** @var Automation $automation */
        $automation = Automation::create([
            'name' => '"paid" to "single_line_orders"',
            'priority' => 10,
            'enabled' => false,
        ]);

        $automation->conditions()->create([
            'condition_class' => StatusCodeEqualsCondition::class,
            'condition_value' => 'paid'
        ]);

        $automation->conditions()->create([
            'condition_class' => LineCountEqualsCondition::class,
            'condition_value' => '1'
        ]);

        $automation->actions()->create([
            'action_class' => SetStatusCodeAction::class,
            'action_value' => 'single_line_orders'
        ]);

        $automation->update(['enabled' => true]);
    }
}
