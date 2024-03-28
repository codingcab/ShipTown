@auth
{{--    <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm mb-0">--}}
    <nav class="navbar navbar-expand-md navbar-light mb-2 p-0 bg-primary" style="z-index: 1021">
        <div class="container text-white">
            <div class="d-block mb-0 navbar-dark bg-primary">
                <div class="btn-group" role="group" aria-label="Basic example">
                    <a style="width: 40px" id="dashboard_link" class="btn btn-primary" href="{{ route('dashboard') }}"><font-awesome-icon icon="chart-bar" class="fa-lg"></font-awesome-icon></a>
                    <a id="products_link" class="btn btn-primary" href="{{ route('products') }}">{{ __('Products') }}</a>
                    <a id="orders_link" class="btn btn-primary" href="{{ route('orders') }}">{{ __('Orders') }}</a>

                    @if(Auth::user()->warehouse_id)
                        <!-- Tools -->
                        <div class="dropdown">
                            <a id="tools_link" class="dropdown-toggle btn btn-primary" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ __('Tools') }}<span class="caret"></span>
                            </a>

                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-md-left w-auto text-left bg-primary " aria-labelledby="navbarDropdown" >
                                <a class="dropdown-item text-white lightHover mt-1" id="stocktaking_link" href="{{ route('stocktaking') }}">{{ __('Stocktaking') }}</a>
                                <a class="dropdown-item text-white lightHover mt-1" id="restocking_link" href="{{ route('reports.restocking' , ['sort' => '-quantity_required', 'cache_name' => 'restocking_page']) }}">{{ __('Restocking') }}</a>
                                <a class="dropdown-item text-white lightHover mt-1" id="data_collector_link" href="{{ route('data-collector') }}">{{ __('Data Collector') }}</a>
                                <a class="dropdown-item text-white lightHover mt-1 mb-1" id="inventory_movements_link" href="{{ route('reports.inventory-movements') }}">{{ __('Inventory Movements') }}</a>
                                <a class="dropdown-item text-white lightHover mt-1 mb-1" id="shelf_label_printing_link" href="{{ route('tools.shelf-label-printing') }}">{{ __('Shelf Label Printing') }}</a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Burger menu -->
            <button id="navToggleButton" class="btn btn-primary float-right border-0 btn-primary d-m-block d-md-none " type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                <font-awesome-icon icon="bars" class="fa-lg"></font-awesome-icon>
            </button>


            <!-- Right Side Of Navbar -->
            <div class="collapse navbar-collapse bg-primary" id="navbarSupportedContent" style="transition: none !important;">
                <div class="flex-fill bg-danger row col"></div>
                <ul class="navbar-nav mr-auto text-center text-md-left">
                    <!-- Picklist Dropdown -->
                    <li class="nav-item dropdown">
                        <a id="picklists_link" class="dropdown-toggle btn btn-primary" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            {{ __('Picklist') }}<span class="caret"></span>
                        </a>

                        <div class="dropdown-menu dropdown-menu-left bg-primary border-0" aria-labelledby="navbarDropdown">
                           @foreach ($navigationMenuPicklist as $menu)
                                <a id="picklistItem{{ $loop->index }}" class="dropdown-item text-white lightHover" href="{{ $menu->url }}">{{ $menu->name }}</a>
                            @endforeach
                        </div>
                    </li>

                    <!-- Packlist Dropdown -->
                    <li class="nav-item dropdown">
                        <a id="packlists_link" class="dropdown-toggle btn btn-primary" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            {{ __('Packlist') }}<span class="caret"></span>
                        </a>

                        <div class="dropdown-menu dropdown-menu-left bg-primary" aria-labelledby="navbarDropdown">
                            @foreach ($navigationMenuPacklist as $menu)
                                <a class="dropdown-item text-white lightHover" href="{{ $menu->url }}">{{ $menu->name }}</a>
                            @endforeach
                        </div>
                    </li>

                    <!-- Reports Dropdown -->
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown3" class="dropdown-toggle btn btn-primary" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            {{ __('Reports') }}<span class="caret"></span>
                        </a>

                        <div class="dropdown-menu left bg-primary" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item text-white lightHover" href="{{ route('inventory-dashboard') }}">{{ __('Inventory Dashboard') }}</a>
                            <a class="dropdown-item text-white lightHover" href="{{ route('fulfillment-dashboard') }}">{{ __('Fulfillment Dashboard') }}</a>
                            <a class="dropdown-item text-white lightHover" href="{{ route('fulfillment-statistics') .'?between_dates=-7days,now' }}">{{ __('Fulfillment Statistics') }}</a>
                            <a class="dropdown-item text-white lightHover" href="{{ route('reports.picks') }}">{{ __('Order Picks') }}</a>
                            <a class="dropdown-item text-white lightHover" href="{{ route('reports.shipments') }}">{{ __('Order Shipments') }}</a>
                            <a class="dropdown-item text-white lightHover" href="{{ route('reports.inventory', ['per_page' => 50, 'filter[quantity_between]=0.01,999999999', 'sort' => '-quantity']) }}">{{ __('Inventory') }}</a>
                            <a class="dropdown-item text-white lightHover" href="{{ route('reports.inventory-movements') }}">{{ __('Inventory Movements') }}</a>
                            <a class="dropdown-item text-white lightHover" href="{{ route('reports.inventory-movements-summary').'?filter[created_at_between]=-7days,now&per_page=1000' }}">{{ __('Inventory Movements Summary') }}</a>
                            <a class="dropdown-item text-white lightHover" href="{{ route('reports.restocking') }}">{{ __('Restocking') }}</a>
                            <a class="dropdown-item text-white lightHover" href="{{ route('reports.stocktake-suggestions.index') }}">{{ __('Stocktake Suggestions') }}</a>
                            <a class="dropdown-item text-white lightHover" href="{{ route('activity-log') }}">{{ __('Activity Log') }}</a>
{{--                            <a class="dropdown-item text-white lightHover" href="{{ url('products-merge?sku1=45&sku2=44') }}">{{ __('products-merge') }}</a>--}}

                            @if(count($navigationMenuReports) > 0)
                                <hr v-if='{{ count($navigationMenuReports) > 0 }}' class="mb-1 mt-1">
                                @foreach ($navigationMenuReports as $menu)
                                    <a class="dropdown-item text-white lightHover" href="{{ $menu->url }}">
                                        {{ $menu->name }}
                                    </a>
                                @endforeach
                            @endif
                        </div>
                    </li>
                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="navbar-nav ml-auto text-center text-md-left">
                    <li class="nav-item dropdown"> <!-- User dropdown menu -->
                        <a id="navbarDropdown" class="nav-link text-white lightHover dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            {{ Auth::user()->name }} ({{ Auth::user()->warehouse ? Auth::user()->warehouse->code : 'All Locations' }})<span class="caret"></span>
                        </a>

                        <!-- Menu Items END -->
                        <div class="dropdown-menu dropdown-menu-right bg-primary text-center text-md-left" aria-labelledby="navbarDropdown">
                            {{-- Profile --}}
                            <a class="dropdown-item text-white lightHover" href="{{ route('setting-profile') }}">{{ __('Profile') }}</a>

                            @hasrole('admin')
                                <a class="dropdown-item text-white lightHover" href="{{ route('settings') }}">{{ __('Settings') }}</a>
                            @endhasrole


                            <a class="dropdown-item text-white lightHover" href="https://ship.town/academy" target="_blank">{{ __('Academy') }}</a>
                            <a class="dropdown-item text-white lightHover" href="https://www.youtube.com/channel/UCl04S5dRXop1ZdZsOqY3OnA" target="_blank">{{ __('YouTube') }}</a>
                            <a class="dropdown-item text-white lightHover" href="https://docs.google.com/spreadsheets/d/1IagdPL-ZKOz0-_Rf83ukhDKj02S-DpgLTz7LrvzWWR4/copy" target="_blank">{{ __('Shelf Labels') }}</a>

                            <!-- Logout -->
                            <a class="dropdown-item text-white lightHover" href="{{ route('logout') }}"
                               onclick="event.preventDefault();
                               document.getElementById('logout-form').submit();">
                                 {{ __('Logout') }}
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>

                        </div>
                    </li>

                </ul>

            </div>


        </div>
    </nav>
@endauth
