<?php

namespace Tests\Browser;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Warehouse;
use App\Modules\InventoryMovements\src\InventoryMovementsServiceProvider;
use App\Modules\InventoryTotals\src\InventoryTotalsServiceProvider;
use App\Services\InventoryService;
use App\User;
use Facebook\WebDriver\Exception\ElementClickInterceptedException;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeOutException;
use Facebook\WebDriver\WebDriverKeys;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Throwable;

class PagesWalkTroughTest extends DuskTestCase
{
    private Order $order;
    private User $user;
    private int $shortDelay = 120;
    private int $longDelay = 0;


    private Product $product1;
    private Product $product2;


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
            $this->dataCollectorStockDelivery($browser);
            $this->stocktaking($browser);
            $this->picklist($browser);
            $this->packlist($browser);
            $this->dashboard($browser);
            $this->restocking($browser);
        });
    }

    protected function setUp(): void
    {
        parent::setUp();

        InventoryTotalsServiceProvider::enableModule();
        InventoryMovementsServiceProvider::enableModule();

        /** @var Warehouse $warehouse */
        $warehouse = Warehouse::factory()->create(['name' => 'Dublin', 'code' => 'DUB']);

        $this->user = User::factory()->create([
            'warehouse_id' => $warehouse->getKey(),
            'password' => bcrypt('password')
        ]);

        $this->product1 = Product::query()->where(['sku' => '111576'])->first() ?? Product::factory()->create(['sku' => '111576']);
        $this->product2 = Product::query()->where(['sku' => '222957'])->first() ?? Product::factory()->create(['sku' => '222957']);

        $this->order = Order::factory()->create(['status_code' => 'paid']);

        /** @var OrderProduct $orderProduct1 */
        OrderProduct::factory()->create([
            'order_id' => $this->order->id,
            'sku_ordered' => $this->product1->sku,
            'name_ordered' => $this->product1->name,
            'product_id' => $this->product1->getKey(),
            'quantity_ordered' => 1
        ]);

        /** @var OrderProduct $orderProduct2 */
        OrderProduct::factory()->create([
            'order_id' => $this->order->id,
            'sku_ordered' => $this->product2->sku,
            'name_ordered' => $this->product2->name,
            'product_id' => $this->product2->getKey(),
            'quantity_ordered' => 3
        ]);
    }

    /**
     * @param Browser $browser
     * @throws ElementClickInterceptedException
     * @throws NoSuchElementException
     * @throws TimeOutException
     */
    private function packlist(Browser $browser): void
    {
        $browser->pause($this->shortDelay)->mouseover('#navToggleButton')
            ->pause($this->shortDelay)->click('#navToggleButton')
            ->pause($this->shortDelay)->mouseover('#packlists_link')
            ->pause($this->shortDelay)->clickLink('Packlist')
            ->pause($this->shortDelay)->clickLink('Status: paid')
            ->pause($this->longDelay)
            ->pause($this->shortDelay)->assertSee('Start AutoPilot Packing')
            ->pause($this->longDelay)
            ->pause($this->shortDelay)->click('@startAutopilotButton')
            ->pause($this->longDelay);


        while (OrderProduct::query()->where(['order_id' => $this->order->getKey()])
            ->where('quantity_to_ship', '>', 0)->exists()) {
            /** @var OrderProduct $orderProduct */
            $orderProduct = $this->order->orderProducts()
                ->where('quantity_to_ship', '>', 0)
                ->orderBy('id')
                ->get()
                ->first();

            $browser->waitForText($orderProduct->product->sku);
            $browser->assertSee($orderProduct->product->sku);

            $browser->driver->getKeyboard()->sendKeys($orderProduct->product->sku);
            $browser->pause($this->shortDelay);
            $browser->driver->getKeyboard()->sendKeys(WebDriverKeys::ENTER);
            $browser->pause($this->shortDelay);
        }

        $browser->pause($this->shortDelay);
        $browser->pause($this->shortDelay);
        $browser->pause($this->longDelay);
        $browser->driver->getKeyboard()->sendKeys('CB100023444');
        $browser->pause($this->shortDelay);
        $browser->driver->getKeyboard()->sendKeys(WebDriverKeys::ENTER);
        $browser->pause($this->shortDelay);
        $browser->pause($this->shortDelay);
    }

    /**
     * @param Browser $browser
     */
    private function login(Browser $browser): void
    {
        $browser->visit('/')
            ->pause($this->shortDelay)
            ->assertPathIs('/login')
            ->type('email', $this->user->email)->pause($this->shortDelay)
            ->type('password', 'password')->pause($this->shortDelay)
            ->press('Login')->pause($this->longDelay)
            ->assertPathBeginsWith('/dashboard')
            ->pause($this->shortDelay)
            ->pause($this->longDelay);
    }

    /**
     * @param Browser $browser
     * @throws ElementClickInterceptedException
     * @throws NoSuchElementException
     * @throws TimeOutException
     */
    private function dataCollectorStockDelivery(Browser $browser): void
    {
        $browser
            ->pause($this->shortDelay)->mouseover('#tools_link')
            ->pause($this->shortDelay)->click('#tools_link')
            ->pause($this->shortDelay)->mouseover('#data_collector_link')
            ->pause($this->shortDelay)->clickLink('Data Collector')
            ->pause($this->shortDelay)->click('#new_data_collection')
            ->pause($this->shortDelay)->typeSlowly('@collection_name_input', 'Stock delivery', 20)
            ->pause($this->shortDelay)->keys('@collection_name_input', '{enter}')
            ->pause($this->shortDelay)
            ->pause($this->shortDelay)->waitUntilMissing('#collection_name_input')
            ->pause($this->shortDelay)->waitFor('@data_collection_record')
            ->pause($this->shortDelay)
            ->pause($this->shortDelay)->mouseover('@data_collection_record')->pause($this->shortDelay)
            ->pause($this->shortDelay)->click('@data_collection_record')
            ->pause($this->shortDelay)->waitUntilMissing('@data_collection_record')
            ->pause($this->shortDelay)->waitFor('#data_collection_name')
            ->pause($this->longDelay);

        $this->order->orderProducts()
            ->where('quantity_to_ship', '>', 0)
            ->limit(4)
            ->get()
            ->each(function (OrderProduct $orderProduct) use ($browser) {
                $browser
                ->pause($this->shortDelay)
                ->pause($this->shortDelay)->keys('@barcode-input-field', $orderProduct->sku_ordered, '{ENTER}')
                ->pause($this->shortDelay)
                ->pause($this->shortDelay)->waitForText($orderProduct->sku_ordered)
                ->pause($this->shortDelay)
                ->pause($this->shortDelay)->waitFor('#data-collection-record-quantity-request-input')
                ->pause($this->shortDelay)->typeSlowly('#data-collection-record-quantity-request-input', 12)->pause($this->shortDelay)
                ->pause($this->shortDelay)->keys('#data-collection-record-quantity-request-input', '{ENTER}')
                ->pause($this->longDelay);
            });

        $browser
            ->pause($this->shortDelay)->mouseover('#showConfigurationButton')
            ->pause($this->shortDelay)->click('#showConfigurationButton')
            ->pause($this->longDelay)
            ->pause($this->shortDelay)->mouseover('#transferInButton')
            ->pause($this->shortDelay)->click('#transferInButton')
            ->pause($this->longDelay);
    }

    /**
     * @param Browser $browser
     * @throws ElementClickInterceptedException
     * @throws NoSuchElementException
     */
    private function picklist(Browser $browser): void
    {
        $browser->pause($this->shortDelay)
            ->pause($this->shortDelay)->mouseover('#navToggleButton')
            ->pause($this->shortDelay)->click('#navToggleButton')
            ->pause($this->longDelay)->mouseover('#picklists_link')
            ->pause($this->shortDelay)->clickLink('Picklist')
            ->pause($this->longDelay)->clickLink('Status: paid')
            ->pause($this->longDelay);

        $browser->pause($this->shortDelay)
            ->pause($this->shortDelay)->keys('@barcode-input-field', $this->product1->sku)
            ->pause($this->shortDelay)->keys('@barcode-input-field', '{enter}')
            ->pause($this->longDelay);

        $browser->pause($this->shortDelay)
            ->pause($this->shortDelay)->keys('@barcode-input-field', $this->product2->sku)
            ->pause($this->shortDelay)->keys('@barcode-input-field', '{enter}')
            ->pause($this->longDelay);
    }

    /**
     * @throws ElementClickInterceptedException
     * @throws NoSuchElementException
     */
    private function dashboard(Browser $browser): void
    {
        $browser->mouseover('#dashboard_link')->pause($this->shortDelay)
            ->click('#dashboard_link')->pause($this->longDelay)
            ->pause($this->longDelay);
    }

    /**
     * @param Browser $browser
     * @throws TimeoutException
     */
    private function stocktaking(Browser $browser): void
    {
        /** @var Product $product */
        $product = Product::first();

        $browser
            ->pause($this->shortDelay)->mouseover('#tools_link')
            ->pause($this->shortDelay)->clickLink('Tools')
            ->pause($this->shortDelay)
            ->pause($this->shortDelay)->mouseover('#stocktaking_link')
            ->pause($this->shortDelay)->clickLink('Stocktaking')
            ->pause($this->shortDelay)
            ->pause($this->shortDelay)->type('@barcode-input-field', $product->sku)
            ->pause($this->shortDelay)->screenshot('stocktaking')
            ->pause($this->shortDelay)->keys('@barcode-input-field', '{enter}')
            ->pause($this->shortDelay)
            ->pause($this->shortDelay)->waitForText($product->name)
            ->pause($this->shortDelay)
            ->pause($this->shortDelay)->waitFor('#quantity-request-input')
            ->pause($this->shortDelay)->typeSlowly('#quantity-request-input', 12)
            ->pause($this->shortDelay)->keys('#quantity-request-input', '{ENTER}')
            ->pause($this->longDelay);
    }

    /**
     * @param Browser $browser
     */
    private function products(Browser $browser): void
    {
        $browser->mouseover('#products_link')
            ->pause($this->shortDelay)->clickLink('Products')
            ->pause($this->longDelay)
            ->pause($this->shortDelay)->keys('@barcode-input-field', Product::first('sku')['sku'], '{enter}')
            ->pause($this->longDelay);
    }

    private function orders(Browser $browser): void
    {
        $browser->mouseover('#orders_link')
            ->pause($this->shortDelay)->clickLink('Orders')
            ->pause($this->longDelay)
            ->pause($this->shortDelay)->keys('@barcode-input-field', Order::first()->getAttribute('order_number'), '{enter}')
            ->pause($this->longDelay);
    }

    /**
     * @param Browser $browser
     */
    private function restocking(Browser $browser): void
    {
        $browser->mouseover('#tools_link')->pause($this->shortDelay)
            ->clickLink('Tools')->pause($this->shortDelay)
            ->mouseover('#restocking_link')->pause($this->shortDelay)
            ->clickLink('Restocking')->pause($this->longDelay);
    }
}
