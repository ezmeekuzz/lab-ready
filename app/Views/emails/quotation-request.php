<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Crete+Round:ital@0;1&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
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
                <div class="con-text email-content">
                    <h3>Dear Client,</h3>
                    <p>Thank you for requesting a quote from www.lab-ready.com. We are glad to inform you that your quotes is now ready and can be viewed by logging into your dashboard. These are your login details:</p>
                </div>
                <div class="con-text initail">
                    <h4>Sincerely,</h4>
                    <h3>Charlie</h3>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
