<table width="100%" border="0" cellpadding="0" cellspacing="0" class="--padding">
    @if ( $settings->account?->number || $settings->account?->iban )
    <tr><td>&nbsp;</td></tr>
    @endif
    @if ( $invoice->payment_method )
    <tr>
        <td width="50%">{{ _('Spôsob úhrady') }}:</td>
        <td width="50%">{{ $invoice->payment_method ? $invoice->payment_method->name : null }}</td>
    </tr>
    @endif
    @if ( $invoice->vs )
    <tr>
        <td width="50%"><strong>{{ _('Variabilný symbol') }}:</strong></td>
        <td width="50%"><strong>{{ $invoice->vs }}</strong></td>
    </tr>
    <tr>
        <td width="50%">&nbsp;</td>
        <td width="50%">&nbsp;</td>
    </tr>
    @endif
    <tr>
        <td width="50%">{{ _('Dátum vystavenia') }}</td>
        <td width="50%">{{ $invoice->created_at->format('d.m.Y') }}</td>
    </tr>
    @if ( $invoice->delivery_at )
    <tr>
        <td width="50%">{{ _('Dátum dodania') }}</td>
        <td width="50%">{{ $invoice->delivery_at->format('d.m.Y') }}</td>
    </tr>
    @endif
    <tr>
        <td width="50%">{{ _('Dátum splatnosti') }}</td>
        <td width="50%">{{ $invoice->payment_date->format('d.m.Y') }}</td>
    </tr>
</table>