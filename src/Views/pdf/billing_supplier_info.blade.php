<table width="100%" border="0" cellpadding="0" cellspacing="0" class="--padding">
    @if ( $settings->account?->number || $settings->account?->iban )
        <tr><td><strong>{{ _('Účet dodávateľa') }}</strong></td></tr>
        @if ( $settings->account?->number )
        <tr><td>{{ _('Čislo účtu') }}: {{ $settings->account->number }}</td></tr>
        @endif
        @if ( $settings->account?->iban )
        <tr><td>{{ _('IBAN') }}: {{ $settings->account?->iban }}</td></tr>
        @endif
        @if ( $settings->account?->swift )
        <tr><td>{{ _('SWIFT Kód') }}: {{ $settings->account?->swift }}</td></tr>
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