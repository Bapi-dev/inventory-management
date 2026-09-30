<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — BagSmart Wholesale</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-bg: #f8fafc;
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            --sidebar-active: #2563eb;
            --sidebar-text: #94a3b8;
            --sidebar-text-active: #ffffff;
            --accent-color: #2563eb;
            --accent-hover: #1d4ed8;
            --card-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05);
            --card-border: #e2e8f0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--primary-bg);
            color: #1e293b;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        #sidebar {
            width: 260px;
            min-height: 100vh;
            background-color: var(--sidebar-bg);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1040;
        }

        #sidebar .brand-header {
            padding: 1.25rem 1.5rem;
            background-color: #090e1a;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        #sidebar .nav-link {
            color: var(--sidebar-text);
            padding: 0.75rem 1.25rem;
            border-radius: 0.5rem;
            margin: 0.15rem 0.75rem;
            font-size: 0.88rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
        }

        #sidebar .nav-link i {
            font-size: 1.15rem;
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }

        #sidebar .nav-link:hover {
            color: #f1f5f9;
            background-color: var(--sidebar-hover);
        }

        #sidebar .nav-link.active {
            color: var(--sidebar-text-active);
            background-color: var(--sidebar-active);
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .nav-category {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            padding: 1rem 1.5rem 0.35rem;
            font-weight: 700;
        }

        /* Main Content Layout */
        #main-wrapper {
            flex-grow: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Top Navbar */
        .top-navbar {
            background-color: #ffffff;
            border-bottom: 1px solid var(--card-border);
            padding: 0.75rem 1.5rem;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.03);
        }

        /* Custom Cards */
        .card-custom {
            background-color: #ffffff;
            border: 1px solid var(--card-border);
            border-radius: 0.75rem;
            box-shadow: var(--card-shadow);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .metric-card {
            border-left: 4px solid var(--accent-color);
        }

        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.08);
        }

        /* Table Aesthetics */
        .table-custom {
            vertical-align: middle;
        }

        .table-custom thead th {
            background-color: #f1f5f9;
            color: #475569;
            font-size: 0.78rem;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.03em;
            border-bottom: 1px solid #cbd5e1;
            padding: 0.85rem 1rem;
        }

        .table-custom tbody td {
            padding: 0.85rem 1rem;
            color: #1e293b;
            font-size: 0.875rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-custom tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Badges */
        .badge {
            font-weight: 500;
            padding: 0.4em 0.7em;
            border-radius: 0.375rem;
        }

        /* Floating Flash Alerts */
        .alert-dismissible {
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05);
        }

        @media (max-width: 991.98px) {
            #sidebar {
                position: fixed;
                left: -260px;
                top: 0;
                bottom: 0;
            }
            #sidebar.show {
                left: 0;
            }
            .sidebar-backdrop {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1030;
            }
            .sidebar-backdrop.show {
                display: block;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar Backdrop for Mobile -->
        <div id="sidebar-backdrop" class="sidebar-backdrop" onclick="toggleSidebar()"></div>

        <!-- Sidebar Navigation -->
        <nav id="sidebar" class="d-flex flex-column flex-shrink-0">
            <div class="brand-header d-flex align-items-center justify-content-between">
                <a href="{{ route('dashboard') }}" class="d-flex align-items-center text-white text-decoration-none">
                    <span class="d-inline-flex align-items-center justify-content-center bg-primary rounded-3 text-white me-2" style="width: 36px; height: 36px;">
                        <i class="bi bi-handbag-fill fs-5"></i>
                    </span>
                    <div>
                        <div class="fw-bold fs-6 lh-1">BagSmart</div>
                        <span class="text-secondary small" style="font-size: 0.72rem;">Wholesale & Inventory</span>
                    </div>
                </a>
                <button class="btn btn-sm text-white-50 d-lg-none" onclick="toggleSidebar()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="py-2 flex-grow-1 overflow-y-auto">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard</span>
                </a>

                <div class="nav-category">Inventory & Products</div>

                <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <i class="bi bi-boxes"></i>
                    <span>All Products</span>
                </a>

                <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <i class="bi bi-tags-fill"></i>
                    <span>Categories</span>
                </a>

                <a href="{{ route('inventory.index') }}" class="nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-steps"></i>
                    <span>Stock & Movements</span>
                </a>

                <div class="nav-category">Sales & Relations</div>

                <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i>
                    <span>All Customers</span>
                </a>

                <a href="{{ route('outstanding.index') }}" class="nav-link {{ request()->routeIs('outstanding.*') ? 'active' : '' }}">
                    <i class="bi bi-exclamation-octagon-fill"></i>
                    <span>Outstanding</span>
                </a>

                <div class="nav-category">Order Processing</div>

                <a href="{{ route('orders.index') }}" class="nav-link {{ request()->routeIs('orders.index') || request()->routeIs('orders.show') ? 'active' : '' }}">
                    <i class="bi bi-receipt-cutoff"></i>
                    <span>All Orders</span>
                </a>

                <a href="{{ route('orders.create') }}" class="nav-link {{ request()->routeIs('orders.create') ? 'active' : '' }}">
                    <i class="bi bi-plus-circle-fill"></i>
                    <span>New Order</span>
                </a>

                <a href="{{ route('payments.index') }}" class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                    <i class="bi bi-credit-card-fill"></i>
                    <span>Payments</span>
                </a>

                <div class="nav-category">Intelligence & Config</div>

                <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-bar-graph-fill"></i>
                    <span>Reports</span>
                </a>

                <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <i class="bi bi-gear-fill"></i>
                    <span>Settings</span>
                </a>
            </div>

            <div class="p-3 border-top border-secondary border-opacity-25" style="background-color: #090e1a;">
                <div class="d-flex align-items-center">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-2" style="width: 34px; height: 34px; font-size: 0.85rem;">
                        {{ substr(Auth::user()->name ?? 'Admin', 0, 1) }}
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="text-white text-truncate fw-medium" style="font-size: 0.85rem;">{{ Auth::user()->name ?? 'Administrator' }}</div>
                        <div class="text-secondary text-truncate" style="font-size: 0.72rem;">{{ Auth::user()->email ?? 'admin@wholesale.com' }}</div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Wrapper -->
        <div id="main-wrapper">
            <!-- Top Navbar -->
            <header class="top-navbar d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-outline-secondary btn-sm d-lg-none" type="button" onclick="toggleSidebar()">
                        <i class="bi bi-list fs-5"></i>
                    </button>
                    <h5 class="mb-0 fw-semibold text-slate-800">@yield('page_title', 'Dashboard')</h5>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('orders.create') }}" class="btn btn-primary btn-sm d-none d-sm-inline-flex align-items-center gap-1">
                        <i class="bi bi-plus-lg"></i>
                        <span>New Wholesale Order</span>
                    </a>

                    <div class="dropdown">
                        <button class="btn btn-light btn-sm dropdown-toggle d-flex align-items-center gap-2 border" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle text-primary"></i>
                            <span class="d-none d-md-inline">{{ Auth::user()->name ?? 'Admin' }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><span class="dropdown-header">Role: Administrator</span></li>
                            <li><a class="dropdown-item" href="{{ route('settings.index') }}"><i class="bi bi-gear me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i>Log Out
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Page Content Body -->
            <main class="flex-grow-1 p-3 p-md-4">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                        <div>{{ session('success') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <div>{{ session('error') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                        <i class="bi bi-info-circle-fill fs-5"></i>
                        <div>{{ session('info') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <div class="fw-semibold mb-1"><i class="bi bi-exclamation-octagon-fill me-1"></i> Please check the following errors:</div>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </main>

            <footer class="text-center py-3 text-muted small border-top bg-white">
                BagSmart Wholesale & Inventory Management &copy; {{ date('Y') }}. Enterprise Demo System.
            </footer>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            sidebar.classList.toggle('show');
            backdrop.classList.toggle('show');
        }

        // Global destructive action confirmation helper
        function confirmAction(message, formId) {
            if (confirm(message || 'Are you sure you want to proceed?')) {
                document.getElementById(formId).submit();
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
