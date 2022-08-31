<table width="100%" border="0" cellpadding="0" cellspacing="0" class="--padding">
    @if ( $settings->account || $settings->iban )
        <tr><td><strong>{{ _('Účet dodávateľa') }}</strong></td></tr>
        @if ( $settings->account )
        <tr><td>{{ _('Čislo účtu') }}: {{ $settings->account }}</td></tr>
        @endif
        @if ( $settings->iban )
        <tr><td>{{ _('IBAN') }}: {{ $settings->iban }}</td></tr>
        @endif
        @if ( $settings->swift )
        <tr><td>{{ _('SWIFT Kód') }}: {{ $settings->swift }}</td></tr>
        @endif
        <tr><td>&nbsp;</td></tr>
    @endif
    @if ( $settings->register )
    <tr><td>{{ _('Register') }}: {{ $settings->register }}</td></tr>
    @endif
    @if ( $settings->input )
    <tr><td>{{ _('Číslo vložky') }}: {{ $settings->input }}</td></tr>
    @endif
</table>