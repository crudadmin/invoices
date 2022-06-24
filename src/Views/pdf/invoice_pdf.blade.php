<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $invoice->number }}</title>
    @include('invoices::pdf.styles')
</head>
<body>
    @include('invoices::pdf.header')
    @include('invoices::pdf.billing')
    @include('invoices::pdf.note')
    @include('invoices::pdf.items')
    @include('invoices::pdf.summary')
</body>
</html>