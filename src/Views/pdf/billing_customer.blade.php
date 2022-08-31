<table width="100%" border="0" cellpadding="0" cellspacing="0" class="--padding --border">
    <tr>
        <td width="50%" valign="top" class="--pt0 --pb0">
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr><td><small><u>{{ _('Fakturačné údaje') }}:</u></small></td></tr>
                <tr><td><strong>{{ $invoice->company_name }}</strong></td></tr>
                <tr><td>{{ $invoice->street }}</td></tr>
                <tr><td>{{ implode(', ', array_filter([$invoice->zipcode, $invoice->city])) }}</td></tr>
                <tr><td>{{ $invoice->country ? $invoice->country->name : '' }}</td></tr>
                @if ($invoice->company_id)
                <tr><td>&nbsp;</td></tr>
                <tr><td>{{ _('IČO') }}: {{ $invoice->company_id }}</td></tr>
                @endif
                @if ($invoice->company_tax_id)
                <tr><td>{{ _('DIČ') }}: {{ $invoice->company_tax_id }}</td></tr>
                @endif
                @if ($invoice->company_vat_id)
                <tr><td>{{ _('IČ DPH') }}: {{ $invoice->company_vat_id }}</td></tr>
                @endif
                @foreach($additionalRows as $i => $row)
                    @continue(!($rowData = $row['customer'] ?? null))
                    <tr><td>{!! $rowData !!}</td></tr>
                @endforeach
            </table>
        </td>
        @if ( config('invoices.delivery') )
        <td width="50%" valign="top" class="--pt0 --pb0">
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr><td><small><u>{{ _('Dodacia adresa').':' }}</u></td></tr>
                <tr><td>{{ $invoice->delivery_company_name }}</td></tr>
                <tr><td>{{ $invoice->delivery_street }}</td></tr>
                <tr><td>{{ implode(', ', array_filter([$invoice->delivery_zipcode, $invoice->delivery_city])) }}</td></tr>
                <tr><td>{{ $invoice->delivery_country ? $invoice->delivery_country->name : '' }}</td></tr>
            </table>
        </td>
        @endif
    </tr>
</table>