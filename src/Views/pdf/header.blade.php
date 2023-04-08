@php
    $logoHeight = $settings->logo_height ?: config('invoices.logo_height');
@endphp
<table width="100%" border="0">
    <tr>
        <td width="30%">
            @if ( $image = $settings->logo )
            <img src="{{ $image->resize(null, $logoHeight * 2, true)->getBase64() }}" height="{{ $logoHeight }}px" type="" alt="">
            @else
            <h1 class="h-title">{{ env('APP_NAME') }}</h1>
            @endif
        </td>
        <td width="70%" align="right">
            <h1>
                {{ $invoice->typeName }} {{ _('č.') }} <strong>{{ $invoice->number }}</strong>
                @if ( $invoice->type == 'proform' )
                <br><small style="font-size: 11px">{!! _('Proforma faktúra neslúži ako daňový doklad.<br>Faktúra (daňový doklad) bude vystavená po prijatí platby.') !!}</small>
                @endif
            </h1>
            @if ( in_array($invoice->type, ['invoice', 'advance']) && $invoice->proform )
                @if ( $invoice->proform->type == 'proform' )
                    {{ _('Tento doklad je úhradou proformy č.') }} {{ $invoice->proform->number }}
                @elseif ( $invoice->proform->type == 'advance' )
                    {{ _('Odpočet zálohy k Proforma faktúre č.') }} {{ $invoice->proform->number }}
                @endif
            @elseif ( $invoice->type == 'return' && $invoice->return )
            {{ _('K faktúre č.') }} {{ $invoice->return->number }}
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="2" height="20px" width="100%"></td>
    </tr>
</table>