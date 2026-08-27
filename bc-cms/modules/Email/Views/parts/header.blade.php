@php
    $main_color = setting_item('style_main_color', '#5291fa') ?: '#5291fa';
    $email_header = setting_item_with_lang('email_header');
    $linkStyle = 'color:#ffffff !important;text-decoration:none !important;-webkit-text-fill-color:#ffffff !important;';

    if ($email_header) {
        $email_header = preg_replace_callback('/<a\b([^>]*)>/i', function ($matches) use ($linkStyle) {
            $attrs = $matches[1];
            if (preg_match('/\bstyle\s*=\s*(["\'])(.*?)\1/is', $attrs, $styleMatch)) {
                $quote = $styleMatch[1];
                $merged = $linkStyle . $styleMatch[2];
                $attrs = preg_replace('/\bstyle\s*=\s*(["\'])(.*?)\1/is', 'style=' . $quote . $merged . $quote, $attrs, 1);
            } else {
                $attrs .= ' style="' . $linkStyle . '"';
            }
            return '<a' . $attrs . '>';
        }, $email_header);
    } else {
        $title = e(mail_site_title());
        $url = e(url('/'));
        $email_header = '<a href="' . $url . '" style="' . $linkStyle . ' display:block;text-align:center;font-size:32px;font-weight:600;font-family:Poppins,Arial,Helvetica,sans-serif;">'
            . $title
            . '</a>';
    }
@endphp
<div>
    <div class="b-container">
        <div class="b-header" bgcolor="{{ $main_color }}" style="background-color: {{ $main_color }} !important; padding: 30px; color: #ffffff !important; text-align: center;">
            {!! $email_header !!}
        </div>
    </div>
</div>
