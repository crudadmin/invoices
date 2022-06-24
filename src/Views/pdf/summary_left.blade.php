@if ( isset($qrimage) )
<table style="border: 2px solid #eee; margin-top: 30px; padding: 5px">
    <tr>
        <td style="padding-bottom: 10px"><strong>{{ _('QR Platba') }}:</strong></td>
    </tr>
    <tr>
        <td>
            <img src="{{ $qrimage }}" style="margin: 0;max-width: {{ config('invoices.qrcode_width', 75) }}px">
        </td>
    </tr>
</table>
@endif