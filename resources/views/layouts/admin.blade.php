<!DOCTYPE html>
<html
  lang="en"
  class="light-style layout-menu-fixed"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="{{ asset('assets/') }}/"
  data-template="vertical-menu-template-free"
>
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Chicking - Banjarmasin')</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/Chicking-logo-bjm.png') }}">

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/Chicking-logo-bjm.png') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />

    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>

    <link rel="stylesheet" href="{{ asset('assets/css/custom-pagination.css') }}">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
  
    @stack('styles')
  </head>

  <body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <!-- Menu -->
        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
          <div class="app-brand demo">
            <a href="{{ route('beranda') }}" class="app-brand-link">
              <span class="app-brand-logo demo">
                <img src="{{ asset('assets/img/chicking-logo-bjm.png') }}" alt="Chicking Logo" width="180" height="120" />
              </span>
            </a>

            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
              <i class="bx bx-chevron-left bx-sm align-middle"></i>
            </a>
          </div>

          <div class="menu-inner-shadow"></div>

          <ul class="menu-inner py-1">
            <!-- Dashboard -->
            <li class="menu-item {{ request()->routeIs('beranda') || request()->routeIs('dashboard') ? 'active' : '' }}">
              <a href="{{ route('beranda') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Dashboard">Dashboard</div>
              </a>
            </li>
            <!-- ========================================
                 2. WAREHOUSE MANAGEMENT
                 ======================================== -->
            @if(Auth::user()->isManager() || Auth::user()->isSuperAdmin())
            <li class="menu-header small text-uppercase"><span class="menu-header-text">Warehouse Management</span></li>
            <li class="menu-item {{ request()->routeIs('warehouses.*') ? 'active open' : '' }}">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-building"></i>
                <div data-i18n="Warehouses">Warehouse</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('warehouses.index') ? 'active' : '' }}">
                  <a href="{{ route('warehouses.index') }}" class="menu-link"><div data-i18n="List">Semua Warehouse</div></a>
                </li>
                @if(Auth::user()->isSuperAdmin() || Auth::user()->isCentralManager())
                <li class="menu-item {{ request()->routeIs('warehouses.create') ? 'active' : '' }}">
                  <a href="{{ route('warehouses.create') }}" class="menu-link"><div data-i18n="Create">Tambah Warehouse</div></a>
                </li>
                @endif
              </ul>
            </li>
            @endif

            <!-- ========================================
                 3. CENTRAL WAREHOUSE
                 ======================================== -->
            @if(Auth::user()->canAccessWarehouseType('central'))
            <li class="menu-header small text-uppercase"><span class="menu-header-text">Central Warehouse</span></li>
            <li class="menu-item {{ request()->routeIs('central-warehouse.*') ? 'active open' : '' }}">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-store"></i>
                <div data-i18n="CentralWarehouse">Central Warehouse</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('central-warehouse.index') ? 'active' : '' }}">
                  <a href="{{ route('central-warehouse.index') }}" class="menu-link"><div data-i18n="Stock">Stock Balance</div></a>
                </li>
                @if(Auth::user()->isCentralLevel() || Auth::user()->isSuperAdmin())
                <li class="menu-item {{ request()->routeIs('central-warehouse.receive-stock') ? 'active' : '' }}">
                  <a href="{{ route('central-warehouse.receive-stock') }}" class="menu-link"><div data-i18n="Receive">Receive Stock</div></a>
                </li>
                <li class="menu-item {{ request()->routeIs('central-warehouse.distribute-stock') ? 'active' : '' }}">
                  <a href="{{ route('central-warehouse.distribute-stock') }}" class="menu-link"><div data-i18n="Receive">Distribute</div></a>
                </li>
                @endif
                <li class="menu-item {{ request()->routeIs('central-warehouse.transactions') ? 'active' : '' }}">
                  <a href="{{ route('central-warehouse.transactions') }}" class="menu-link"><div data-i18n="Transactions">Transactions</div></a>
                </li>
              </ul>
            </li>
            @endif

            <!-- ========================================
                 4. BRANCH WAREHOUSE
                 ======================================== -->
            @if(Auth::user()->canAccessWarehouseType('branch'))
            <li class="menu-header small text-uppercase"><span class="menu-header-text">Branch Warehouse</span></li>
            <li class="menu-item {{ request()->routeIs('branch-warehouse.*') ? 'active open' : '' }}">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-store"></i>
                <div data-i18n="BranchWarehouse">Branch Warehouse</div>
              </a>
              <ul class="menu-sub">
                @php
                  $accessibleBranchWarehouses = Auth::user()->getAccessibleBranchWarehouses();
                @endphp
                
                @if($accessibleBranchWarehouses->count() === 1)
                  {{-- Direct link if only one branch warehouse --}}
                  <li class="menu-item {{ request()->routeIs('branch-warehouse.show') ? 'active' : '' }}">
                    <a href="{{ route('branch-warehouse.show', $accessibleBranchWarehouses->first()->id) }}" class="menu-link">
                      <div data-i18n="Stock">{{ $accessibleBranchWarehouses->first()->warehouse_name }}</div>
                    </a>
                  </li>
                  <li class="menu-item {{ request()->routeIs('branch-warehouse.pending-distributions') ? 'active' : '' }}">
                    <a href="{{ route('branch-warehouse.pending-distributions', $accessibleBranchWarehouses->first()->id) }}" class="menu-link">
                      <div data-i18n="Stock">Pending Distributions</div>
                    </a>
                  </li>
                  <li class="menu-item {{ request()->routeIs('branch-warehouse.adjust-form') ? 'active' : '' }}">
                    <a href="{{ route('branch-warehouse.adjust-form', $accessibleBranchWarehouses->first()->id) }}" class="menu-link">
                      <div data-i18n="Stock">Adjust Form</div>
                    </a>
                  </li>
                  <li class="menu-item {{ request()->routeIs('branch-warehouse.receive-form') ? 'active' : '' }}">
                    <a href="{{ route('branch-warehouse.receive-form', $accessibleBranchWarehouses->first()->id) }}" class="menu-link">
                      <div data-i18n="Stock">Add Stock</div>
                    </a>
                  </li>
                  <li class="menu-item {{ request()->routeIs('branch-warehouse.distribute-form') ? 'active' : '' }}">
                    <a href="{{ route('branch-warehouse.distribute-form', $accessibleBranchWarehouses->first()->id) }}" class="menu-link">
                      <div data-i18n="Stock">Distribute</div>
                    </a>
                  </li>
                  <li class="menu-item {{ request()->routeIs('branch-warehouse.distributions') ? 'active' : '' }}">
                    <a href="{{ route('branch-warehouse.distributions', $accessibleBranchWarehouses->first()->id) }}" class="menu-link">
                      <div data-i18n="Stock">Transaksi</div>
                    </a>
                  </li>
                @else
                  {{-- List all accessible branch warehouses --}}
                  <li class="menu-item {{ request()->routeIs('branch-warehouse.index') ? 'active' : '' }}">
                    <a href="{{ route('branch-warehouse.index') }}" class="menu-link"><div data-i18n="Read">Semua Branch</div></a>
                  </li>
                @endif
              </ul>
            </li>
            @endif

            <!-- ========================================
                 5. OUTLET WAREHOUSE
                 ======================================== -->
            @if(Auth::user()->canAccessWarehouseType('outlet'))
            <li class="menu-header small text-uppercase"><span class="menu-header-text">Outlet Warehouse</span></li>
            <li class="menu-item {{ request()->routeIs('outlet-warehouse.*') ? 'active open' : '' }}">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-store"></i>
                <div data-i18n="OutletWarehouse">Outlet Warehouse</div>
              </a>
              <ul class="menu-sub">
                @php
                  $accessibleOutletWarehouses = Auth::user()->getAccessibleOutletWarehouses();
                @endphp
                
                @if($accessibleOutletWarehouses->count() === 1)
                  <li class="menu-item {{ request()->routeIs('outlet-warehouse.show') ? 'active' : '' }}">
                    <a href="{{ route('outlet-warehouse.show', ['warehouseId' => $accessibleOutletWarehouses->first()->id, 'detail' => 'detail']) }}" class="menu-link">
                      <div data-i18n="Stock">{{ $accessibleOutletWarehouses->first()->warehouse_name }}</div>
                    </a>
                  </li>
                  <li class="menu-item {{ request()->routeIs('outlet-warehouse.receive.create') ? 'active' : '' }}">
                    <a href="{{ route('outlet-warehouse.receive.create', ['warehouseId' => $accessibleOutletWarehouses->first()->id, 'detail' => 'detail']) }}" class="menu-link">
                      <div data-i18n="Stock">Add Stock</div>
                    </a>
                  </li>
                  <li class="menu-item {{ request()->routeIs('outlet-warehouse.distribute.create') ? 'active' : '' }}">
                    <a href="{{ route('outlet-warehouse.distribute.create', ['warehouseId' => $accessibleOutletWarehouses->first()->id, 'detail' => 'detail']) }}" class="menu-link">
                      <div data-i18n="Stock">Distribute</div>
                    </a>
                  </li>
                  <li class="menu-item {{ request()->routeIs('outlet-warehouse.adjustment.create') ? 'active' : '' }}">
                    <a href="{{ route('outlet-warehouse.adjustment.create', ['warehouseId' => $accessibleOutletWarehouses->first()->id, 'detail' => 'detail']) }}" class="menu-link">
                      <div data-i18n="Stock">Adjustment</div>
                    </a>
                  </li>
                  <li class="menu-item {{ request()->routeIs('outlet-warehouse.transactions') ? 'active' : '' }}">
                    <a href="{{ route('outlet-warehouse.transactions', ['warehouseId' => $accessibleOutletWarehouses->first()->id, 'detail' => 'detail']) }}" class="menu-link">
                      <div data-i18n="Stock">Transactions</div>
                    </a>
                  </li>
                @else
                  <li class="menu-item {{ request()->routeIs('outlet-warehouse.index') ? 'active' : '' }}">
                    <a href="{{ route('outlet-warehouse.index') }}" class="menu-link"><div data-i18n="Read">Semua Outlet</div></a>
                  </li>
                @endif
              </ul>
            </li>
            @endif

            <!-- ========================================
                 6. KITCHEN STOCK
                 ======================================== -->
            <li class="menu-header small text-uppercase"><span class="menu-header-text">Kitchen Stock</span></li>
            <li class="menu-item {{ request()->routeIs('kitchen.*') ? 'active open' : '' }}">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-restaurant"></i>
                <div data-i18n="Kitchen">Kitchen Stock</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('kitchen.index') ? 'active' : '' }}">
                  <a href="{{ route('kitchen.index') }}" class="menu-link"><div data-i18n="Stock">Stock Balance</div></a>
                </li>
                @if(Auth::user()->isManager() || Auth::user()->isSuperAdmin())
                <li class="menu-item {{ request()->routeIs('kitchen.usage') ? 'active' : '' }}">
                  <a href="{{ route('kitchen.usage') }}" class="menu-link"><div data-i18n="Usage">Stock Usage</div></a>
                </li>
                @endif
                <li class="menu-item {{ request()->routeIs('kitchen.transactions') ? 'active' : '' }}">
                  <a href="{{ route('kitchen.transactions') }}" class="menu-link"><div data-i18n="Transactions">Transactions</div></a>
                </li>
              </ul>
            </li>

            <!-- ========================================
                 7. RECIPES
                 ======================================== -->
            <li class="menu-header small text-uppercase">
              <span class="menu-header-text">Resep</span>
            </li>
            <li class="menu-item {{ request()->routeIs('recipes.*') ? 'active open' : '' }}">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-food-menu"></i>
                <div data-i18n="Resep">Resep</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('recipes.index') ? 'active' : '' }}">
                  <a href="{{ route('recipes.index') }}" class="menu-link">
                    <div data-i18n="All Recipes">Semua Resep</div>
                  </a>
                </li>
                @if(Auth::user()->isManager())
                <li class="menu-item {{ request()->routeIs('recipes.create') ? 'active' : '' }}">
                  <a href="{{ route('recipes.create') }}" class="menu-link">
                    <div data-i18n="Add Recipe">Tambah Resep</div>
                  </a>
                </li>
                @endif
              </ul>
            </li>

            <!-- ========================================
                 8. BACKUP (Super Admin Only)
                 ======================================== -->
            @if(Auth::user()->isSuperAdmin())
            <li class="menu-header small text-uppercase">
              <span class="menu-header-text">System</span>
            </li>
            <li class="menu-item {{ request()->routeIs('backup.*') ? 'active open' : '' }}">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-hdd"></i>
                <div data-i18n="Backup">Backup & Restore</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('backup.index') ? 'active' : '' }}">
                  <a href="{{ route('backup.index') }}" class="menu-link">
                    <div data-i18n="Database Backup">Database Backup</div>
                  </a>
                </li>
              </ul>
            </li>
            @endif
            <!-- ========================================
                 1. DATA MASTER
                 ======================================== -->
            <li class="menu-header small text-uppercase"><span class="menu-header-text">Data Master</span></li>

            <li class="menu-item {{ request()->routeIs('items.*') ? 'active open' : '' }}">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-package"></i>
                <div data-i18n="Products">Barang</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('items.index') ? 'active' : '' }}">
                  <a href="{{ route('items.index') }}" class="menu-link"><div data-i18n="All Products">Semua Barang</div></a>
                </li>
                @if(Auth::user()->isManager() || Auth::user()->isSuperAdmin())
                <li class="menu-item {{ request()->routeIs('items.create') ? 'active' : '' }}">
                  <a href="{{ route('items.create') }}" class="menu-link"><div data-i18n="Add Product">Tambah Barang</div></a>
                </li>
                @endif
              </ul>
            </li>
            @if(Auth::user()->isManager() || Auth::user()->isSuperAdmin())
            <li class="menu-item {{ request()->routeIs('categories.*') ? 'active open' : '' }}">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-category"></i>
                <div data-i18n="Categories">Kategori</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('categories.index') ? 'active' : '' }}">
                  <a href="{{ route('categories.index') }}" class="menu-link"><div data-i18n="All Categories">Semua Kategori</div></a>
                </li>
                <li class="menu-item {{ request()->routeIs('categories.create') ? 'active' : '' }}">
                  <a href="{{ route('categories.create') }}" class="menu-link"><div data-i18n="Add Category">Tambah Kategori</div></a>
                </li>
              </ul>
            </li>
            @endif

            @if(Auth::user()->isManager() || Auth::user()->isSuperAdmin())
            <li class="menu-item {{ request()->routeIs('suppliers.*') ? 'active open' : '' }}">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-group"></i>
                <div data-i18n="Suppliers">Suplier</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('suppliers.index') ? 'active' : '' }}">
                  <a href="{{ route('suppliers.index') }}" class="menu-link"><div data-i18n="All Suppliers">Semua Suplier</div></a>
                </li>
                <li class="menu-item {{ request()->routeIs('suppliers.create') ? 'active' : '' }}">
                  <a href="{{ route('suppliers.create') }}" class="menu-link"><div data-i18n="Add Supplier">Tambah Suplier</div></a>
                </li>
              </ul>
            </li>
            @endif
            <!-- ========================================
                 9. USER MENU
                 ======================================== -->
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">User</span>
            </li>

            @if(Auth::user()->canManageUsers())
                <li class="menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <a href="{{ route('users.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-user"></i>
                        <div data-i18n="ManageUsers">Manage User</div>
                    </a>
                </li>
            @endif

            <li class="menu-item {{ request()->routeIs('profile') ? 'active' : '' }}">
                <a href="{{ route('profile') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-id-card"></i>
                    <div data-i18n="Profile">Profile</div>
                </a>
            </li>

            <li class="menu-item">
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="menu-link w-100 text-start border-0 bg-transparent" style="padding: 0.625rem 1rem;">
                        <i class="menu-icon tf-icons bx bx-power-off text-danger"></i>
                        <div data-i18n="Logout">Logout</div>
                    </button>
                </form>
            </li>
          </ul>
        </aside>
        <!-- / Menu -->

        <!-- Layout container -->
        <div class="layout-page">
          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- Content -->
            <div class="container-xxl flex-grow-1 container-p-y">
              @yield('content')
            </div>
            <!-- / Content -->

            <!-- Footer -->
            <footer class="content-footer footer bg-footer-theme">
              <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
                <div class="mb-2 mb-md-0">
                  ©
                  <script>
                    document.write(new Date().getFullYear());
                  </script>
                  , Chicking BJM - Banjarmasin
                </div>
              </div>
            </footer>
          </div>
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
      </div>
      <!-- / Layout wrapper -->

      <!-- Success/Error Messages -->
      @if(session('success'))
      <div class="bs-toast toast toast-placement-ex m-3 bg-success top-0 end-0 fade show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <i class="bx bx-bell me-2"></i>
          <div class="me-auto fw-semibold">Success</div>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          {{ session('success') }}
        </div>
      </div>
      @endif

      @if(session('error'))
      <div class="bs-toast toast toast-placement-ex m-3 bg-danger top-0 end-0 fade show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <i class="bx bx-bell me-2"></i>
          <div class="me-auto fw-semibold">Error</div>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
          {{ session('error') }}
        </div>
      </div>
      @endif

      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
      
      <!-- Select2 JS -->
      <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
      <!-- Core JS -->
      <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
      <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
      <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
      <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
      <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>

      <!-- Vendors JS -->
      <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>

      <!-- Main JS -->
      <script src="{{ asset('assets/js/main.js') }}"></script>

      @stack('scripts')
      <script>
      // Global CSV Download Function
      function downloadCSV(filename, headers, rows) {
        let csvContent = headers.join(',') + '\n';
        rows.forEach(row => {
          csvContent += row.map(cell => `"${cell}"`).join(',') + '\n';
        });
        
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        link.setAttribute('href', url);
        link.setAttribute('download', filename + '_' + new Date().toISOString().slice(0,10) + '.csv');
        link.style.visibility = 'hidden';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      }
      </script>
    </body>
  </html>
