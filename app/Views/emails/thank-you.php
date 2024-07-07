<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LAB Ready - Email Template</title>
    <style>
        /* General styles */
        body {
            background-color: #E4E4E4;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        table {
            border-spacing: 0;
            width: 100%;
        }

        td {
            padding: 0;
        }

        img {
            border: 0;
        }

        /* Container styles */
        .main-section {
            width: 100%;
            background-color: #E4E4E4;
            padding: 20px;
            box-sizing: border-box;
        }

        .inner-content {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            box-sizing: border-box;
        }

        /* Header styles */
        .header {
            background-color: #000;
            color: #fff;
            text-align: center;
            padding: 20px;
            font-family: 'Crete Round', serif;
        }

        .header h1 {
            margin: 0;
            font-size: 40px;
            font-weight: 700;
        }

        /* Content styles */
        .content {
            padding: 20px;
        }

        .content h3.title {
            text-align: center;
            font-size: 24px;
            font-family: 'Crete Round', serif;
            font-weight: 600;
            margin: 0 0 20px 0;
        }

        .content h3,
        .content h4,
        .content p {
            margin: 0 0 10px 0;
        }

        .content p {
            font-size: 16px;
            line-height: 1.5;
        }

        .signature {
            margin-top: 20px;
        }

        .signature h4 {
            font-size: 18px;
            font-weight: 400;
        }

        .signature h3 {
            font-size: 20px;
            font-weight: 600;
        }

        /* Media queries */
        @media screen and (max-width: 600px) {
            .header h1 {
                font-size: 32px;
                padding: 10px;
            }

            .content h3.title {
                font-size: 20px;
            }

            .content h3 {
                font-size: 18px;
            }

            .content h4 {
                font-size: 16px;
            }

            .content p {
                font-size: 14px;
            }

            .signature h4,
            .signature h3 {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="main-section">
        <div class="inner-content">
            <div class="header">
                <h1>Lab-Ready</h1>
            </div>
            <div class="content">
                <h3 class="title">Thank You For Your Quotation Request</h3>
                <div class="email-content">
                    <h3>Here Are Your Order Details:</h3>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                </div>
                <div class="signature">
                    <h4>Sincerely,</h4>
                    <h3>Charlie</h3>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
