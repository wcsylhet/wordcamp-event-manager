<?php
/*
 * var $attendees array;
 * set browser window size to 1134 * 1701 on responsive mode
 */
$sides = [
    'top'
];

function sylFormatName($name)
{
    $name = trim($name);
    // remove multiple spaces
    $name = preg_replace('/\s+/', ' ', $name);
    // make the name as Title case
    $name = ucwords(strtolower($name));
    return $name;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charSet="utf-8"/>
    <title>Print Tickets for <?php echo $type; ?></title>
    <link rel="stylesheet" type="text/css" href="<?php echo WP_SYL_ENTRY_PASS_PLUGIN_URL; ?>/dist/print.css">
</head>
<body>
<div class="for_<?php echo esc_attr(strtolower($type)); ?>" id="app">
    <?php foreach ($attendees as $attendee): ?>
        <div class="card_holder">
            <div class="attendee_part attendee_top">
                <div class="qr_code">
                    <img src="<?php echo $attendee->image; ?>"/>
                </div>
                <div class="attendee_id">
                    <div class="id_text"><?php echo $attendee->attendee_uid; ?></div>
                </div>
                <div class="name_holder">
                    <div class="name"><?php echo $attendee->full_name; ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>
