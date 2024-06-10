<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title><?=$title;?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta content="Admin template that can be used to build dashboards for CRM, CMS, etc." name="description" />
    <meta content="Rustom Codilan" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="icon" href="<?=base_url();?>images/favicon.jpeg" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Nanum+Gothic&family=Quicksand:wght@300..700&family=Roboto+Mono:ital,wght@0,100..700;1,100..700&family=Ruda:wght@400..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="<?=base_url();?>assets/css/vendors.css" />
    <link href="<?=base_url();?>assets/css/style.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.8/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" integrity="sha512-yVvxUQV0QESBt1SyZbNJMAwyKvFTLMyXSyBHDO4BG5t7k/Lw34tyqlSDlKIrIENIzCl+RVUNjmCPG+V/GMesRw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://unpkg.com/dropzone@6.0.0-beta.1/dist/dropzone.css" rel="stylesheet" type="text/css" />
    <style>
        .badge {
            position: absolute;
            right: 0;
            top: 0;
            border-top: 90px solid green;
            border-left: 90px solid transparent;
        }
        .mask-t {
            color: #fff;
            position: absolute;
            width: 100px;
            height: 100px;
            right: 0px;
            top: 0px;
        }
        .mask-t strong {
            display: block;
            width:100%;
            height:100%;
            line-height: 100px;
            text-align: center;
            -webkit-transform: rotate(45deg) translate(0, -25%);
            -moz-transform: rotate(45deg) translate(0, -25%);
            -ms-transform: rotate(45deg) translate(0, -25%);
            -o-transform: rotate(45deg) translate(0, -25%);
            transform: rotate(45deg) translate(0, -25%);
        }
        .delete-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .card-statistics:hover .delete-btn {
            opacity: 1;
        }
        .upload-area {
            border: 2px dashed #ccc;
            border-radius: 5px;
            width: 100%;
            text-align: center;
            padding: 20px;
        }

        .upload-area h2 {
            margin: 0;
        }

        .upload-area p {
            margin: 10px 0;
        }

        .upload-area button {
            padding: 10px 20px;
            border: none;
            background-color: #007BFF;
            color: white;
            cursor: pointer;
            border-radius: 5px;
        }

        .upload-area button:hover {
            background-color: #0056b3;
        }

        #fileList {
            margin-top: 20px;
        }

        .file-item {
            margin-bottom: 10px;
        }
        canvas {
            display: block; 
        }
    </style>
</head>

<body class="dark-sidebar">
    <div class="app">
        <div class="app-wrap">
            <div class="loader">
                <div class="h-100 d-flex justify-content-center">
                    <div class="align-self-center">
                        <img src="<?=base_url();?>assets/img/loader/loader.gif" alt="loader">
                    </div>
                </div>
            </div>
            <header class="app-header top-bar">
                <nav class="navbar navbar-expand-md">
                    <div class="navbar-header dark-header d-flex align-items-center">
                        <a href="javascript:void:(0)" class="mobile-toggle"><i class="ti ti-align-right"></i></a>
                        <!--<a class="navbar-brand" href="index.html">
                            <img src="<?=base_url();?>assets/img/logo.png" class="img-fluid logo-desktop" alt="logo" />
                            <img src="<?=base_url();?>assets/img/logo.png" class="img-fluid logo-mobile" alt="logo" />
                        </a>-->
                    </div>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <i class="ti ti-align-left"></i>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <div class="navigation d-flex bg-dark">
                            <ul class="navbar-nav nav-left">
                                <li class="nav-item">
                                    <a href="javascript:void(0)" class="nav-link sidebar-toggle">
                                        <i class="ti ti-align-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            </header>