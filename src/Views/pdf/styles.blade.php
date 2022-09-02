<style type="text/css">
    body, table {font-family: tahoma; font-size: 11px; color: black; margin:0; padding:0}
    h1 {font-family: tahoma; font-size: 15px; color: #000; font-weight:normal}
    h1.h-title {font-family: tahoma; font-size: {{ config('invoices.logo_size', 48) }}px; color: #000; font-weight:bold;color: {{ $settings->invoice_color ?: '#3a92c3' }};}
    h2 {font-family: tahoma; font-size: 15px}
    table {border-spacing:0}
    table.--padding tr td {padding-bottom: {{ config('invoices.line_padding_size') }}px;padding-top: {{ config('invoices.line_padding_size') }}px;}
    @if ( $borderSize = config('invoices.billing_border_size') )
    table.--border {padding: {{ config('invoices.line_padding_size') }} {{ config('invoices.line_padding_size') * 2 }}px {{ config('invoices.line_padding_size') }} {{ config('invoices.line_padding_size') * 2 }}px;border: solid {{ $borderSize }}px {{ $settings->invoice_color ?: '#3a92c3'  }}}
    @endif
    table td.--pt0 {padding-top: 0}
    table td.--pb0 {padding-bottom: 0}
    table.top {border-top: solid 2px #eee}
    table.po tr.n td {padding:5px; font-size: 10px}
    table.po tr.p td {padding:5px; font-size: 12px}
    .bl {border-left: solid 1px #eee}
    .br {border-right: solid 1px #eee}
    .bb {border-bottom: solid 1px #eee}
    .bw {border-right: solid 1px #eee}
    .ct {text-align:center}
    .py {width:60px}
    .fp {height:100px; width:100%; position:absolute; bottom:30px; left:0; display:table-cell; vertical-align:middle; text-align:center}
    .fh {height:100px; width:100%; text-align:center}
    @media print {.fh {height:100px; width:100%; position:absolute; bottom:30px; left:0; display:table-cell; vertical-align:middle; text-align:center}}
</style>

@include('invoices::pdf.styles_custom')