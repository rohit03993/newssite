<?php
function nm_color_choices()
{
    return array(
        'red' => array('name' => 'Red on charcoal', 'hex' => '#e11d2e', 'header' => '#1a1a1a', 'ink' => '#ffffff', 'soft' => '#fff1f2'),
        'blue' => array('name' => 'Blue on navy', 'hex' => '#2563eb', 'header' => '#0f2744', 'ink' => '#ffffff', 'soft' => '#eff6ff'),
        'navy' => array('name' => 'Navy and gold', 'hex' => '#c9a227', 'header' => '#0b1f3a', 'ink' => '#ffffff', 'soft' => '#f8f5eb'),
        'green' => array('name' => 'Green', 'hex' => '#15803d', 'header' => '#14532d', 'ink' => '#ffffff', 'soft' => '#f0fdf4'),
        'teal' => array('name' => 'Teal', 'hex' => '#0f766e', 'header' => '#134e4a', 'ink' => '#ffffff', 'soft' => '#f0fdfa'),
        'orange' => array('name' => 'Orange', 'hex' => '#ea580c', 'header' => '#1c1917', 'ink' => '#ffffff', 'soft' => '#fff7ed'),
        'purple' => array('name' => 'Purple', 'hex' => '#7c3aed', 'header' => '#1e1b4b', 'ink' => '#ffffff', 'soft' => '#f5f3ff'),
        'black' => array('name' => 'Black', 'hex' => '#111111', 'header' => '#111111', 'ink' => '#ffffff', 'soft' => '#f5f5f5'),
    );
}

function nm_theme($key)
{
    $choices = nm_color_choices();
    $key = (string) $key;
    if (!isset($choices[$key])) {
        $key = 'red';
    }
    $theme = $choices[$key];
    $theme['key'] = $key;
    $theme['on'] = nm_accent_ink($theme['hex']);
    return $theme;
}

function nm_theme_css($key)
{
    $t = nm_theme($key);
    return ':root{'
        . '--accent:' . $t['hex'] . ';'
        . '--on-accent:' . $t['on'] . ';'
        . '--header:' . $t['header'] . ';'
        . '--header-ink:' . $t['ink'] . ';'
        . '--soft:' . $t['soft'] . ';'
        . '}';
}

function nm_accent_hex($key)
{
    return nm_theme($key)['hex'];
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
