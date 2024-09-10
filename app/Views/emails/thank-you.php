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
                <p class="title">Hi <?=($fullname) ? $fullname : session()->get('user_fullname');?>. Thank you for requesting a quote.</p>
                <div class="email-content">
                    <h3>Your quote number is <i><?=$reference;?></i></h3>
                    <p>You should receive a quote within 2-3 working days.</p>
                    <p>Please contact us with any questions. Thank you!</p>
                </div>
                <div class="signature">
                    <h4>Charlie Barfield</h4>
                    <span>(662) 910-9173</span>
                    <span>charlie@lab-ready.net</span>
                    <span>Lab-ready.net</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
