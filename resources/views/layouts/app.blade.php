<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>@yield('title')</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta content="Home" name="description" />
        <meta content="" name="author" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <link rel="shortcut icon" href="{{asset('assets/images/favicon.ico')}}">
         <link href="{{asset('plugins/daterangepicker/daterangepicker.css')}}" rel="stylesheet" />
        <link href="{{asset('plugins/select2/select2.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('plugins/bootstrap-touchspin/css/jquery.bootstrap-touchspin.min.css')}}" rel="stylesheet" />
        <link href="{{asset('plugins/nestable/jquery.nestable.min.css')}}" rel="stylesheet" />
        <link href="{{asset('plugins/sweet-alert2/sweetalert2.min.css')}}" rel="stylesheet" type="text/css">
        <link href="{{asset('plugins/animate/animate.css')}}" rel="stylesheet" type="text/css">
        <link href="{{asset('plugins/dropify/css/dropify.min.css')}}" rel="stylesheet">
        <link href="{{asset('plugins/jvectormap/jquery-jvectormap-2.0.2.css')}}" rel="stylesheet">
        <link href="{{asset('plugins/jquery-steps/jquery.steps.css')}}">
        <link href="{{asset('plugins/sweet-alert2/sweetalert2.min.css')}}" rel="stylesheet" type="text/css">
        <link href="{{asset('plugins/datatables/dataTables.bootstrap4.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('plugins/datatables/buttons.bootstrap4.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('plugins/datatables/responsive.bootstrap4.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets/css/jquery-ui.min.css')}}" rel="stylesheet">
        <link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets/css/icons.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets/css/metisMenu.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets/css/app.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets/css/custom.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets/css/style.css')}}" rel="stylesheet" type="text/css" />
        <script src="{{asset('assets/js/jquery.min.js')}}"></script>
        <script src="{{asset('assets/js/jquery-ui.min.js')}}"></script>
        <link rel="stylesheet" type="text/css" href="{{asset('assets/css/pnotify.custom.css')}}">
        <script type="text/javascript" src="{{asset('assets/js/pnotify.custom.js')}}"></script>
        <link href="{{asset('assets/css/client_style.css')}}" rel="stylesheet" type="text/css" />
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
        <style>
            body {
            font-family: 'Poppins', sans-serif;
        }

          #overlay {
            display: none; 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            background-color: rgba(0, 0, 0, 0.5); 
            z-index: 999;
        }
        
            .menu.notification {
                position: relative;
            }
            .loader-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.9); /* Slightly transparent background */
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999; /* Ensure it appears on top of everything */
    flex-direction: column; /* Stack loader and text */
    font-family: Arial, sans-serif;
}

.loader {
    border: 8px solid #f3f3f3; /* Light grey */
    border-radius: 50%;
    border-top: 8px solid #3498db; /* Blue */
    width: 60px;
    height: 60px;
    animation: spin 1s linear infinite; /* Spinning animation */
    margin-bottom: 15px; /* Space between loader and text */
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

.loader-text {
    font-size: 18px;
    color: #3498db; /* Matches loader color */
    font-weight: bold;
}

        
        </style>

    </head>
    <body data-layout="horizontal" style="background-image: url('{{ asset('assets/images/mainbg.png') }}">
        <div id="overlay"></div>
        <div class="topbar">
            <div id="snow"></div>
            @if (Auth::guard('web')->check())
                <div class="topbar-inner">

                    <div class="navbar-custom-menu " style="margin-left: .5rem !important">
                        <div id="navigation">
                            {{-- <div class="text-center text-lg-left"> --}}
                            <a href="{{route('home')}}" class="logo">
                                <span><img src="{{asset('assets/images/logo.png')}}" alt="logo-small" class="logo-sm" style="height: 2.5rem;"></span>
                            </a>

                            <ul class="navigation-menu ml-4">
                                <li class="{{ (Request::is('/') || Request::is('home')) ? 'submenuactive' : '' }}">
                                    <a href="{{ route('home') }}">
                                        <i id="dashboard-icon" class="dripicons-meter {{ (Request::is('/') || Request::is('home')) ? 'submenuactivei' : '' }}"></i>
                                        Dashboard
                                    </a>
                                </li>
                                <li class="{{ (Request::is('orders_status') || Request::is('orders_status/*')) ? 'submenuactive' : '' }}">
                                    <a href="{{ route('orders_status') }}">
                                        <i id="single_order-icon" class="dripicons-list {{ (Request::is('orders_status') || Request::is('orders_status/*')) ? 'submenuactivei' : '' }}"></i>
                                        Orders
                                    </a>
                                </li>
                                @if(Auth::user()->hasRole(['Super Admin', 'VP', 'Business Head', 'PM/TL','SPOC', 'Admin','AVP' ]))
                                <li class="{{ (Request::is('single_order') || Request::is('single_order/*')) ? 'submenuactive' : '' }}">
                                    <a href="{{ route('single_order') }}">
                                        <i id="single_order-icon" class="dripicons-document {{ (Request::is('single_order') || Request::is('single_order/*')) ? 'submenuactivei' : '' }}"></i>
                                        Order Creation
                                    </a>
                                </li>
                                <li class="{{ (Request::is('Reports') || Request::is('Reports/*')) ? 'submenuactive' : '' }}">
                                    <a href="{{ route('Reports') }}">
                                        <i id="Reports-icon" class="dripicons-document {{ (Request::is('Reports') || Request::is('Reports/*')) ? 'submenuactivei' : '' }}"></i>Reports
                                    </a>
                                </li>
                                <li class="{{ (Request::is('settings') || Request::is('settings/*')) ? 'submenuactive' : '' }}">
                                    <a href="{{ route('settings') }}">
                                        <i id="settings-icon" class="dripicons-gear {{ (Request::is('settings') || Request::is('settings/*')) ? 'submenuactivei' : '' }}"></i>Settings
                                    </a>
                                </li>
                                 @endif

                            </ul>
                        </div>
                    </div>
                    <nav class="navbar-custom float-right">
                        <ul class="list-unstyled topbar-nav mb-0">
                            <li class="dropdown">
                                <a class="nav-link dropdown-toggle waves-effect waves-light nav-user" data-toggle="dropdown" href="#" role="button"
                                    aria-haspopup="false" aria-expanded="false">
                                    <img src="{{asset('assets/images/users/user-1.png')}}" alt="profile-user" class="rounded-circle" />
                                    <span class="ml-1 nav-user-name hidden-sm">
                                        @if(Auth::guard('web')->check())
                                             {{Auth::guard('web')->user()->username}}
                                        @endif
                                    <i class="mdi mdi-chevron-down"></i></span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item"  href="{{ route('profile_Edit') }}">
                                        <i class=" far fa-address-card text-muted mr-2"></i> Profile
                                    </a>
                                    @if(Auth::guard('web')->check())
                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                            @csrf
                                        </form>
                                        <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            <i class="ti-power-off text-muted mr-2"></i> Logout
                                        </a>
                                    @endif
                                </div>
                            </li>
                            <li class="menu-item">
                                <a class="navbar-toggle nav-link" id="mobileToggle">
                                    <div class="lines">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            @endif
        </div>
        <!-- Top Bar End -->
        <div class="page-wrapper" >
            <div class="page-content">
                @yield('content')
            </div>
        </div>
        <footer class="footer text-center text-sm-left">
            <div class="boxed-footer text-center">
                <a href="https://www.stellarapps.net/" target="_blank">&copy; {!! date("Y") !!} Stellar Innovations</a>
            </div>
        </footer>
        <style>
            .footer {
            border-top: 1px solid #e8ebf3;
            bottom: auto !important;   /* bottom: 0; */
            padding: 20px;
            position: absolute;
            right: 0;
            left: 0;
            color: #7081b9;
        }
        </style>
        {{-- <script src="{{asset('plugins/bootstrap-5.3.1/js/bootstrap.bundle.min.js')}}"></script> --}}
        <script src="{{asset('assets/js/bootstrap.bundle.min.js')}}"></script>
        <script src="{{asset('assets/js/metismenu.min.js')}}"></script>
        <script src="{{asset('assets/js/waves.js')}}"></script>
        <script src="{{asset('assets/js/feather.min.js')}}"></script>
        <script src="{{asset('assets/js/jquery.slimscroll.min.js')}}"></script>
        <script src="{{asset('plugins/jvectormap/jquery-jvectormap-2.0.2.min.js')}}"></script>
        <script src="{{asset('plugins/jvectormap/jquery-jvectormap-us-aea-en.js')}}"></script>
        <script src="{{asset('plugins/parsleyjs/parsley.min.js')}}"></script>
        <script src="{{asset('assets/pages/jquery.validation.init.js')}}"></script>
        <!-- Required datatable js -->
        <script src="{{asset('plugins/datatables/jquery.dataTables.min.js')}}"></script>
        <script src="{{asset('plugins/datatables/dataTables.bootstrap4.min.js')}}"></script>
        <!-- Buttons examples -->
        <script src="{{asset('plugins/datatables/dataTables.buttons.min.js')}}"></script>
        <script src="{{asset('plugins/datatables/buttons.bootstrap4.min.js')}}"></script>
        <script src="{{asset('plugins/datatables/jszip.min.js')}}"></script>
        <script src="{{asset('plugins/datatables/pdfmake.min.js')}}"></script>
        <script src="{{asset('plugins/datatables/vfs_fonts.js')}}"></script>
        <script src="{{asset('plugins/datatables/buttons.html5.min.js')}}"></script>
        <script src="{{asset('plugins/datatables/buttons.print.min.js')}}"></script>
        <script src="{{asset('plugins/datatables/buttons.colVis.min.js')}}"></script>
        <!-- Responsive examples -->
        <script src="{{asset('plugins/datatables/dataTables.responsive.min.js')}}"></script>
        <script src="{{asset('plugins/datatables/responsive.bootstrap4.min.js')}}"></script>
        <script src="{{asset('assets/pages/jquery.datatable.init.js')}}"></script>
        <script src="{{asset('plugins/dropify/js/dropify.min.js')}}"></script>
        <script src="{{asset('assets/pages/jquery.form-upload.init.js')}}"></script>
        <script src="{{asset('assets/pages/jquery.animate.init.js')}}"></script>
        <!-- App js -->
        <script src="{{asset('plugins/sweet-alert2/sweetalert2.min.js')}}"></script>
        <script src="{{asset('assets/pages/jquery.sweet-alert.init.js')}}"></script>
        <script src="{{asset('plugins/jquery-steps/jquery.steps.min.js')}}"></script>
        <script src="{{asset('assets/pages/jquery.form-wizard.init.js')}}"></script>
        <script src="{{asset('plugins/repeater/jquery.repeater.min.js')}}"></script>
        <script src="{{asset('assets/pages/jquery.form-repeater.js')}}"></script>
        <script src="{{asset('assets/js/jquery.core.js')}}"></script>
        <script src="{{asset('plugins/moment/moment.js')}}"></script>
        <script src="{{asset('plugins/daterangepicker/daterangepicker.js')}}"></script>
        <script src="{{asset('plugins/select2/select2.min.js')}}"></script>
        <script src="{{asset('plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js')}}"></script>
        <script src="{{asset('plugins/timepicker/bootstrap-material-datetimepicker.js')}}"></script>
        <script src="{{asset('plugins/bootstrap-maxlength/bootstrap-maxlength.min.js')}}"></script>
        <script src="{{asset('plugins/bootstrap-touchspin/js/jquery.bootstrap-touchspin.min.js')}}"></script>
        <script src="{{asset('assets/pages/jquery.forms-advanced.js')}}"></script>
        <script src="{{asset('assets/js/app.js')}}"></script>
        <script src="{{asset('assets/js/parsley.min.js')}}"></script>
        @stack('script-bottom')
    </body>

    <div id="logout-popup" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); padding:30px; background-color:#ffffff; border-radius:12px; box-shadow:0 6px 30px rgba(0, 0, 0, 0.3); z-index:1000; font-family: 'Arial', sans-serif; width: 400px;">
        <div style="text-align: center;">
            <h3 style="margin: 0; color: #f70707;font-weight:bold;">You have been Inactive!</h3>
            <p style="color: #151515; margin: 15px 0; font-weight:bold; font-size:14px;">You will be logged out in <span id="countdown" style="font-weight: bold; color: #e63946; font-size:20px;"></span> seconds.</p>
        </div>
        <div style="margin-top: 15px; text-align: center;">
            <button onclick="resetTimer()" style="padding: 12px 20px; background-color: #007bff; color: white; border: none; border-radius: 6px; cursor: pointer; transition: background-color 0.3s; font-size: 16px; font-weight: bold;">
                Stay Logged In
            </button>
        </div>
    </div>
    
    
    


    <script>
        (function (e) {
            var el = document.createElement('script');
            el.setAttribute('data-account', 'sukLXB6xXR');
            el.setAttribute('src', 'https://cdn.userway.org/widget.js');
            document.body.appendChild(el);
        })();


        let logoutTimer;
        let countdownTimer;
        let countdown = 60;
        let isPopupVisible = false; // Track popup visibility
    // Countdown timer starts at 500 seconds


    function addEventListeners() {
        window.onmousemove = resetTimer;
        window.onkeypress = resetTimer;
        window.onscroll = resetTimer;
        window.onclick = resetTimer;
    }

    function removeEventListeners() {
        window.onmousemove = null;
        window.onkeypress = null;
        window.onscroll = null;
        window.onclick = null;
    }

        function resetTimer() {

            clearTimeout(logoutTimer); // Clear previous logout timer
            clearInterval(countdownTimer); // Clear previous countdown interval
            countdown = 60; // Reset countdown timer
            document.getElementById("logout-popup").style.display = "none";
            document.getElementById("overlay").style.display = "none"; // Hide overlay

            // Start a new timer for inactivity (60 seconds)
            logoutTimer = setTimeout(() => {
                startLogoutCountdown();
            }, 600000); // 60000 ms = 1 minute
        }

        

        // Show popup and start countdown
        function startLogoutCountdown() {
            isPopupVisible = true; // Popup is visible
            removeEventListeners();

            document.getElementById("logout-popup").style.display = "block";
            document.getElementById("overlay").style.display = "block"; // Show overlay
             // Show popup

            countdownTimer = setInterval(() => {
                countdown--; // Decrease the countdown

                document.getElementById("countdown").textContent = countdown; // Update countdown display

                
                if (countdown === 0) {
                    clearInterval(countdownTimer); // Stop the countdown interval
                    logoutUser(); // Log out user
                }
            }, 1000); // 1000 ms = 1 second
        }

        // Trigger logout by making an AJAX call
        function logoutUser() {
            // Send a logout request to the server
            fetch("{{ route('logout') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }).then(response => {
                if (response.ok) {
                    window.location.href = "{{ route('login') }}"; // Redirect to login page after logout
                }
            });
        }

        window.onload = function() {
        resetTimer();
        addEventListeners();
    };


    </script>
</html>

