<!DOCTYPE html>
<html lang="en">
<head>
  <title>@yield('title')</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('logo.png') }}">

  <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css"> -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.1/css/font-awesome.min.css"> -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">

  <style>
    .navbar {
      padding-bottom: 0rem;
      padding-top: 0rem;
    }

    .logo-img {
      max-height: 75px;
      max-width: 75px;
    }

    .bg-blue {
      background-color: rgba(8, 73, 153, 1.0);
    }

  </style>

  @yield('css')
</head>
<body style="background: rgb(255, 255, 255);">

<nav class="navbar navbar-expand-lg bg-blue navbar-dark sticky-top">
  <div class="container">
    <a class="navbar-brand" href="{{ url('/') }}">
      <img class="logo-img" src="{{ asset('logo/logo-up-putih.png') }}">
    </a>
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link @if (Request::is('well-trajectory/build-hold*') && !Request::is('well-trajectory/build-hold-drop*')) active @endif" href="{{ route('build.hold') }}"><i class="fa fa-wrench"></i> Build Hold &nbsp;</a>
      </li>
      <li class="nav-item">
        <a class="nav-link @if (Request::is('well-trajectory/build-hold-drop*')) active @endif" href="{{ route('build.hold.drop') }}"><i class="fas fa-chart-area"></i> Build Hold Drop &nbsp;</a>
      </li>
      <li class="nav-item">
        <a class="nav-link @if (Request::is('well-trajectory/horizontal-well*')) active @endif" href="{{ route('horizontal.well') }}"><i class="fa fa-cogs"></i> Horizontal Well</a>
      </li>
    </ul>
  </div>
</nav>

@yield('container')

<!-- CSS only -->
<!--<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">-->
<!-- JavaScript Bundle with Popper -->

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
<script src='https://cdn.plot.ly/plotly-2.16.1.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.17/d3.min.js'></script>

@yield('js')
</body>
</html>


