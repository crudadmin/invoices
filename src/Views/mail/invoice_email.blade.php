@component('mail::message')
# {{ sprintf(_('Dobrý deň %s,'), $invoice->company_name) }}

@if ( !empty($message) )
@component('mail::panel')
{{ $message }}
@endcomponent
@endif

@component('mail::button', ['url' => $invoice->pdf])
    {{ _('Stiahnuť doklad') }}
@endcomponent

{{ $settings->email_greeting }}<br>
@endcomponent
