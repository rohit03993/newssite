<?php
function nm_color_choices()
{
    return array(
        'red' => array('name' => 'Red', 'hex' => '#ee1c24'),
        'blue' => array('name' => 'Blue', 'hex' => '#1d4ed8'),
        'navy' => array('name' => 'Navy', 'hex' => '#0f2744'),
        'green' => array('name' => 'Green', 'hex' => '#15803d'),
        'teal' => array('name' => 'Teal', 'hex' => '#0f766e'),
        'orange' => array('name' => 'Orange', 'hex' => '#ea580c'),
        'purple' => array('name' => 'Purple', 'hex' => '#7c3aed'),
        'black' => array('name' => 'Black', 'hex' => '#111111'),
    );
}

function nm_accent_hex($key)
{
    $choices = nm_color_choices();
    $key = (string) $key;
    if (!isset($choices[$key])) {
        $key = 'red';
    }
    return $choices[$key]['hex'];
}

function nm_accent_ink($hex)
{
    $hex = ltrim((string) $hex, '#');
    if (strlen($hex) !== 6) {
        return '#ffffff';
    }
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    $light = ((0.299 * $r) + (0.587 * $g) + (0.114 * $b)) / 255;
    return $light > 0.62 ? '#111111' : '#ffffff';
}
