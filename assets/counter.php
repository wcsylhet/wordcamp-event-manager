<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charSet="utf-8"/>
    <title>Find Your Registration Counter - WordCamp Sylhet</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        * {
            font-family: "Inter var", -apple-system, BlinkMacSystemFont, "Helvetica Neue", Helvetica, sans-serif;
        }
        /* Box sizing rules */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        /* Set core root defaults */
        html:focus-within {
            scroll-behavior: smooth;
        }

        /* Set core body defaults */
        body {
            min-height: 100vh;
            text-rendering: optimizeSpeed;
            line-height: 1.5;
            background: #f8fafc;
        }

        /* A elements that don't have a class get default styles */
        a:not([class]) {
            text-decoration-skip-ink: auto;
        }

        /* Make images easier to work with */
        img, picture {
            max-width: 100%;
            display: block;
        }

        /* Inherit fonts for inputs and buttons */
        input,
        button,
        textarea,
        select {
            font: inherit;
        }

        /* Remove all animations, transitions and smooth scroll for people that prefer not to see them */
        @media (prefers-reduced-motion: reduce) {
            html:focus-within {
                scroll-behavior: auto;
            }

            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }
    </style>

    <style>
        #wordcamp_counter {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
        }
        .header img {
            max-width: 200px;
            text-align: center;
            display: inline-block;
        }
        .header h1 {
            font-size: 25px;
            margin: 0;
        }
        .form_field label {
            display: block;
            font-weight: bold;
            font-size: 20px;
            margin-bottom: 10px;
        }

        .form_field {
            margin-bottom: 20px;
        }

        .form_field input {
            padding: 10px 15px;
            border-radius: 5px;
            border: 2px solid gray;
            width: 100%;
        }
        .form {
            padding: 20px 0;
        }
        button#submit {
            padding: 10px 15px;
            border: 1px solid #84c918;
            background: #84c918;
            color: white;
            font-weight: 500;
            border-radius: 5px;
            width: 100%;
            cursor: pointer;
            transition: all 0.1s ease;
        }

        button#submit:hover {
            background: #5a8f08;
        }
        .result {
            text-align: center;
        }
        .result .form_field {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            align-content: center;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 50px;
        }
        .booth_card {
            display: inline-block;
            padding: 35px 50px;
            background: #673AB7;
            color: white;
            font-size: 30px;
            border-radius: 3px;
        }
        .qr_code img {
            width: 216px;
            height: 216px;
        }
        p.error {
            background: red;
            color: white;
            padding: 10px 15px;
        }
    </style>
</head>
<body>
<div id="app">
    <div id="wordcamp_counter">
        <div class="header">
            <img src="https://i0.wp.com/sylhet.wordcamp.org/2024/files/2024/02/wc-sylhet-logo-white@2x.png" />
            <h1>Your Registration Counter - WordCamp Sylhet 2024</h1>
        </div>

        <div id="result" class="result">
            <?php if($attendee): ?>
            <div class="result_item">
                <div class="form_field">
                    <div class="booth_card">
                        <span>Counter: <br /><?php echo $attendee->counter; ?></span>
                    </div>
                    <div class="qr_code">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo $attendee->secret_key; ?>" />
                    </div>
                </div>
            </div>
            <?php else: ?>
            <h3>Please meet at WordCamp Sylhet Service Desk at the venue</h3>
            <?php endif; ?>
        </div>
    </div>
</div>


</body>
</html>
