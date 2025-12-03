@component('mail::message')
# {{ $invoice->subject->name }}

{{ sprintf(_('Zaznamenali sme nesprávnu platbu dokladu č. %s.'), $invoice->number) }}

@component('mail::table')
| {{ _('Popis') }}           | {{ _('Suma') }} |
|:----------------------------|---------------:|
| {{ _('Očakávaná suma') }}  | {{ priceFormat($expectedAmount) }} € |
| {{ _('Prijatá suma') }}   | {{ priceFormat($paidAmount) }} €    |
@endcomponent

@component('mail::button', ['url' => $invoice->pdf])
    {{ _('Zobraziť doklad') }}
@endcomponent

@endcomponent