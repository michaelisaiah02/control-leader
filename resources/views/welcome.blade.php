<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application</title>
    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <link rel="manifest" href="/site.webmanifest" />
    @vite(['resources/css/app.css', 'resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        #navbar-kalibrasi {
            border-bottom-left-radius: 180px;
            border-bottom-right-radius: 180px;
        }

        #title-section {
            height: 10rem
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light text-light mx-5 px-5 pb-3 bg-primary" id="navbar-kalibrasi">
        <div class="container-fluid justify-content-center">
            <a class="navbar-brand mx-0 mx-md-4" href="/">
                <img src="{{ asset('image/logo-pt.png') }}" alt="Logo" class="mt-0 logo">
            </a>
            <div class="row text-center justify-content-center" id="title-section">
                <p id="main-title" class="align-self-center main-title p-0 m-0">APPLICATION</p>
                <p class="align-self-center company-name p-0 m-0">PT. CATURINDO AGUNGJAYA RUBBER</p>
            </div>
            <a class="navbar-brand mx-0 mx-md-4" href="/">
                <img src="{{ asset('image/logo-rice.png') }}" alt="Logo" class="mt-0 logo">
            </a>
        </div>
    </nav>
    <div class="container" id="dashboard">
        <div class="row justify-content-md-around justify-content-center mb-3">
            <div class="col-md-5 main-menu">
                <div class="card border-0">
                    <a href="{{ route('login') }}?app=kalibrasi" class="mx-auto mt-1 mt-md-5 icon menu-btn">
                        <img src="{{ asset('icon/input.svg') }}" alt="KALIBRASI">
                    </a>
                    <div class="card-body mx-auto">
                        <a class="btn btn-primary py-2 px-5 rounded-4 menu-btn btn1"
                            href="{{ route('login') }}?app=kalibrasi">KALIBRASI</a>
                    </div>
                </div>
            </div>
            <div class="col-md-5 main-menu">
                <div class="card border-0">
                    <a href="{{ route('login') }}?app=control_leader" class="mx-auto mt-1 mt-md-5 icon menu-btn">
                        <img src="{{ asset('icon/report.svg') }}" alt="CONTROL LEADER">
                    </a>
                    <div class="card-body mx-auto">
                        <a class="btn btn-primary py-2 px-5 rounded-4 menu-btn btn1 text-nowrap"
                            href="{{ route('login') }}?app=control_leader">CONTROL
                            LEADER</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
