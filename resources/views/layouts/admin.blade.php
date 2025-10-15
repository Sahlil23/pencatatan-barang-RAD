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
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

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
              <!-- <span class="app-brand-text demo menu-text fw-bolder ms-2">BANJARMASIN</span> -->
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
          </ul>
        </aside>
        <!-- / Menu -->
 
        <!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->
          <nav
            class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
            id="layout-navbar"
          >
            <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
              <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                <i class="bx bx-menu bx-sm"></i>
              </a>
            </div>

            <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
              <!-- Search -->
              <div class="navbar-nav align-items-center">
                <div class="nav-item d-flex align-items-center">
                  <i class="bx bx-search fs-4 lh-0"></i>
                  <input
                    type="text"
                    class="form-control border-0 shadow-none"
                    placeholder="Search..."
                    aria-label="Search..."
                  />
                </div>
              </div>
              <!-- /Search -->

              <ul class="navbar-nav flex-row align-items-center ms-auto">
                <!-- User -->
                <li class="nav-item navbar-dropdown dropdown-user dropdown">
                  <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                      <img src="{{ asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle" />
                    </div>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                      <a class="dropdown-item" href="#">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar avatar-online">
                              <img src="{{ asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle" />
                            </div>
                          </div>
                          <div class="flex-grow-1">
                            <span class="fw-semibold d-block">Admin</span>
                            <small class="text-muted">Administrator</small>
                          </div>
                        </div>
                      </a>
                    </li>
                    <li>
                      <div class="dropdown-divider"></div>
                    </li>
                    <li>
                      <a class="dropdown-item" href="{{ route('profile') }}">
                        <i class="bx bx-user me-2"></i>
                        <span class="align-middle">My Profile</span>
                      </a>
                    </li>

                    <!-- Logout Link - Fix this -->
                    <li>
                      <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left;">
                          <i class="bx bx-power-off me-2"></i>
                          <span class="align-middle">Log Out</span>
                        </button>
                      </form>
                    </li>
                  </ul>
                </li>
                <!--/ User -->
              </ul>
            </div>
          </nav>
          <!-- / Navbar -->

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
                </div
        <!-- / Layout page -->
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

