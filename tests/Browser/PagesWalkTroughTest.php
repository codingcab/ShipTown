<?php

namespace Tests\Browser;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\User;
use Facebook\WebDriver\Exception\ElementClickInterceptedException;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeOutException;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Throwable;

class PagesWalkTroughTest extends DuskTestCase
{
    private Order $order;
    private User $user;
    private int $shortDelay = 200;
    private int $longDelay = 0;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create();

        $this->user = User::factory()->create([
            'warehouse_id' => $warehouse->getKey(),
            'password' => bcrypt('password')
        ]);

        $product1 = Product::factory()->create(['sku' => '111576']);
        $product2 = Product::factory()->create(['sku' => '222957']);

        $inventory1 = Inventory::query()->where(['product_id' => $product1->getKey(), 'warehouse_id' => $warehouse->getKey()])->first();
        $inventory2 = Inventory::query()->where(['product_id' => $product2->getKey(), 'warehouse_id' => $warehouse->getKey()])->first();

        InventoryService::stocktake($inventory1, 12);
        InventoryService::stocktake($inventory2, 15);

        $this->order = Order::factory()->create(['status_code' => 'paid']);

        /** @var OrderProduct $orderProduct1 */
        $orderProduct1 = OrderProduct::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $product1->getKey(),
            'quantity_ordered' => 1
        ]);

        /** @var OrderProduct $orderProduct2 */
        $orderProduct2 = OrderProduct::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $product2->getKey(),
            'quantity_ordered' => 3
        ]);


        InventoryService::stocktake($inventory1, $orderProduct1->quantity_ordered+1);
        InventoryService::stocktake($inventory2, $orderProduct2->quantity_ordered+1);
    }

    /**
     * A Dusk test example.
     *
     * @return void
     * @throws Throwable
     */
    public function testExample()
    {
        $this->browse(function (Browser $browser) {
            $browser->disableFitOnFailure();

            $this->login($browser);
            $this->products($browser);
            $this->orders($browser);
            $this->transferIn($browser);
            $this->stocktaking($browser);
            $this->picklist($browser);
            $this->packlist($browser);
            $this->dashboard($browser);
            $this->restocking($browser);
        });
    }

    /**
     * @param Browser $browser
     * @throws ElementClickInterceptedException
     * @throws NoSuchElementException
     * @throws TimeOutException
     */
    private function packlist(Browser $browser): void
    {
        $browser->mouseover('#navToggleButton')->pause($this->shortDelay)
            ->click('#navToggleButton')->pause($this->shortDelay)
            ->mouseover('#packlists_link')->pause($this->shortDelay)
            ->clickLink('Packlist')->pause($this->shortDelay)
            ->clickLink('Status: paid')->pause($this->longDelay)
            ->assertSee('Start AutoPilot Packing')
            ->click('@startAutopilotButton')
            ->pause($this->longDelay);

        while ($this->order->orderProducts()->where('quantity_to_ship', '>', 0)->exists()) {
            /** @var OrderProduct $orderProduct */
            $orderProduct = $this->order->orderProducts()
                ->where('quantity_to_ship', '>', 0)
                ->orderBy('id')
                ->get()
                ->first();

            $browser->waitForText($orderProduct->product->sku);
            $browser->assertSee($orderProduct->product->sku);

            $browser->driver->getKeyboard()->sendKeys($orderProduct->product->sku);
            $browser->pause($this->shortDelay)
                ->keys('#barcodeInput', '{enter}')
                ->pause(1500);
        }

        $browser->pause(2000)
            ->keys('#shipping_number_input', 'CB100023444')->pause(500)
            ->keys('#shipping_number_input', '{enter}')
            ->pause($this->longDelay);
    }

    /**
     * @param Browser $browser
     */
    private function login(Browser $browser): void
    {
        $browser->visit('/')->pause(500)
            ->assertPathIs('/login')
            ->type('email', $this->user->email)->pause($this->shortDelay)
            ->type('password', 'password')->pause($this->shortDelay)
            ->press('Login')->pause($this->longDelay)
            ->assertPathBeginsWith('/dashboard')
            ->pause(100);
    }

    /**
     * @param Browser $browser
     * @throws ElementClickInterceptedException
     * @throws NoSuchElementException
     * @throws TimeOutException
     */
    private function transferIn(Browser $browser): void
    {
        $browser->mouseover('#tools_link')->pause($this->shortDelay)
            ->click('#tools_link')->pause($this->shortDelay)
            ->mouseover('#data_collector_link')->pause($this->shortDelay)
            ->clickLink('Data Collector')->pause($this->longDelay)
            ->click('#new_data_collection')->pause($this->longDelay)
            ->typeSlowly('#collection_name_input', 'Stock delivery')
            ->press('OK')
            ->waitUntilMissing('#collection_name_input');

        $browser->waitFor('@data_collection_record')
            ->pause($this->longDelay)
            ->mouseover('@data_collection_record')->pause($this->shortDelay)
            ->click('@data_collection_record')
            ->waitUntilMissing('@data_collection_record')
            ->waitFor('#data_collection_name');

        $this->order->orderProducts()
            ->where('quantity_to_ship', '>', 0)
            ->first()
            ->each(function (OrderProduct $orderProduct) use ($browser) {
                $browser->pause(210);// wait for input to be focused
                $browser->screenshot('01');
                $browser->keys('#barcodeInput', $orderProduct->product->sku, '{ENTER}');
                $browser->pause($this->shortDelay);
                $browser->screenshot('02');
                $browser->waitForText($orderProduct->product->sku);
                $browser->pause($this->shortDelay);
                $browser->screenshot('04');
                $browser->waitFor('#data-collection-record-quantity-request-input')
                    ->pause($this->shortDelay)
                    ->screenshot('04')
                    ->typeSlowly('#data-collection-record-quantity-request-input', 12)->pause($this->shortDelay)
                    ->keys('#data-collection-record-quantity-request-input', '{ENTER}')
                    ->pause($this->shortDelay);
            });

        $browser->mouseover('#showConfigurationButton')->pause($this->shortDelay)
            ->click('#showConfigurationButton')->pause($this->shortDelay)
            ->mouseover('#transferInButton')->pause(500)->click('#transferInButton')->pause($this->longDelay)
            ->pause($this->longDelay);
    }

    /**
     * @param Browser $browser
     * @throws ElementClickInterceptedException
     * @throws NoSuchElementException
     */
    private function picklist(Browser $browser): void
    {
        $browser->mouseover('#navToggleButton')->pause($this->shortDelay)->click('#navToggleButton')->pause($this->shortDelay)
            ->mouseover('#picklists_link')->pause($this->shortDelay)->clickLink('Picklist')->pause($this->shortDelay)
            ->pause($this->shortDelay)->clickLink('Status: paid')->pause($this->longDelay)->pause(15000);

        $this->order->orderProducts()
            ->where('quantity_to_pick', '>', 0)
            ->first()
            ->each(function (OrderProduct $orderProduct) use ($browser) {
                $browser->waitForText($orderProduct->product->sku);
                $browser->assertSee($orderProduct->product->sku);
                $browser->type('#barcodeInput', $orderProduct->product->sku);
                $browser->pause(500)
                    ->keys('#barcodeInput', '{enter}')
                    ->pause($this->longDelay);
            });
    }

    /**
     * @param Browser $browser
     * @throws ElementClickInterceptedException
     * @throws NoSuchElementException
     */
    private function dashboard(Browser $browser): void
    {
        $browser->mouseover('#dashboard_link')->pause($this->shortDelay)->click('#dashboard_link')->pause($this->longDelay)
            ->pause($this->longDelay);
    }

    /**
     * @param Browser $browser
     */
    private function stocktaking(Browser $browser): void
    {
        $browser->mouseover('#tools_link')->pause($this->shortDelay)->clickLink('Tools')->pause($this->shortDelay)
            ->mouseover('#stocktaking_link')->pause($this->shortDelay)->clickLink('Stocktaking')->pause($this->shortDelay)
            ->pause($this->longDelay);
    }

    /**
     * @param Browser $browser
     */
    private function products(Browser $browser): void
    {
        $browser->mouseover('#products_link')->pause($this->shortDelay)->clickLink('Products')->pause($this->longDelay);
    }

    /**
     * @param Browser $browser
     */
    private function orders(Browser $browser): void
    {
        $browser->mouseover('#orders_link')->pause($this->shortDelay)->clickLink('Orders')->pause($this->longDelay);
    }

    /**
     * @param Browser $browser
     */
    private function restocking(Browser $browser): void
    {
        $browser->mouseover('#tools_link')->pause($this->shortDelay)->clickLink('Tools')->pause($this->shortDelay)
            ->mouseover('#restocking_link')->pause($this->shortDelay)->clickLink('Restocking')->pause($this->longDelay);
    }
}
