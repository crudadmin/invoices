<table width="100%" border="0" cellpadding="0" cellspacing="0" class="--padding">
    <tr><td><small><u>{{ _('Dodávateľ') }}</u></small>:</td></tr>
    <tr><td><strong>{{ $settings->name }}</strong></td></tr>
    <tr><td>{{ $settings->street }}</td></tr>
    <tr><td>{{ implode(', ', array_filter([$settings->zipcode, $settings->city])) }}</td></tr>
    <tr><td>{{ $settings->country }}</td></tr>
    <tr><td>&nbsp;</tr>
    <tr><td>{{ _('IČO') }}: {{ $settings->company_id }}</td></tr>
    <tr><td>{{ _('DIČ') }}: {{ $settings->tax_id }}</td></tr>
    @if ( $settings->hasVat($invoice) )
    <tr><td>{{ _('IČ DPH') }}: {{ $settings->vat_id }}</td></tr>
    @endif
    @foreach($additionalRows as $i => $row)
    <tr>
        <td>
            @if ( $row['supplier'] ?? null )
            {!! $row['supplier'] !!}
            @endif
        </td>
    </tr>
    @endforeach
</table>