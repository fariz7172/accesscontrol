<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('img/soyal.png') }}">
    <title>Soyal - Edit Schedule</title>
    <link href="{{ asset('template') }}/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="{{ asset('template') }}/css/font.css" rel="stylesheet" type="text/css">
    <link href="{{ asset('template') }}/css/stackpath.css" rel="stylesheet" type="text/css">
    <link href="{{ asset('template') }}/css/checkbox.css" rel="stylesheet" type="text/css">
    <link href="{{ asset('template') }}/css/queri.css" rel="stylesheet" type="text/css">
    <link href="{{ asset('template') }}/css/buttonmargin.css" rel="stylesheet" type="text/css">
    <link href="{{ asset('template') }}/css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body id="page-top">
    <div id="wrapper">
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ url('/trainer') }}">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-laugh-wink"></i>
                </div>
                <div class="sidebar-brand-text mx-3">Soyal Trainer Dashboard</div>
            </a>
            <hr class="sidebar-divider my-0">
            <li class="nav-item {{ request()->is('trainer') ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('/trainer') }}">
                    <i class="fas fa-file"></i>
                    <span>My Leave Requests</span>
                </a>
            </li>
            <li class="nav-item {{ request()->is('trainer/availability*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('trainer.availability.index') }}">
                    <i class="fas fa-calendar"></i>
                    <span>My Availability</span>
                </a>
            </li>
            <li class="nav-item {{ request()->is('trainer/schedule*') ? 'active' : '' }}">
                <a class="nav-link" href="{{