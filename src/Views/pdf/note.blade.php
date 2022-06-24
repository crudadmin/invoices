@if ( !empty($invoice->note) )
<table width="100%" border="0" style="margin: 0 0 20px">
    <tr>
        <td>{{ $invoice->note }}</td>
    </tr>
</table>
@endif