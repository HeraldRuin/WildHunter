@props(['url'])
@php
    $headerColor = '#5291fa';
    if (function_exists('setting_item')) {
        $headerColor = setting_item('style_main_color', '#5291fa') ?: '#5291fa';
    }
@endphp
<tr>
<td class="header" bgcolor="{{ $headerColor }}" style="background-color: {{ $headerColor }}; padding: 30px; text-align: center;">
<a href="{{ $url }}" style="display: block; color: #ffffff; text-decoration: none;">
<h1 style="margin: 0; padding: 0; color: #ffffff; font-size: 32px; font-weight: 600; line-height: 1.2; text-align: center; font-family: Poppins, Arial, Helvetica, sans-serif;">Wild-hunter.ru</h1>
</a>
</td>
</tr>
