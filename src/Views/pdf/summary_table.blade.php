<table width="100%" border="0" class="po">
    <tr>
        <td height="30" colspan="{{ $invoice->vat ? 4 : 3 }}">&nbsp;</td>
    </tr>
    <tr class="n">
        <td class="bw" bgcolor="#eee" align="right"><strong>{{ _('DPH') }}</strong></td>
        <td class="bw" bgcolor="#eee" align="right"><strong>{{ _('Bez DPH') }}</strong></td>
        <td class="bw" bgcolor="#eee" align="right"><strong>{{ _('DPH') }}</strong></td>
        <td bgcolor="#eee" align="right"><strong>{{ _('S DPH') }}</strong></td>
    </tr>
    @foreach( $summary['withTax'] as $tax => $price )
    <tr class="p">
        <td class="bb bl" align="right">{{ $tax }} %</td>
        <td class="bb" align="right">{{ priceFormat($summary['withoutTax'][$tax]) }} €</td>
        <td class="bb" align="right">{{ priceFormat($price - $summary['withoutTax'][$tax]) }} €</td>
        <td class="bb br" align="right">{{ priceFormat($price) }} €</td>
    </tr>
    @endforeach
</table>

<table width="100%" border="0" class="po">
    <tr>
        <td height="10" colspan="2">&nbsp;</td>
    </tr>
    <tr class="p">
        <td bgcolor="#eee" align="left"><h2><strong>{{ _('Celkom k úhrade') }} @if ( $invoice->paid_at ) {{ _('(zaplatené)') }}@endif</strong></h2></td>
        <td bgcolor="#eee" align="right"><h2><strong>{{ priceFormat( ! $invoice->paid_at ? $summary['totalWithTax'] : 0 ) }} €</strong></h2></td>
    </tr>
</table>