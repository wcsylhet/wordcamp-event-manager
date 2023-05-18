<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charSet="utf-8"/>
    <title>Organizer's Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script type="text/javascript">
        window.WordCampEntryAdmin = <?php echo json_encode($adminVars); ?>;
    </script>
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
</head>
<body>
<div id="app">
    <div id="wordcamp_entry_pass_app">Loading</div>
</div>
<script type='text/javascript' src='<?php echo site_url('/wp-includes/js/jquery/jquery.min.js?ver=3.6.3');?>' id='jquery-core-js'></script>
<script type='text/javascript' src='<?php echo WP_SYL_ENTRY_PASS_PLUGIN_URL . 'dist/app.js'; ?>'></script>

<p style="text-align: center; margin-top: 200px;">
    <a href="<?php echo wp_logout_url(); ?>">Logout</a>
</p>
</body>
</html>
