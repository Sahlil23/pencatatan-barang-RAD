{{-- resources/views/layouts/admin.blade.php --}}
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
            <li class="menu-item {{ request()->routeIs('beranda') ? 'active' : '' }}">
              <a href="{{ route('beranda') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Dashboard">Dashboard</div>
              </a>
            </li>

            <!-- Inventory Management -->
            <li class="menu-header small text-uppercase">
              <span class="menu-header-text">Inventory</span>
            </li>

            <!-- Barang/Produk -->
            <li class="menu-item {{ request()->routeIs('items.*') ? 'active open' : '' }}">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-package"></i>
                <div data-i18n="Products">Barang</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('items.index') ? 'active' : '' }}">
                  <a href="{{ route('items.index') }}" class="menu-link">
                    <div data-i18n="All Products">Semua Barang</div>
                  </a>
                </li>
                <li class="menu-item {{ request()->routeIs('items.create') ? 'active' : '' }}">
                  <a href="{{ route('items.create') }}" class="menu-link">
                    <div data-i18n="Add Product">Tambah Barang</div>
                  </a>
                </li>
                <li class="menu-item {{ request()->routeIs('items.low-stock') ? 'active' : '' }}">
                  <a href="{{ route('items.low-stock') }}" class="menu-link">
                    <div data-i18n="Low Stock">Stok Menipis</div>
                  </a>
                </li>
                <li class="menu-item {{ request()->routeIs('items.report') ? 'active' : '' }}">
                  <a href="{{ route('items.report') }}" class="menu-link">
                    <div data-i18n="Low Stock">Laporan Stok</div>
                  </a>
                </li>
              </ul>
            </li>

            <!-- Transaksi -->
            <li class="menu-header small text-uppercase">
              <span class="menu-header-text">Transaksi</span>
            </li>

            <!-- Stock Transactions -->
            <li class="menu-item {{ request()->routeIs('stock-transactions.*') ? 'active open' : '' }}">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-transfer"></i>
                <div data-i18n="Stock Transactions">Transaksi Stok</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('stock-transactions.index') ? 'active' : '' }}">
                  <a href="{{ route('stock-transactions.index') }}" class="menu-link">
                    <div data-i18n="All Transactions">Semua Transaksi</div>
                  </a>
                </li>
                <li class="menu-item {{ request()->routeIs('stock-transactions.create') ? 'active' : '' }}">
                  <a href="{{ route('stock-transactions.create') }}" class="menu-link">
                    <div data-i18n="Add Transaction">Input Transaksi</div>
                  </a>
                </li>
                <li class="menu-item {{ request()->routeIs('stock-transactions.report') ? 'active' : '' }}">
                  <a href="{{ route('stock-transactions.report') }}" class="menu-link">
                    <div data-i18n="Reports">Laporan</div>
                  </a>
                </li>
              </ul>
            </li>

            <!-- Master Data -->
            <li class="menu-header small text-uppercase">
              <span class="menu-header-text">Master Data</span>
            </li>
            
            <!-- Kategori Produk -->
            <li class="menu-item {{ request()->routeIs('categories.*') ? 'active open' : '' }}">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-category"></i>
                <div data-i18n="Categories">Kategori</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('categories.index') ? 'active' : '' }}">
                  <a href="{{ route('categories.index') }}" class="menu-link">
                    <div data-i18n="All Categories">Semua Kategori</div>
                  </a>
                </li>
                <li class="menu-item {{ request()->routeIs('categories.create') ? 'active' : '' }}">
                  <a href="{{ route('categories.create') }}" class="menu-link">
                    <div data-i18n="Add Category">Tambah Kategori</div>
                  </a>
                </li>
              </ul>
            </li>

            <!-- Supplier -->
            <li class="menu-item {{ request()->routeIs('suppliers.*') ? 'active open' : '' }}">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-group"></i>
                <div data-i18n="Suppliers">Supplier</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('suppliers.index') ? 'active' : '' }}">
                  <a href="{{ route('suppliers.index') }}" class="menu-link">
                    <div data-i18n="All Suppliers">Semua Supplier</div>
                  </a>
                </li>
                <li class="menu-item {{ request()->routeIs('suppliers.create') ? 'active' : '' }}">
                  <a href="{{ route('suppliers.create') }}" class="menu-link">
                    <div data-i18n="Add Supplier">Tambah Supplier</div>
                  </a>
                </li>
              </ul>
            </li>

            <!-- Recipe -->
            <li class="menu-header small text-uppercase">
              <span class="menu-header-text">Resep</span>
            </li>

            <!-- Recipe -->
            <li class="menu-item {{ request()->routeIs('recipes.*') ? 'active open' : '' }}">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-transfer"></i>
                <div data-i18n="Resep">Resep</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('recipes.index') ? 'active' : '' }}">
                  <a href="{{ route('recipes.index') }}" class="menu-link">
                    <div data-i18n="All Recipes">Semua Resep</div>
                  </a>
                </li>
                <li class="menu-item {{ request()->routeIs('recipes.create') ? 'active' : '' }}">
                  <a href="{{ route('recipes.create') }}" class="menu-link">
                    <div data-i18n="Add Recipe">Tambah Resep</div>
                  </a>
                </li>
              </ul>
            </li>

            <!-- Backup -->
            <li class="menu-header small text-uppercase">
              <span class="menu-header-text">Backup</span>
            </li>
            <li class="menu-item {{ request()->routeIs('backup.*') ? 'active open' : '' }}">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-hdd"></i>
                <div data-i18n="Backup">Backup</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item">
                  <a href="{{ route('backup.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-hdd"></i>
                    <div data-i18n="Database Backup">Database Backup</div>
                  </a>
                </li>
              </ul>
            </li>

            <!-- User Management - Admin Only -->
            @if(Auth::user()->isAdmin())
            <li class="menu-header small text-uppercase">
              <span class="menu-header-text">User Management</span>
            </li>
            <li class="menu-item">
              <a href="{{ route('users.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user"></i>
                <div data-i18n="Users">Manage Users</div>
              </a>
            </li>
            @endif

            <li class="menu-header small text-uppercase">
              <span class="menu-header-text">Account</span>
            </li>
            <li class="menu-item {{ request()->routeIs('backup.*') ? 'active open' : '' }}">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-user"></i>
                <div data-i18n="Account">Account</div>
              </a>
              <ul class="menu-sub">
                <li class="menu-item">
                  <a href="{{ route('profile') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-user"></i>
                    <div data-i18n="Profile">Profile</div>
                  </a>
                </li>
                            <li class="menu-item">
              <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="menu-link w-100 text-start border-0 bg-transparent" style="padding: 0.625rem 1rem;">
                  <i class="menu-icon tf-icons bx bx-power-off"></i>
                  <div data-i18n="Logout">Logout</div>
                </button>
              </form>
            </li>
              </ul>
            </li>
            <!-- Logout -->
              <!-- <li class="menu-item">
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                  @csrf
                  <button type="submit" class="menu-link w-100 text-start border-0 bg-transparent" style="padding: 0.625rem 1rem;">
                    <i class="menu-icon tf-icons bx bx-power-off"></i>
                    <div data-i18n="Logout">Logout</div>
                  </button>
                </form>
              </li>
            </ul> -->
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
 