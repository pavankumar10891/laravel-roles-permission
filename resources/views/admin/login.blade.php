<!DOCTYPE html>
<html lang="en">
<head>
  <head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <title> Login </title>
  <link href="https://fonts.googleapis.com/css?family=Karla:400,700|Roboto" rel="stylesheet">
  <link href="{{ asset('assets') }}/plugins/material/css/materialdesignicons.min.css" rel="stylesheet" />
  <link href="{{ asset('assets') }}/plugins/simplebar/simplebar.css" rel="stylesheet" />
  <link href="{{ asset('assets') }}/plugins/nprogress/nprogress.css" rel="stylesheet" />  
  <link id="main-css-href" rel="stylesheet" href="{{ asset('assets') }}/css/style.css" />
  <link href="images/favicon.png" rel="shortcut icon" />
  <script src="{{ asset('assets') }}/plugins/nprogress/nprogress.js"></script>
</head>

</head>
  <body class="bg-light-gray" id="body">
    <div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh">
        <div class="d-flex flex-column justify-content-between">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-10">
            <div class="card card-default mb-0">
                <div class="card-header pb-0">
                <div class="app-brand w-100 d-flex justify-content-center border-bottom-0">
                    <a class="w-auto pl-0" href="/index.html">
                    <img src="{{ asset('assets') }}/images/logo.png" alt="Mono">
                    <span class="brand-name text-dark">MONO</span>
                    </a>
                </div>
                </div>
                <div class="card-body px-5 pb-5 pt-0">

                <h4 class="text-dark mb-6 text-center">Sign in for free</h4>

                <form action="{{ route('admin.login.submit') }}" method="POST">
                    @csrf
                    <div class="row">
                    <div class="form-group col-md-12 mb-4">
                        <input type="email" class="form-control input-lg" name="email" id="email" aria-describedby="emailHelp"
                        placeholder="email">
                    </div>
                    <div class="form-group col-md-12 ">
                        <input type="password" class="form-control input-lg"  name="password" placeholder="Password">
                    </div>
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between mb-3">
                        <div class="custom-control custom-checkbox mr-3 mb-3">
                            <input type="checkbox" class="custom-control-input" id="customCheck2">
                            <label class="custom-control-label" for="customCheck2">Remember me</label>
                        </div>
                            {{-- <a class="text-color" href="#"> Forgot password? </a> --}}
                        </div>

                        <button type="submit" class="btn btn-primary btn-pill mb-4">Sign In</button>
                    </div>
                    </div>
                </form>
                </div>
            </div>
            </div>
        </div>
        </div>
    </div>
</body>
</html>
