<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?=$title;?></title>
        <link rel="icon" href="/images/favicon.jpeg" type="image/x-icon">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@100;300;400;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="/css/style.css">
    </head>
    <body>
    <nav class="navbar navbar-expand-lg p-3">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="/home">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/contact-us">Contact Us</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/materials-and-surface-finishes">Materials And Surface Finishes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/about-us">About Us</a>
                        </li>
                    </ul>
                </div>
                <div class="d-flex ms-auto">
                    <?php if(session()->has('user_user_id')) : ?>
                    <a href="/user/logout" class="btn btn-outline-light btn-lg me-2">Logout</a>
                    <a href="/request-quotation" class="btn btn-light btn-lg">Quote Portal</a>
                    <?php else : ?>
                    <a href="/user/login" class="btn btn-outline-light btn-lg me-2">Login</a>
                    <a href="/register" class="btn btn-light btn-lg">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>