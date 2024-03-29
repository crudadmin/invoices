<table style="margin-top: 10px;" width="100%">
    <tr>
        <td align="left" valign="top">
            @if ( $settings->hasVat($invoice) == false )
            {{ _('Nie sme platci DPH.') }}
            @endif
        </td>
        <td align="right" valign="top">
            @if ( config('invoices.issued_by') )
            <p>{{ _('Doklad vystavil') }}: {{ $settings->sign }}</p>
            @endif
        </td>
    </tr>
    @php
        $signatureHeight = $settings->signature_height ?: config('invoices.signature_height');
    @endphp
    @if ( $image = $settings->signature )
    <tr>
        <td colspan="2" align="right">
            <img src="{{ $image->resize(null, $signatureHeight * 2, true)->getBase64() }}" height="{{ $signatureHeight }}px">
        </td>
    </tr>
    @endif
</table>