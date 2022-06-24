<table width="100%" border="0" cellpadding="0" cellspacing="0" class="aa">
    <tr>
        <td width="36%"><small><u>{{ _('Dodávateľ') }}</u></small>:</td>
        <td class="bt2top bl2"><small><u>{{ _('Fakturačné údaje') }}:</u></small></td>
        <td class="bt2top br2"><small><u>{{ config('invoices.delivery') ? _('Dodacia adresa').':' : '' }}</u></small></td>
    </tr>
    <tr>
        <td><strong>{{ $settings->name }}</strong></td>
        <td class="bl2"><strong>{{ $invoice->company_name }}</strong></td>
        <td class="br2">{{ $invoice->delivery_company_name }}</td>
    </tr>
    <tr>
        <td>{{ $settings->street }}</td>
        <td class="bl2">{{ $invoice->street }}</td>
        <td class="br2">{{ $invoice->delivery_street }}</td>
    </tr>
    <tr>
        <td>{{ implode(', ', array_filter([$settings->zipcode, $settings->city])) }}</td>
        <td class="bl2">{{ implode(', ', array_filter([$invoice->zipcode, $invoice->city])) }}</td>
        <td class="br2">{{ config('invoices.delivery') ? implode(', ', array_filter([$invoice->delivery_zipcode, $invoice->delivery_city])) : '' }}</td>
    </tr>
        <tr>
        <td>{{ $settings->country }}</td>
        <td class="bl2">{{ $invoice->country ? $invoice->country->name : '' }}</td>
        <td class="br2">{{ $invoice->delivery_country ? $invoice->delivery_country->name : '' }}</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td class="bl2">&nbsp;</td>
        <td class="br2">&nbsp;</td>
    </tr>
    <tr>
        <td>{{ _('IČO') }}: {{ $settings->company_id }}</td>
        <td width="32%" class="bl2">{{ _('IČO') }}: {{ $invoice->company_id }}</td>
        <td width="32%" class="br2"></td>
    </tr>
    <tr>
        <td>{{ _('DIČ') }}: {{ $settings->tax_id }}</td>
        <td width="32%" class="bl2">{{ _('DIČ') }}: {{ $invoice->company_tax_id }}</td>
        <td width="32%" class="br2"></td>
    </tr>
    <tr>
        <td>{{ _('IČ DPH') }}: {{ $settings->vat_id }}</td>
        <td width="32%" class="{{ count($additionalRows) == 0 ? 'bb2' : '' }} bl2">{{ _('IČ DPH') }}: {{ $invoice->company_vat_id }}</td>
        <td width="32%" class="{{ count($additionalRows) == 0 ? 'bb2' : '' }} br2"></td>
    </tr>
    @foreach($additionalRows as $i => $row)
    <tr>
        <td>
            @if ( $row['supplier'] ?? null )
            {!! $row['supplier'] !!}
            @endif
        </td>
        <td width="32%" class="{{ $i + 1 == count($additionalRows) ? 'bb2' : '' }} bl2">
            @if ( $row['customer'] ?? null )
            {!! $row['customer'] !!}
            @endif
        </td>
        <td width="32%" class="{{ $i + 1 == count($additionalRows) ? 'bb2' : '' }} br2"></td>
    </tr>
    @endforeach
    <tr>
        <td colspan="3">&nbsp;</td>
    </tr>
    <tr>
        <td><strong>{{ _('Účet dodávateľa') }}</strong></td>
    </tr>
    <tr>
        <td>{{ _('Čislo účtu') }}: {{ $settings->account }}</td>
        <td>{{ $invoice->payment_method ? (_('Spôsob úhrady').':') : '' }}</td>
        <td>{{ $invoice->payment_method ? $invoice->payment_method->name : null }}</td>
    </tr>
    <tr>
        <td>{{ _('IBAN') }}: {{ $settings->iban }}</td>
        <td><strong>{{ _('Variabilný symbol') }}:</strong></td>
        <td><strong>{{ $invoice->vs }}</strong></td>
    </tr>
    <tr>
        <td>{{ _('SWIFT Kód') }}: {{ $settings->swift }}</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td></td>
        @if ( $invoice->delivery_at )
        <td valign="top">{{ _('Dátum dodania') }}</td>
        <td valign="top">{{ $invoice->delivery_at->format('d.m.Y') }}</td>
        @endif
    </tr>
    <tr>
        <td>{{ _('Register') }}: {{ $settings->register }}</td>
        <td valign="top">{{ _('Dátum vystavenia') }}</td>
        <td valign="top">{{ $invoice->created_at->format('d.m.Y') }}</td>
    </tr>
    <tr>
        <td>{{ _('Číslo vložky') }}: {{ $settings->input }}</td>
        <td>{{ _('Dátum splatnosti') }}</td>
        <td>{{ $invoice->payment_date->format('d.m.Y') }}</td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr class="p" height="30px">
        <td colspan="3">&nbsp;</td>
    </tr>
</table>