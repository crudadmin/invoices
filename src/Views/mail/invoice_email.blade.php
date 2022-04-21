@component('mail::message')
# {{ sprintf(_('Dobrý deň %s,'), $invoice->company_name) }}

{{ $settings->email_message }}

@if ( !empty($message) )
@component('mail::panel')
{{ $message }}
@endcomponent
@endif

@component('mail::button', ['url' => $invoice->pdf])
    {{ sprintf(_('Stiahnuť %s'), $invoice->typeNameWithNumber) }}
@endcomponent

{{ $settings->email_greeting }}<br>
@endcomponent
