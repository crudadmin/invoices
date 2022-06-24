<style type="text/css">
body, table {font-family: tahoma; font-size: 11px; color: black; margin:0; padding:0}
h1 {font-family: tahoma; font-size: 15px; color: #000; font-weight:normal}
h1.h-title {font-family: tahoma; font-size: {{ config('invoices.logo_size', 48) }}px; color: #000; font-weight:bold;color: {{ $settings->invoice_color ?: '#3a92c3' }};}
h2 {font-family: tahoma; font-size: 15px}
table {border-spacing:0}
table.aa tr td {padding: 5px 5px 5px 5px;}
table.top {border-top: solid 2px #eee}
table.po tr.n td {padding:5px; font-size: 10px}
table.po tr.p td {padding:5px; font-size: 12px}
.bl {border-left: solid 1px #eee}
.br {border-right: solid 1px #eee}
.bb {border-bottom: solid 1px #eee}
.bw {border-right: solid 1px #eee}
.bl2 {border-left: solid {{ config('invoices.billing_border_size', 2) }}px {{ $settings->invoice_color ?: '#3a92c3'  }}}
.bb2 {border-bottom: solid {{ config('invoices.billing_border_size', 2) }}px {{ $settings->invoice_color ?: '#3a92c3'  }}}
.br2 {border-right: solid {{ config('invoices.billing_border_size', 2) }}px {{ $settings->invoice_color ?: '#3a92c3'  }}}
.bt2top {border-top: solid {{ config('invoices.billing_border_size', 2) }}px {{ $settings->invoice_color ?: '#3a92c3'  }}}
.ct {text-align:center}
.py {width:60px}
.fp {height:100px; width:100%; position:absolute; bottom:30px; left:0; display:table-cell; vertical-align:middle; text-align:center}
.fh {height:100px; width:100%; text-align:center}
@media print {.fh {height:100px; width:100%; position:absolute; bottom:30px; left:0; display:table-cell; vertical-align:middle; text-align:center}}
</style>