<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws Exception
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->integer('printer_id')->nullable();
            $table->string('address_label_template')->nullable();
            $table->boolean('ask_for_shipping_number')->default(true);
            $table->string('name');
            $table->string('email')->unique();
            $table->foreignId('warehouse_id')->nullable(true);
            $table->foreignId('location_id')->nullable(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('two_factor_code')->nullable();
            $table->dateTime('two_factor_expires_at')->nullable();
            $table->string('default_dashboard_uri')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->unique();
            $table->foreignId('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('payload');
            $table->integer('last_activity');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::create('oauth_auth_codes', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->foreignId('user_id')->index();
            $table->foreignId('client_id');
            $table->text('scopes')->nullable();
            $table->boolean('revoked');
            $table->dateTime('expires_at')->nullable();
        });

        Schema::create('oauth_access_tokens', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->foreignId('client_id');
            $table->string('name')->nullable();
            $table->text('scopes')->nullable();
            $table->boolean('revoked');
            $table->timestamps();
            $table->dateTime('expires_at')->nullable();
        });

        Schema::create('oauth_refresh_tokens', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('access_token_id', 100)->index();
            $table->boolean('revoked');
            $table->dateTime('expires_at')->nullable();
        });

        Schema::create('oauth_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('name');
            $table->string('secret', 100)->nullable();
            $table->string('provider')->nullable();
            $table->text('redirect');
            $table->boolean('personal_access_client');
            $table->boolean('password_client');
            $table->boolean('revoked');
            $table->timestamps();
        });

        Schema::create('oauth_personal_access_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id');
            $table->timestamps();
        });

        Schema::create(config('queue-monitor.table'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('job_id')->index();
            $table->string('name')->nullable();
            $table->string('queue')->nullable();
            $table->timestamp('started_at')->nullable()->index();
            $table->string('started_at_exact')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->string('finished_at_exact')->nullable();
            $table->float('time_elapsed', 12, 6)->nullable()->index();
            $table->boolean('failed')->default(false)->index();
            $table->integer('attempt')->default(0);
            $table->integer('progress')->nullable();
            $table->longText('exception')->nullable();
            $table->text('exception_message')->nullable();
            $table->text('exception_class')->nullable();
            $table->longText('data')->nullable();
        });



        Schema::create('mail_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->nullable(false)->default('');
            $table->string('mailable');
            $table->string('to', 255)->nullable();
            $table->string('reply_to', 100)->nullable();
            $table->text('subject')->nullable();
            $table->longtext('html_template');
            $table->longtext('text_template')->nullable();
            $table->timestamps();
        });

        Schema::connection(config('activitylog.database_connection'))->create(config('activitylog.table_name'), function (Blueprint $table) {
            $table->id();
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->foreignId('subject_id')->nullable();
            $table->string('subject_type')->nullable();
            $table->string('event')->nullable();
            $table->foreignId('causer_id')->nullable();
            $table->string('causer_type')->nullable();
            $table->json('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->timestamps();
            $table->index('log_name');
            $table->index(['subject_id', 'subject_type'], 'subject');
            $table->index(['causer_id', 'causer_type'], 'causer');
        });

        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->nullable()->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('widgets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('config');
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 50)->unique();
            $table->string('name', 100)->default('');
            $table->decimal('price', 10)->default(0);
            $table->decimal('sale_price', 10)->default(0);
            $table->date('sale_price_start_date')->default('1899-01-01');
            $table->date('sale_price_end_date')->default('1899-01-01');
            $table->string('commodity_code')->default('');
            $table->decimal('quantity', 10)->default(0);
            $table->decimal('quantity_reserved', 10)->default(0);
            $table->decimal('quantity_available', 10)->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index('quantity');
            $table->index('quantity_reserved');
            $table->index('quantity_available');
            $table->index('deleted_at');
        });

        Schema::create('products_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id');
            $table->string('alias')->unique();
            $table->timestamps();

            $table->foreign('product_id')
                ->on('products')
                ->references('id')
                ->onDelete('CASCADE');
        });

        Schema::create('orders_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('company')->default('');
            $table->string('gender')->default('');
            $table->string('address1')->default('');
            $table->string('address2')->default('');
            $table->string('postcode')->default('');
            $table->string('city')->default('');
            $table->string('state_code')->default('');
            $table->string('state_name')->default('');
            $table->string('country_code')->default('');
            $table->string('country_name')->default('');
            $table->string('fax')->default('');
            $table->string('website')->default('');
            $table->string('region')->default('');
            $table->longText('first_name_encrypted')->nullable();
            $table->longText('last_name_encrypted')->nullable();
            $table->longText('email_encrypted')->nullable();
            $table->longText('phone_encrypted')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 5)->nullable(false)->unique();
            $table->string('name');
            $table->foreignId('address_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('address_id')
                ->references('id')
                ->on('orders_addresses')
                ->onDelete('CASCADE');
        });

        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id');
            $table->foreignId('product_id');
            $table->string('location_id')->default('');
            $table->string('warehouse_code', 5)->nullable(false);
            $table->string('shelve_location')->default('');
            $table->decimal('quantity_available', 20)
                ->storedAs('quantity - quantity_reserved')
                ->comment('quantity - quantity_reserved');
            $table->decimal('quantity', 20)->default(0);
            $table->boolean('is_in_stock')
                ->storedAs('quantity_available > 0')
                ->comment('quantity_available > 0');
            $table->decimal('quantity_reserved', 20)->default(0);
            $table->decimal('quantity_incoming', 20)->default(0);
            $table->decimal('quantity_required', 20)
                ->storedAs('CASE WHEN (quantity - quantity_reserved + quantity_incoming) BETWEEN 0 AND reorder_point ' .
                    'THEN restock_level - (quantity - quantity_reserved + quantity_incoming)' .
                    'ELSE 0 END')
                ->comment('CASE WHEN (quantity - quantity_reserved + quantity_incoming) BETWEEN 0 AND reorder_point ' .
                    'THEN restock_level - (quantity - quantity_reserved + quantity_incoming)' .
                    'ELSE 0 END');
            $table->decimal('reorder_point', 20)->default(0);
            $table->decimal('restock_level', 20)->default(0);
            $table->dateTime('last_movement_at')->nullable();
            $table->dateTime('first_received_at')->nullable();
            $table->dateTime('last_received_at')->nullable();
            $table->dateTime('first_sold_at')->nullable();
            $table->dateTime('last_sold_at')->nullable();
            $table->timestamp('last_counted_at')->nullable();
            $table->unsignedBigInteger('last_movement_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('product_id');
            $table->index('warehouse_code');
            $table->index('shelve_location');
            $table->index('quantity_available');
            $table->index('quantity');
            $table->index('is_in_stock');
            $table->index('quantity_reserved');
            $table->index('quantity_incoming');
            $table->index('quantity_required');
            $table->index('restock_level');
            $table->index('reorder_point');
            $table->index('last_sold_at');
            $table->index('last_counted_at');

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');

            $table->foreign('warehouse_id')
                ->on('warehouses')
                ->references('id')
                ->onDelete('cascade');
        });

        Schema::create('orders_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->boolean('order_active')->default(true);
            $table->boolean('order_on_hold')->default(false);
            $table->boolean('hidden')->default(false);
            $table->boolean('sync_ecommerce')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_address_id')->nullable();
            $table->string('order_number')->unique();
            $table->string('status_code')->default('');
            $table->string('label_template')->default('');
            $table->boolean('is_active')->nullable(false)->default(0);
            $table->boolean('is_on_hold')->default(false);
            $table->boolean('is_editing')->default(0);
            $table->boolean('is_fully_paid')
                ->storedAs('total_paid >= total - total_discounts')
                ->comment('total_paid >= total - total_discounts');
            $table->decimal('total_products')->default(0);
            $table->decimal('total_shipping')->default(0);
            $table->decimal('total', 10)->default(0);
            $table->decimal('total_paid')->default(0);
            $table->decimal('total_discounts', 10)->default(0);
            $table->string('shipping_method_code')->default('')->nullable(true);
            $table->string('shipping_method_name')->default('')->nullable(true);
            $table->timestamp('order_placed_at')->useCurrent()->nullable();
            $table->timestamp('order_closed_at')->nullable();
            $table->integer('product_line_count')->default(0);
            $table->timestamp('picked_at')->nullable();
            $table->timestamp('packed_at')->nullable();
            $table->foreignId('packer_user_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('status_code');
            $table->index('is_active');
            $table->index('is_on_hold');
            $table->index('label_template');
            $table->index('order_placed_at');
            $table->fullText(['order_number','status_code']);

            $table->foreign('status_code')
                ->on('orders_statuses')
                ->references('code')
                ->onDelete('RESTRICT');

            $table->foreign('shipping_address_id')
                ->on('orders_addresses')
                ->references('id')
                ->onDelete('SET NULL');

            $table->foreign('packer_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('SET NULL');
        });

        Schema::create('orders_products', function (Blueprint $table) {
            $table->id();
            $table->string('custom_unique_reference_id')->unique()->nullable();
            $table->foreignId('order_id');
            $table->foreignId('product_id')->nullable();
            $table->string('sku_ordered');
            $table->string('name_ordered');
            $table->decimal('price', 10, 3)->default(0);
            $table->decimal('quantity_ordered', 10)->default(0);
            $table->decimal('quantity_split', 10)->default(0);
            $table->decimal('total_price', 20)
                ->storedAs('(quantity_ordered - quantity_split) * price')
                ->comment('(quantity_ordered - quantity_split) * price');
            $table->decimal('quantity_shipped', 10)->default(0);
            $table->decimal('quantity_to_pick', 10)
                ->storedAs('quantity_ordered - quantity_split - quantity_picked - quantity_skipped_picking')
                ->comment('quantity_ordered - quantity_split - quantity_picked - quantity_skipped_picking');
            $table->decimal('quantity_to_ship', 10)
                ->storedAs('quantity_ordered - quantity_split - quantity_shipped')
                ->comment('quantity_ordered - quantity_split - quantity_shipped');
            $table->decimal('quantity_picked', 10)->default(0);
            $table->decimal('quantity_skipped_picking', 10)->default(0);
            $table->decimal('quantity_not_picked', 10)->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('product_id')
                ->on('products')
                ->references('id')
                ->onDelete('SET NULL');

            $table->foreign('order_id')
                ->on('orders')
                ->references('id')
                ->onDelete('cascade');
        });

        Schema::create('orders_products_totals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique();
            $table->integer('count')->default(0);
            $table->decimal('quantity_ordered', 20)->default(0);
            $table->decimal('quantity_split', 20)->default(0);
            $table->decimal('total_price', 20)->default(0);
            $table->decimal('quantity_picked', 20)->default(0);
            $table->decimal('quantity_skipped_picking', 20)->default(0);
            $table->decimal('quantity_not_picked', 20)->default(0);
            $table->decimal('quantity_shipped', 20)->default(0);
            $table->decimal('quantity_to_pick', 20)->default(0);
            $table->decimal('quantity_to_ship', 20)->default(0);
            $table->timestamp('max_updated_at')->default('2000-01-01 00:00:00');
            $table->timestamps();

            $table->index('count');
            $table->index('quantity_ordered');
            $table->index('quantity_split');
            $table->index('quantity_picked');
            $table->index('quantity_skipped_picking');
            $table->index('quantity_not_picked');
            $table->index('quantity_shipped');
            $table->index('quantity_to_pick');
            $table->index('quantity_to_ship');
            $table->index('updated_at');

            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('CASCADE');
        });

        Schema::create('orders_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('order_id');
            $table->string('shipping_number');
            $table->string('carrier')->default('');
            $table->string('service')->default('');
            $table->string('tracking_url')->default('');
            $table->longText('base64_pdf_labels');
            $table->timestamps();


            $table->fullText(['shipping_number']);

            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('CASCADE');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('SET NULL');
        });

        Schema::create('orders_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id');
            $table->foreignId('user_id')->nullable();
            $table->string('comment');
            $table->timestamps();

            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('CASCADE');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('SET NULL');
        });

        Schema::create('picks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('product_id')->nullable();
            $table->string('warehouse_code')->nullable();
            $table->string('sku_ordered');
            $table->string('name_ordered');
            $table->decimal('quantity_picked', 10, 2)->default(0);
            $table->decimal('quantity_skipped_picking', 10, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('SET NULL');

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('SET NULL');
        });

        Schema::create('shipping_services', function (Blueprint $table) {
            $table->id();
            $table->string('code', 25)->unique()->nullable(false);
            $table->string('service_provider_class');
            $table->timestamps();
        });

        Schema::create('picks_orders_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pick_id')->constrained('picks')->cascadeOnDelete();
            $table->foreignId('order_product_id')->nullable()->constrained('orders_products')->nullOnDelete();
            $table->decimal('quantity_picked', 10, 2)->default(0);
            $table->decimal('quantity_skipped_picking', 10, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->increments('id');
            $table->json('name');
            $table->json('slug');
            $table->string('type')->nullable();
            $table->integer('order_column')->nullable();
            $table->timestamps();
        });

        Schema::create('taggables', function (Blueprint $table) {
            $table->integer('tag_id')->unsigned();
            $table->morphs('taggable');
            $table->unique(['tag_id', 'taggable_id', 'taggable_type']);
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
        });

        Schema::create('products_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->index();
            $table->foreignId('warehouse_id');
            $table->string('location_id')->default('');
            $table->string('warehouse_code', 5)->nullable(false);
            $table->decimal('cost', 20, 2)->default(0);
            $table->decimal('price', 20)->default(0);
            $table->decimal('sale_price', 20)->default(0);
            $table->date('sale_price_start_date')->default('2000-01-01');
            $table->date('sale_price_end_date')->default('2000-01-01');
            $table->softDeletes();
            $table->timestamps();

            $table->index('warehouse_code');
            $table->unique(['product_id', 'warehouse_id']);

            $table->foreign('product_id')
                ->on('products')
                ->references('id')
                ->onDelete('CASCADE');

            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->onDelete('CASCADE');
        });

        Schema::create('modules_printnode_clients', function (Blueprint $table) {
            $table->id();
            $table->string('api_key');
            $table->timestamps();
        });

        Schema::create('module_auto_status_pickings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')
                ->nullable(false)
                ->default(true);
            $table->timestamps();
        });

        Schema::create('modules_dpd-ireland_configuration', function (Blueprint $table) {
            $table->id();
            $table->boolean('live')->nullable(false)->default(false);
            $table->string('token');
            $table->string('user');
            $table->string('password');
            $table->string('contact')->nullable(false)->default('');
            $table->string('contact_telephone')->nullable(false)->default('');
            $table->string('contact_email')->nullable(false)->default('');
            $table->string('business_name')->nullable(false)->default('');
            $table->string('address_line_1')->nullable(false)->default('');
            $table->string('address_line_2')->nullable(false)->default('');
            $table->string('address_line_3')->nullable(false)->default('');
            $table->string('address_line_4')->nullable(false)->default('');
            $table->string('country_code', 10)->nullable(false)->default('');
            $table->timestamps();
        });

        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('service_provider_class')->nullable(false);
            $table->boolean('enabled')->nullable(false)->default(false);
            $table->timestamps();

            $table->unique('service_provider_class');
        });

        Schema::create('modules_autostatus_picking_configurations', function (Blueprint $table) {
            $table->id();
            $table->integer('max_batch_size')->nullable(false)->default(10);
            $table->integer('max_order_age')->nullable(false)->default(5);
            $table->timestamps();
        });

        Schema::create('modules_printnode_print_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // = 'Url Print';
            $table->string('printer_id'); // = $printerId;
            $table->string('content_type'); // = 'pdf_base64';
            $table->longText('content'); // = $base64PdfString;
            $table->integer('expire_after');
            $table->timestamps();
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('navigation_menu', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('url', 999)->default('');
            $table->string('group', 100);
            $table->timestamps();
        });

        Schema::create('modules_automations', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('priority')->nullable(false)->default(0);
            $table->boolean('enabled')->nullable(false)->default(false);
            $table->string('name')->nullable(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('modules_automations_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_id');
            $table->string('condition_class')->nullable();
            $table->string('condition_value')->nullable()->default('');
            $table->timestamps();

            $table->unique(
                ['automation_id', 'condition_class', 'condition_value'],
                'modules_automations_conditions_automation_id_class_value_unique'
            );

            $table->foreign('automation_id')
                ->references('id')
                ->on('modules_automations')
                ->onDelete('CASCADE');
        });

        Schema::create('modules_automations_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_id');
            $table->smallInteger('priority')->nullable(false)->default(0);
            $table->string('action_class')->nullable();
            $table->string('action_value')->nullable(false)->default('');
            $table->timestamps();

            $table->foreign('automation_id')
                ->references('id')
                ->on('modules_automations')
                ->onDelete('CASCADE');
        });

        Schema::create('orders_products_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('warehouse_id')->nullable();
            $table->foreignId('order_id')->nullable();
            $table->foreignId('order_product_id');
            $table->foreignId('order_shipment_id')->nullable();
            $table->string('sku_shipped')->default('');
            $table->decimal('quantity_shipped', 10);
            $table->timestamps();

            $table->foreign('order_id')
                ->on('orders')
                ->references('id')
                ->onDelete('SET NULL');
        });

        Schema::create('modules_rmsapi_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->nullable();
            $table->string('location_id');
            $table->string('url');
            $table->string('username');
            $table->string('password');
            $table->string('price_field_name')->default('price');
            $table->unsignedBigInteger('products_last_timestamp')->default(0);
            $table->unsignedBigInteger('shippings_last_timestamp')->default(0);
            $table->unsignedBigInteger('sales_last_timestamp')->default(0);
            $table->timestamps();

            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->cascadeOnDelete();
        });

        Schema::create('modules_rmsapi_shipping_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('connection_id');
            $table->json('raw_import');
            $table->timestamps();

            $table->foreign('connection_id')
                ->references('id')
                ->on('modules_rmsapi_connections')
                ->onDelete('cascade');
        });

        Schema::create('modules_rmsapi_products_imports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('connection_id');
            $table->foreignId('warehouse_id')->nullable();
            $table->string('warehouse_code')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->timestamp('reserved_at')->nullable();
            $table->dateTime('when_processed')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('sku')->nullable();
            $table->decimal('quantity_on_hand', 20)->nullable();
            $table->decimal('quantity_committed', 20)->nullable();
            $table->decimal('quantity_available', 20)->nullable();
            $table->decimal('quantity_on_order', 20)->nullable();
            $table->json('raw_import');
            $table->timestamps();


            $table->index('when_processed');

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('SET NULL');

            $table->foreign('connection_id')
                ->references('id')
                ->on('modules_rmsapi_connections')
                ->onDelete('cascade');

            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->cascadeOnDelete();

            $table->foreign('warehouse_code')
                ->references('code')
                ->on('warehouses')
                ->cascadeOnDelete();
        });

        Schema::create('modules_api2cart_connections', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('');
            $table->string('url')->default('');
            $table->string('inventory_source_warehouse_tag')->nullable();
            $table->foreignId('inventory_source_warehouse_tag_id')->nullable();
            $table->foreignId('pricing_source_warehouse_id')->nullable();
            $table->char('prefix', 10)->default('');
            $table->string('bridge_api_key')->nullable();
            $table->unsignedBigInteger('magento_store_id')->nullable();
            $table->string('magento_warehouse_id')->nullable();
            $table->string('pricing_location_id', 5)->nullable(true);
            $table->dateTime('last_synced_modified_at')->default('2020-01-01 00:00:00');
            $table->timestamps();
        });

        Schema::create('modules_api2cart_order_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('connection_id')->nullable();
            $table->foreignId('order_id')->nullable();
            $table->string('shipping_method_name')->nullable(true);
            $table->string('shipping_method_code')->nullable(true);
            $table->dateTime('when_processed')->nullable();
            $table->string('order_number')->nullable();
            $table->integer('api2cart_order_id');
            $table->json('raw_import');
            $table->timestamps();

            $table->index('order_number');
            $table->index('when_processed');

            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('SET NULL');

            $table->foreign('connection_id')
                ->references('id')
                ->on('modules_api2cart_connections')
                ->onDelete('SET NULL');
        });

        Schema::create('modules_api2cart_product_links', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_in_sync')->nullable()->index();
            $table->foreignId('product_id');
            $table->foreignId('api2cart_connection_id');
            $table->string('api2cart_product_type')->nullable();
            $table->string('api2cart_product_id')->nullable();
            $table->timestamp('last_pushed_at')->nullable();
            $table->json('last_pushed_response')->nullable();
            $table->dateTime('last_fetched_at')->nullable();
            $table->json('last_fetched_data')->nullable();
            $table->decimal('api2cart_quantity', 10, 2)->nullable();
            $table->decimal('api2cart_price', 10, 2)->nullable();
            $table->decimal('api2cart_sale_price', 10, 2)->nullable();
            $table->date('api2cart_sale_price_start_date')->nullable();
            $table->date('api2cart_sale_price_end_date')->nullable();
            $table->timestamps();


            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('CASCADE');

            $table->foreign('api2cart_connection_id')
                ->references('id')
                ->on('modules_api2cart_connections')
                ->onDelete('CASCADE');

            $table->unique(['api2cart_connection_id', 'api2cart_product_id'], 'api2cart_connection_product_id_unique');
        });

        Schema::create('configurations', function (Blueprint $table) {
            $table->id();
            $table->string('business_name')->default('');
            $table->boolean('disable_2fa')->default(0);
            $table->timestamps();
        });

        Schema::create('heartbeats', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('error_message', 255)->nullable();
            $table->timestamp('expires_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamps();
        });

        Schema::create('modules_dpduk_connections', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('password');
            $table->string('account_number');
            $table->foreignId('collection_address_id')->nullable();
            $table->string('geo_session')->nullable();
            $table->timestamps();

            $table->foreign('collection_address_id')
                ->references('id')
                ->on('orders_addresses')
                ->onDelete('SET NULL');
        });

        Schema::create('modules_boxtop_warehouse_stock', function (Blueprint $table) {
            $table->id();
            $table->string('SKUGroup');
            $table->string('SKUNumber');
            $table->string('SKUName');
            $table->string('Attributes');
            $table->string('Warehouse');
            $table->float('WarehouseQuantity');
            $table->float('Allocated');
            $table->float('Available');
            $table->timestamps();
        });

        Schema::create('modules_boxtop_order_lock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique();
            $table->timestamps();
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('previous_movement_id')->nullable()->unique();
            $table->string('type')->nullable();
            $table->string('custom_unique_reference_id')->nullable()->unique();
            $table->foreignId('inventory_id');
            $table->foreignId('product_id');
            $table->foreignId('warehouse_id');
            $table->decimal('quantity_delta', 20);
            $table->decimal('quantity_before', 20);
            $table->decimal('quantity_after', 20);
            $table->string('description', 50);
            $table->foreignId('user_id')->nullable();
            $table->timestamps();

            $table->index('type');

            $table->foreign('inventory_id')
                ->references('id')
                ->on('inventory')
                ->cascadeOnDelete();

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();

            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });

        Schema::create('inventory_movements_statistics', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->foreignId('inventory_id');
            $table->foreignId('product_id')->index();
            $table->string('warehouse_code', 5)->index();
            $table->double('last7days_quantity_delta', 10, 2)->default(0);
            $table->double('last14days_quantity_delta', 10, 2)->default(0);
            $table->double('last28days_quantity_delta', 10, 2)->default(0);
            $table->unsignedBigInteger('last7days_min_movement_id')->nullable();
            $table->unsignedBigInteger('last7days_max_movement_id')->nullable();
            $table->unsignedBigInteger('last14days_min_movement_id')->nullable();
            $table->unsignedBigInteger('last14days_max_movement_id')->nullable();
            $table->unsignedBigInteger('last28days_min_movement_id')->nullable();
            $table->unsignedBigInteger('last28days_max_movement_id')->nullable();
            $table->dateTime('last_sold_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['type', 'inventory_id']);

            $table->foreign('inventory_id')
                ->references('id')
                ->on('inventory')
                ->cascadeOnDelete();

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();

            $table->foreign('warehouse_code')
                ->references('code')
                ->on('warehouses')
                ->cascadeOnDelete();
        });

        Schema::create('modules_webhooks_pending_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('model_class');
            $table->foreignId('model_id');
            $table->json('message');
            $table->timestamp('reserved_at')->nullable();
            $table->timestamp('available_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['reserved_at', 'published_at']);
            $table->index('published_at');
            $table->index('reserved_at');
        });

        Schema::create('modules_webhooks_configuration', function (Blueprint $table) {
            $table->id();
            $table->string('topic_arn')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_totals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique();
            $table->decimal('quantity', 20)->default(0);
            $table->decimal('quantity_reserved', 20)->default(0);
            $table->decimal('quantity_incoming', 20)->default(0);
            $table->decimal('quantity_available', 20)
                ->storedAs('quantity - quantity_reserved')
                ->comment('quantity - quantity_reserved');
            $table->softDeletes();
            $table->timestamps();

            $table->index('quantity');
            $table->index('quantity_reserved');
            $table->index('quantity_incoming');

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');
        });

        Schema::create('data_collections', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->foreignId('warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->onDelete('cascade');
            $table->string('name');
            $table->string('currently_running_task')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('data_collection_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_collection_id')->constrained()->onDelete('cascade');
            $table->foreignId('inventory_id')->nullable()
                ->references('id')
                ->on('inventory');
            $table->foreignId('product_id');
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->double('total_transferred_in', 10)->default(0);
            $table->double('total_transferred_out', 10)->default(0);
            $table->decimal('quantity_requested', 20)->nullable();
            $table->decimal('quantity_scanned', 20)->default(0);
            $table->decimal('quantity_to_scan', 20)
                ->storedAs('CASE WHEN quantity_requested - total_transferred_out - total_transferred_in - quantity_scanned < quantity_scanned THEN 0 ' .
                    'ELSE quantity_requested - total_transferred_out - total_transferred_in - quantity_scanned END')
                ->comment('CASE WHEN quantity_requested - total_transferred_out - total_transferred_in - quantity_scanned < quantity_scanned THEN 0 ' .
                    'ELSE quantity_requested - total_transferred_out - total_transferred_in - quantity_scanned - quantity_scanned END');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');
        });

        Schema::create('stocktake_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->references('id')->on('inventory')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->references('id')->on('products')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->references('id')->on('warehouses')->cascadeOnDelete();
            $table->integer('points');
            $table->string('reason');
            $table->timestamps();

            $table->index('reason');
            $table->index(['warehouse_id', 'reason']);
            $table->index(['inventory_id', 'reason']);
        });

        Schema::create('modules_magento2api_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('connection_id');
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->boolean('is_inventory_in_sync')->nullable();
            $table->boolean('is_in_stock')->nullable();
            $table->decimal('quantity', 20)->nullable();
            $table->decimal('magento_price', 20)->nullable();
            $table->decimal('magento_sale_price', 20)->nullable();
            $table->dateTime('magento_sale_price_start_date')->nullable();
            $table->dateTime('magento_sale_price_end_date')->nullable();
            $table->timestamp('stock_items_fetched_at')->nullable();
            $table->timestamp('base_prices_fetched_at')->nullable();
            $table->timestamp('special_prices_fetched_at')->nullable();
            $table->json('stock_items_raw_import')->nullable();
            $table->json('base_prices_raw_import')->nullable();
            $table->json('special_prices_raw_import')->nullable();
            $table->timestamps();
        });

        Schema::create('modules_inventory_reservations_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->nullable();
            $table->timestamps();

            $table->foreign('warehouse_id', 'modules_inventory_reservations_warehouse_id_foreign')
                ->references('id')
                ->on('warehouses');

            $table->index('warehouse_id', 'modules_inventory_reservations_warehouse_id_index');
        });

        Schema::create('modules_magento2api_connections', function (Blueprint $table) {
            $table->id();
            $table->string('base_url');
            $table->integer('magento_store_id')->nullable();
            $table->integer('inventory_source_warehouse_tag_id')->nullable();
            $table->integer('pricing_source_warehouse_id')->nullable();
            $table->longText('access_token_encrypted')->nullable();
            $table->timestamps();
        });

        Schema::create('modules_rmsapi_sales_imports', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->nullable()->index();
            $table->string('type')->nullable()->index();
            $table->unsignedBigInteger('inventory_movement_id')->nullable();
            $table->foreignId('connection_id');
            $table->foreignId('warehouse_id')->nullable();
            $table->foreignId('product_id')->nullable();
            $table->dateTime('reserved_at')->nullable();
            $table->dateTime('processed_at')->nullable();
            $table->string('sku')->nullable();
            $table->decimal('price', 20)->nullable();
            $table->decimal('quantity', 20)->nullable();
            $table->timestamp('transaction_time')->nullable();
            $table->string('transaction_number')->nullable();
            $table->integer('transaction_entry_id')->nullable();
            $table->string('comment')->nullable();
            $table->json('raw_import');
            $table->timestamps();

            $table->foreign('inventory_movement_id')
                ->references('id')
                ->on('inventory_movements')
                ->onDelete('SET NULL');

            $table->foreign('connection_id')
                ->references('id')
                ->on('modules_rmsapi_connections')
                ->onDelete('cascade');
        });

        if (!Schema::hasColumn(config('activitylog.table_name'), 'event')) {
            Schema::connection(config('activitylog.database_connection'))
                ->table(config('activitylog.table_name'), function (Blueprint $table) {
                    $table->string('event')->nullable()->after('subject_type');
                });
        }

        if (!Schema::hasColumn(config('activitylog.table_name'), 'batch_uuid')) {
            Schema::connection(config('activitylog.database_connection'))
                ->table(config('activitylog.table_name'), function (Blueprint $table) {
                    $table->uuid('batch_uuid')->nullable()->after('properties');
                });
        }

        Schema::create('modules_queue_monitor_jobs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable()->unique();
            $table->string('job_class')->index();
            $table->timestamp('dispatched_at')->default(DB::raw('CURRENT_TIMESTAMP'))->index();
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->bigInteger('seconds_dispatching')
                ->storedAs('TIMESTAMPDIFF(SECOND, dispatched_at, processing_at)')
                ->comment('TIMESTAMPDIFF(SECOND, dispatched_at, processing_at)');

            $table->bigInteger('seconds_running')
                ->storedAs('TIMESTAMPDIFF(SECOND, processing_at, processed_at)')
                ->comment('TIMESTAMPDIFF(SECOND, processing_at, processed_at)');
        });

        Schema::create('modules_inventory_movements_statistics_last28days_sale_movements', function (Blueprint $table) {
            $table->foreignId('inventory_movement_id')->unique('inventory_movement_id_index');
            $table->foreignId('inventory_id')->index('inventory_id_index');
            $table->unsignedBigInteger('warehouse_id')->nullable()->index('warehouse_id_index');
            $table->dateTime('sold_at')->index('sold_at_index');
            $table->decimal('quantity_sold', 20, 3);
            $table->boolean('included_in_7days')->nullable()->index('included_in_7days');
            $table->boolean('included_in_14days')->nullable()->index('included_in_14days');
            $table->boolean('included_in_28days')->nullable()->index('included_in_28days');
        });

        Schema::create('modules_slack_config', function (Blueprint $table) {
            $table->id();
            $table->string('incoming_webhook_url')->nullable();
            $table->timestamps();
        });

        $this->installSpatiePermissions();

        DB::statement('
            CREATE OR REPLACE VIEW modules_magento2api_products_prices_comparison_view AS
            SELECT
                modules_magento2api_products.id as modules_magento2api_products_id,
                products.sku,
                modules_magento2api_connections.magento_store_id,
                modules_magento2api_products.magento_price,
                products_prices.price as expected_price,

                modules_magento2api_products.magento_sale_price,
                products_prices.sale_price as expected_sale_price,

                modules_magento2api_products.magento_sale_price_start_date,
                products_prices.sale_price_start_date as expected_sale_price_start_date,

                modules_magento2api_products.magento_sale_price_end_date,
                products_prices.sale_price_end_date as expected_sale_price_end_date,

                modules_magento2api_products.base_prices_fetched_at,
                modules_magento2api_products.special_prices_fetched_at


            FROM modules_magento2api_products
            LEFT JOIN products ON products.id = modules_magento2api_products.product_id

            LEFT JOIN modules_magento2api_connections
              ON modules_magento2api_connections.id = modules_magento2api_products.connection_id

            LEFT JOIN products_prices
              ON products_prices.product_id = modules_magento2api_products.product_id
              AND products_prices.warehouse_id = modules_magento2api_connections.pricing_source_warehouse_id
        ');

        DB::statement("
            CREATE OR REPLACE VIEW modules_magento2api_products_inventory_comparison_view AS
            SELECT modules_magento2api_products.id AS modules_magento2api_products_id,
                   products.sku AS sku,
                   floor(max(modules_magento2api_products.quantity)) AS magento_quantity,
                   if((floor(sum(inventory.quantity_available)) < 0), 0, floor(sum(inventory.quantity_available))) AS expected_quantity,
                   modules_magento2api_products.stock_items_fetched_at

            from modules_magento2api_products

            left join modules_magento2api_connections
              ON modules_magento2api_connections.id = modules_magento2api_products.connection_id

            left join taggables
              ON taggables.tag_id = modules_magento2api_connections.inventory_source_warehouse_tag_id
              AND taggables.taggable_type = 'App\\\\Models\\\\Warehouse'

            left join warehouses
              ON warehouses.id = taggables.taggable_id

            left join inventory
              on inventory.product_id = modules_magento2api_products.product_id
              and inventory.warehouse_id = warehouses.id

            left join products
              on products.id = modules_magento2api_products.product_id

            group by modules_magento2api_products.id
        ");
    }

    private function installSpatiePermissions(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }

        if (!Schema::hasTable('permissions')) {
            Schema::create($tableNames['permissions'], function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('roles')) {
            Schema::create($tableNames['roles'], function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('model_has_permissions')) {
            Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames) {
                $table->unsignedBigInteger('permission_id');

                $table->string('model_type');
                $table->unsignedBigInteger($columnNames['model_morph_key']);
                $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');

                $table->foreign('permission_id')
                    ->references('id')
                    ->on($tableNames['permissions'])
                    ->onDelete('cascade');

                $table->primary(
                    ['permission_id', $columnNames['model_morph_key'], 'model_type'],
                    'model_has_permissions_permission_model_type_primary'
                );
            });
        }

        if (!Schema::hasTable('model_has_roles')) {
            Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames) {
                $table->unsignedBigInteger('role_id');

                $table->string('model_type');
                $table->unsignedBigInteger($columnNames['model_morph_key']);
                $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');

                $table->foreign('role_id')
                    ->references('id')
                    ->on($tableNames['roles'])
                    ->onDelete('cascade');

                $table->primary(
                    ['role_id', $columnNames['model_morph_key'], 'model_type'],
                    'model_has_roles_role_model_type_primary'
                );
            });
        }

        if (!Schema::hasTable('role_has_permissions')) {
            Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames) {
                $table->unsignedBigInteger('permission_id');
                $table->unsignedBigInteger('role_id');

                $table->foreign('permission_id')
                    ->references('id')
                    ->on($tableNames['permissions'])
                    ->onDelete('cascade');

                $table->foreign('role_id')
                    ->references('id')
                    ->on($tableNames['roles'])
                    ->onDelete('cascade');

                $table->primary(['permission_id', 'role_id'], 'role_has_permissions_permission_id_role_id_primary');
            });
        }
        if (!Schema::hasTable('telescope_entries')) {
            Schema::create('telescope_entries', function (Blueprint $table) {
                $table->bigIncrements('sequence');
                $table->uuid('uuid');
                $table->uuid('batch_id');
                $table->string('family_hash')->nullable();
                $table->boolean('should_display_on_index')->default(true);
                $table->string('type', 20);
                $table->longText('content');
                $table->dateTime('created_at')->nullable();

                $table->unique('uuid');
                $table->index('batch_id');
                $table->index('family_hash');
                $table->index('created_at');
                $table->index(['type', 'should_display_on_index']);
            });
        }

        if (!Schema::hasTable('telescope_entries_tags')) {
            Schema::create('telescope_entries_tags', function (Blueprint $table) {
                $table->uuid('entry_uuid');
                $table->string('tag');

                $table->index(['entry_uuid', 'tag']);
                $table->index('tag');

                $table->foreign('entry_uuid')
                    ->references('uuid')
                    ->on('telescope_entries')
                    ->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('telescope_monitoring')) {
            Schema::create('telescope_monitoring', function (Blueprint $table) {
                $table->string('tag');
            });
        }

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }
};
