@component('mail::message')
# {{ _('Dobrý deň') }},

{{ _('Radi by sme Vás informovali, že evidujeme neuhradené faktúry po splatnosti.') }}

@component('mail::table')
| {{ _('Číslo dokladu') }} | {{ _('Splatnosť') }} | {{ _('Suma') }} |
|:------------------------ | --------------:| --------------:|
@foreach ($invoices as $invoice)
| [{{ $invoice->number }}]({{ $invoice->getPdf()->url }}) | {{ $invoice->payment_date->format('d.m.Y') }} | <strong>{{ priceFormat($invoice->price_vat) }} €</strong> |
@endforeach
@endcomponent

@include('invoices::mail.partials.greeting');
@endcomponent
