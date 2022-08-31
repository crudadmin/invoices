<table width="100%" border="0" cellpadding="0" cellspacing="0" class="--padding --border">
    <tr>
        <td width="50%" valign="top">
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
        <td width="50%" valign="top">
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
<br>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="--padding">
    @if ( $invoice->payment_method )
    <tr>
        <td>{{ _('Spôsob úhrady') }}:</td>
        <td>{{ $invoice->payment_method ? $invoice->payment_method->name : null }}</td>
    </tr>
    @endif
    @if ( $invoice->vs )
    <tr>
        <td><strong>{{ _('Variabilný symbol') }}:</strong></td>
        <td><strong>{{ $invoice->vs }}</strong></td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    @endif
    <tr>
        <td>{{ _('Dátum vystavenia') }}</td>
        <td>{{ $invoice->created_at->format('d.m.Y') }}</td>
    </tr>
    @if ( $invoice->delivery_at )
    <tr>
        <td>{{ _('Dátum dodania') }}</td>
        <td>{{ $invoice->delivery_at->format('d.m.Y') }}</td>
    </tr>
    @endif
    <tr>
        <td>{{ _('Dátum splatnosti') }}</td>
        <td>{{ $invoice->payment_date->format('d.m.Y') }}</td>
    </tr>
</table>