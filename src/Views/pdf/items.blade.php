<table width="100%" border="0" class="po">
  <tr class="n">
    <td class="bw" height="30" bgcolor="#eee" align="left"><strong>{{ _('Položky') }}</strong></td>
    <td class="bw" height="30" bgcolor="#eee" align="left"><strong>{{ _('Počet ks') }}</strong></td>
    <td class="bw" bgcolor="#eee" align="right"><strong>{{ _('Cena/ks bez DPH') }}</strong></td>
    <td class="bw" bgcolor="#eee" align="right"><strong>{{ _('DPH') }}</strong></td>
    <td bgcolor="#eee" align="right"><strong>{{ _('Cena spolu s DPH') }}</strong></td>
  </tr>
  @foreach( $items as $item )
    <tr class="p">
        <td class="bb bl" height="30" align="left">{{ $item->name }}</td>
        <td class="bb">{{ $item->quantity }}</td>
        <td class="bb" align="right">{{ priceFormat($item->price) }} €</td>
        <td class="bb" align="right">{{ $item->vat }} %</td>
        <td class="bb br" align="right">{{ priceFormat($item->totalPriceWithTax) }} €</td>
    </tr>
  @endforeach
</table>