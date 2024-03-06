# Management Products API

The Management Products API is a Laravel-based web application that allows users to manage products in a database.

## Getting Started

To get started with the Management Products API, follow these steps:

1. Clone the repository: `git clone https://github.com/codingcab/ShipTown.git`
2. Navigate to the project directory: `cd ShipTown`
3. Install dependencies: `composer install`
4. Copy the `.env.example` file to `.env`: `cp .env.example .env`
5. Generate a new application key: `php artisan key:generate`
6. Create a new MySQL database for the application and update the `.env` file with your database credentials
7. Run database migrations: `php artisan migrate`
8. Start the application: `php artisan serve`

## Authentication

The Management Products API uses token-based authentication to secure API endpoints. To authenticate, send a `POST` request to the `/api/auth/login` endpoint with your email and password in the request body. The API will return a JSON response containing your authentication token. To access authenticated endpoints, include your token in the `Authorization` header of your requests.

## Error Handling

The Management Products API uses HTTP status codes to indicate the success or failure of requests. Successful requests will return a `200 OK` status code, while unsuccessful requests will return an appropriate error code (e.g. `400 Bad Request`, `401 Unauthorized`, `404 Not Found`, etc.). In addition, error messages will be included in the response body.

## Contributing

Contributions to the Management Products API are welcome! To contribute, please follow these steps:

1. Fork the repository.
2. Create a new branch for your changes: `git checkout -b my-new-branch`
3. Make your changes and commit them: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin my-new-branch`
5. Submit a pull request.

Please ensure that your code follows the existing coding style and includes tests for any new functionality.

## License

The Management Products API is open source software licensed under the MIT license. Please see the `LICENSE` file for more details.

## Contact Information
    
If you have any questions or issues with the Management Products API, please contact the project owner at `contact@ship.town`.

---
# Application - Core Principles
- Core functionality it's __Inventory Management__ of stock
- Each product should have a unique SKU (products.sku)
- Each product might have multiple barcodes (products_aliases)
- We can have multiple locations (warehouses)
- Each product can be in multiple locations (inventory)
- Each inventory can have multiple reservations (inventory_reservations)
- We import orders from multiple sources (orders)
- Each order can have multiple products (orders_products)
- We automatically group orders by order_status to help with the most efficient shipment and delivery process
- We help with stock management, restocking, reordering, and stock taking

# Use cases

### Small Web Store / eCommerce Fulfillment Center
1. User logs in
2. User connects Magento2 API or other sources
3. Orders are fetched from eCommerce Store or other sources
4. Orders are grouped by order_status for most efficient shipment and delivery process
5. Users pick and pack the products
6. Courier labels are automatically printed and attached to the packages
7. AutoPilot suggest the next best order to the user, based on the most efficient route
8. Clients are notified of the shipment and tracking number

### 3rd Party eCommerce Fulfillment Center (3PL, 4PL, Multi-Channel, Multi-Location, Multi-User)
1. User logs in
2. User connects CLIENTS Magento2 API (multiple allowed)
3. User connects Couriers API (multiple allowed)
3. Orders are fetched from CLIENTS eCommerce Store
4. Order Automations group orders by order_status for most efficient shipment and delivery process ensuring that standards required are met
5. Order are picked in batches for efficiency
6. AutoPilot suggest the next best order to the user, based on the most efficient route and request for packing
7. Courier labels are automatically printed and attached to the packages
8. Clients are notified of the shipment and tracking number

### Retail Store
1. User logs in
2. User connects ePOS API
3. Sales are fetched from ePOS
4. Inventory can be managed by the user (goods in, goods out, stock take, stock checks, sales, damages...)
5. AutoPilot suggest restock level and reorder points
6. Store staff updates inventory restocking levels and reorder points to their needs
7. Stock gets delivered based on the reorder points and restocking levels
8. Stock is automatically requested from fulfillment center
9. AutoPilot monitors the stock for possible stock issues and suggests actions to the user
10. Buying department can see the sales and stock levels and make decisions based on the accurate data

### Click and Collect orders (eCommerce and Retail Store)
1. User logs in
2. User connects eCommerce Store API
3. Customer places an order requesting collection in specific location
3. Orders are fetched from eCommerce Store
4. Orders are Automatically dispatched to the specific location
4. Orders Automations group orders by order_status for most efficient shipment and delivery process
5. Users pick and pack the products
6. Courier labels are automatically printed and attached to the packages
7. AutoPilot suggest the next best order to the user, based on the most efficient route
8. Clients are notified of the shipment and tracking number

### Preorders (Layaway)
1. User logs in
2. User connects retail store ePOS API
3. Orders are created and managed on the ePOS system
4. Orders are marked for shipment on the ePOS system
4. Orders are fetched from ePOS
5. Orders are grouped by order_status for most efficient shipment and delivery process
6. Users pick and pack the products
7. Courier labels are automatically printed and attached to the packages
8. AutoPilot suggest the next best order to the user, based on the most efficient route
9. Clients are notified of the shipment and tracking number

# Code Structure
### Models (App\Models)
Models are used to interact with the database. 
They are used to read and write data to the database using eloquent

### Observers (App\Observers)
Observers are used to listen for database record changes (Model) and dispatch events when they occur.

>Example: __OrderObserver__ listens for changes in the __Order__ model and dispatches __OrderUpdatedEvent__ when changes happen.

### Events (App\Events)
Event are dispatched throughout the application to notify other parts of the application that something has happened and they should take action.

For example, when an order is created, an event is dispatched to notify the shipping department that they should take action.
> Example: __OrderUpdatedEvent__ is dispatched when an order is updated. 
> __InventoryReservations__ module listens for this event and takes action to ensure stock is not oversold.

### Modules (App\Modules)
Modules are used to group related functionality together.
- modules should be ideally maintaining their own data
- modules data tables should prefix with ___module____ and module name (e.g. modules_inventory_reservations_configuration)
- deleting a module should not affect functionality of other parts of the application

> Example: 
> __InventoryReservations__ module is used to listen for events like __OrderUpdatedEvent__ or __OrderProductShippedEvent__ 
> to ensure correct quantity_reserved is maintained in inventory table and stock is not oversold. 

Each module has its own folder and might contains the following:

- __ModuleServiceProvider__ (App\Modules\InventoryReservations\src\ModuleServiceProvider.php)
- __Controllers__ (App\Modules\InventoryReservations\src\Controllers)
- __Models__ (App\Modules\InventoryReservations\src\Models)
- __Observers__ (App\Modules\InventoryReservations\src\Observers)
- __Events__ (App\Modules\InventoryReservations\src\Events)
- __Listeners__ (App\Modules\InventoryReservations\src\Listeners)
- __Migrations__ (App\Modules\InventoryReservations\Database\src\Migrations)
- __Seeders__ (App\Modules\InventoryReservations\Database\src\Seeders)
- __Views__ (App\Modules\InventoryReservations\src\Views)
- __Resources__ (App\Modules\InventoryReservations\src\Resources)
- __Requests__ (App\Modules\InventoryReservations\src\Requests)

### ___Tests___ (/Tests)- `php artisan test`
Helps us to make sure we didn't break anything accidentally

### Dusk Tests (/Tests/Browser)  - `php artisan dusk`
Dusk Tests sof what user would do, not what the code does

### Route Tests (Tests/Feature)
Write simple but meaningful, easy to understand tests (e.g. test if api route response success if given some data)

### Unit / Module Tests (Tests/Unit)
Write tests for each module to ensure it works as expected

## Code Design Principles
 - User experience and efficiency is the important thing, this is the ultimate problem we are solving
 - Make the code clear and simple so we can understand it and maintain it for years

## Scale
We design for the size of the database:
- 20 locations (warehouses, stores, etc)
- 30 users
- 100 000 orders fulfilled per year
- 500 000 products
- 10 000 000 inventory records

Server size: 4 core, 16gb ram, 500gb ssd, 1gbps

