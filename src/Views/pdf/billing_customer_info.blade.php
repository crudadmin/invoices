<table width="100%" border="0" cellpadding="0" cellspacing="0" class="--padding">
    @if ( $settings->account || $settings->iban )
    <tr><td>&nbsp;</td></tr>
    @endif
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