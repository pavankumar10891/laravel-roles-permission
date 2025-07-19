<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin App</title>
  <meta name="theme-name" content="mono" />
  <link href="https://fonts.googleapis.com/css?family=Karla:400,700|Roboto" rel="stylesheet">
  <link href="{{ asset('assets') }}/plugins/material/css/materialdesignicons.min.css" rel="stylesheet" />
  <link href="{{ asset('assets') }}/plugins/simplebar/simplebar.css" rel="stylesheet" />
  <link href="{{ asset('assets') }}/plugins/nprogress/nprogress.css" rel="stylesheet" />
  <link href="{{ asset('assets') }}/plugins/DataTables/DataTables-1.10.18/css/jquery.dataTables.min.css" rel="stylesheet" />
  <link href="{{ asset('assets') }}/plugins/jvectormap/jquery-jvectormap-2.0.3.css" rel="stylesheet" />
  <link href="{{ asset('assets') }}/plugins/daterangepicker/daterangepicker.css" rel="stylesheet" />
  <link href="{{ asset('assets') }}/https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
  <link href="{{ asset('assets') }}/plugins/toaster/toastr.min.css" rel="stylesheet" />
  <link id="main-css-href" rel="stylesheet" href="{{ asset('assets') }}/css/style.css" />
  <link href="{{ asset('assets') }}/images/favicon.png" rel="shortcut icon" />
  @yield('css')
  <script src="{{ asset('assets') }}/plugins/nprogress/nprogress.js"></script>
</head>
<body class="navbar-fixed sidebar-fixed" id="body">
<script>
    NProgress.configure({ showSpinner: false });
    NProgress.start();
</script>
<div id="toaster"></div>

<div class="wrapper">
    <aside class="left-sidebar sidebar-dark" id="left-sidebar">
        <div id="sidebar" class="sidebar sidebar-with-footer">
        <!-- Aplication Brand -->
        <div class="app-brand">
            <a href="{{ route('admin.dashboard') }}">
            <img src="{{ asset('assets') }}/images/logo.png" alt="Mono">
            <span class="brand-name">MONO</span>
            </a>
        </div>
        <!-- begin sidebar scrollbar -->
        <div class="sidebar-left" data-simplebar style="height: 100%;">
            <!-- sidebar menu -->
            <ul class="nav sidebar-inner" id="sidebar-menu">
                <li class="active">
                  <a class="sidenav-item-link" href="{{ route('admin.dashboard') }}">
                      <i class="mdi mdi-briefcase-account-outline"></i>
                      <span class="nav-text">Dashboard</span>
                  </a>
                </li>
            </ul>

        </div>

        <div class="sidebar-footer">
            <div class="sidebar-footer-content">
            <ul class="d-flex">
                <li>
                  <form method="POST" action="{{ auth('admin')->check() ? route('admin.logout') : route('logout') }}" id="logout-form">

                            @csrf
                    <a  href="javascript:void(0)" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"> <i class="mdi mdi-logout"></i></a> Log Out </a>
                  </form>
                </li>
                {{-- <li>
                  <a href="#" data-toggle="tooltip" title="No chat messages"><i class="mdi mdi-chat-processing"></i></a>
                </li> --}}
            </ul>
            </div>
        </div>
        </div>
    </aside>
    <div class="page-wrapper">
        <header class="main-header" id="header">
            <nav class="navbar navbar-expand-lg navbar-light" id="navbar">
              <!-- Sidebar toggle button -->
              <button id="sidebar-toggler" class="sidebar-toggle">
                <span class="sr-only">Toggle navigation</span>
              </button>

              <span class="page-title">dashboard</span>

              <div class="navbar-right ">

                <!-- search form -->
                <div class="search-form">
                  {{-- <form action="index.html" method="get">
                    <div class="input-group input-group-sm" id="input-group-search">
                      <input type="text" autocomplete="off" name="query" id="search-input" class="form-control" placeholder="Search..." />
                      <div class="input-group-append">
                        <button class="btn" type="button">/</button>
                      </div>
                    </div>
                  </form> --}}
                  {{-- <ul class="dropdown-menu dropdown-menu-search">

                    <li class="nav-item">
                      <a class="nav-link" href="index.html">Morbi leo risus</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="index.html">Dapibus ac facilisis in</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="index.html">Porta ac consectetur ac</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="index.html">Vestibulum at eros</a>
                    </li>

                  </ul> --}}

                </div>

                <ul class="nav navbar-nav">
                  <!-- User Account -->
                  <li class="dropdown user-menu">
                    <button class="dropdown-toggle nav-link" data-toggle="dropdown">
                      <img src="{{ asset('assets') }}/images/user/user-xs-01.jpg" class="user-image rounded-circle" alt="User Image" />
                      <span class="d-none d-lg-inline-block">{{ Auth::user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right">
                      <li>
                        <a class="dropdown-link-item" href="#">
                          <i class="mdi mdi-account-outline"></i>
                          <span class="nav-text">My Profile</span>
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-link-item" href="#">
                          <i class="mdi mdi-settings"></i>
                          <span class="nav-text">Account Setting</span>
                        </a>
                      </li>

                      <li class="dropdown-footer">
                        
                        <form method="POST" action="{{ auth('admin')->check() ? route('admin.logout') : route('logout') }}" id="logout-form">
                            @csrf
                          <a class="dropdown-link-item" href="javascript:void(0)" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"> <i class="mdi mdi-logout"></i> Log Out </a>
                        </form>
                      </li>
                    </ul>
                  </li>
                </ul>
              </div>
            </nav>
        </header>
        <div class="content-wrapper">
            @yield('content')
        </div> 
        <footer class="footer mt-auto">
            <div class="copyright bg-white">
              <p>
                &copy; <span id="copy-year"></span> Copyright.
              </p>
            </div>
            <script>
                var d = new Date();
                var year = d.getFullYear();
                document.getElementById("copy-year").innerHTML = year;
            </script>
        </footer>  
    </div>
</div>
<script src="{{ asset('assets') }}/plugins/jquery/jquery.min.js"></script>
        <script src="{{ asset('assets') }}/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="{{ asset('assets') }}/plugins/simplebar/simplebar.min.js"></script>
        <script src="https://unpkg.com/hotkeys-js/dist/hotkeys.min.js"></script>

        
        
        <script src="{{ asset('assets') }}/plugins/apexcharts/apexcharts.js"></script>
        
        
        
        <script src="{{ asset('assets') }}/plugins/DataTables/DataTables-1.10.18/js/jquery.dataTables.min.js"></script>
        
        
        
        <script src="{{ asset('assets') }}/plugins/jvectormap/jquery-jvectormap-2.0.3.min.js"></script>
        <script src="{{ asset('assets') }}/plugins/jvectormap/jquery-jvectormap-world-mill.js"></script>
        <script src="{{ asset('assets') }}/plugins/jvectormap/jquery-jvectormap-us-aea.js"></script>
        
        
        
        <script src="{{ asset('assets') }}/plugins/daterangepicker/moment.min.js"></script>
        <script src="{{ asset('assets') }}/plugins/daterangepicker/daterangepicker.js"></script>
        <script>
          jQuery(document).ready(function() {
            jQuery('input[name="dateRange"]').daterangepicker({
            autoUpdateInput: false,
            singleDatePicker: true,
            locale: {
              cancelLabel: 'Clear'
            }
          });
            jQuery('input[name="dateRange"]').on('apply.daterangepicker', function (ev, picker) {
              jQuery(this).val(picker.startDate.format('MM/DD/YYYY'));
            });
            jQuery('input[name="dateRange"]').on('cancel.daterangepicker', function (ev, picker) {
              jQuery(this).val('');
            });
          });
        </script>
        
        
        
        <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
        
        
        
        <script src="{{ asset('assets') }}/plugins/toaster/toastr.min.js"></script>

        
        
        <script src="{{ asset('assets') }}/js/mono.js"></script>
        <script src="{{ asset('assets') }}/js/chart.js"></script>
        <script src="{{ asset('assets') }}/js/map.js"></script>
        <script src="{{ asset('assets') }}/js/custom.js"></script>
        @yield('script')
</body>
</html>