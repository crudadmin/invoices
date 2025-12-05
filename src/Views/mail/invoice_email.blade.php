@component('mail::message')
# {{ sprintf(_('Dobrý deň %s,'), $invoice->company_name) }}

@if ( !empty($message) )
{!! $message !!}

@component('mail::panel')
<table width="100%" style="margin: 10px 0;">
    <tr>
        <td align="left"><strong>{{ _('Číslo dokladu') }}:</strong></td>
        <td align="right">{{ $invoice->number }}</td>
    </tr>
    <tr>
        <td align="left"><strong>{{ _('Dátum splatnosti') }}:</strong></td>
        <td align="right">{{ optional($invoice->payment_date)->format('d.m.Y') }}</td>
    </tr>
    <tr>
        <td align="left"><strong>{{ _('Celková suma') }}:</strong></td>
        <td align="right"><strong>{{ priceFormat($invoice->price_vat) }} €</strong></td>
    </tr>
</table>
@endcomponent
@endif

@component('mail::button', ['url' => $invoice->pdf])
    {{ _('Zobraziť doklad') }}
@endcomponent

@include('invoices::mail.partials.greeting');
@endcomponent
