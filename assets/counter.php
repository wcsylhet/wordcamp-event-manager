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
            border-bottom: 1px solid #d1d1d1;
            padding-bottom: 20px;
        }
        .header img {
            width: 64px;
            height: 64px;
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
            flex-direction: row;
            align-content: flex-end;
            align-items: center;
            justify-content: center;
            column-gap: 20px;
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
            width: 112px;
            height: 112px;
            border: 7px solid #673AB7;
            border-radius: 3px;
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
            <img src="https://sylhet.wordcamp.org/2023/files/2023/03/wcsyl_23-1-150x150.png" />
            <h1>Find Your Registration Counter - WordCamp Sylhet</h1>
        </div>
        <div id="form" class="form">
            <div class="form_field">
                <label for="attendee_id">Your Attendee ID</label>
                <input value="<?php echo ($attendee) ? $attendee->attendee_uid : ''; ?>" type="number" required placeholder="ex: 1234" name="attendee_id" id="attendee_id" />
            </div>
            <div class="form_field">
                <button id="submit">Find Registration Booth</button>
            </div>
        </div>
        <div id="result" class="result">
            <?php if($attendee): ?>
            <div class="result_item">
                <div class="form_field">
                    <label for="attendee_id">Your Registration Booth</label>
                </div>
                <div class="form_field">
                    <div class="booth_card">
                        <span><?php echo $attendee->counter; ?></span>
                    </div>
                    <div class="qr_code">
                        <img src="https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=<?php echo $attendee->attendee_uid; ?>&&chld=L|1&choe=UTF-8" />
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script type='text/javascript' src='<?php echo site_url('/wp-includes/js/jquery/jquery.min.js?ver=3.6.3');?>' id='jquery-core-js'></script>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('#submit').on('click', function (e) {
            e.preventDefault();
            var attendee_id = $('#attendee_id').val();
            if(attendee_id == '') {
                alert('Please enter your Attendee ID');
                return false;
            }

            window.jQuery.ajax({
                url: "<?php echo admin_url('admin-ajax.php');  ?>",
                type: 'POST',
                data: {
                    action: 'syl_attendee_counter',
                    attendee_uid: attendee_id
                },
                cache: false
            })
                .then(function (response) {
                    $('#result').html(response.message);
                })
                .fail(function (error) {
                    if(error.responseJSON && error.responseJSON.data && error.responseJSON.data.message) {
                        $('#result').html(error.responseJSON.data.message);
                    } else {
                        $('#result').html(error.responseText);
                    }
                })
                .always(function () {
                    console.log('complete');
                });
        });
    });
</script>

</body>
</html>
