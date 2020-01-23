<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>{{ $invoice->number }}</title>
<style type="text/css">
body, table {font-family: tahoma; font-size: 11px; color: black; margin:0; padding:0}
h1 {font-family: tahoma; font-size: 15px; color: #000; font-weight:normal}
h1.h-title {font-family: tahoma; font-size: 48px; color: #000; font-weight:bold;color: {{ getInvoiceSettings()->invoice_color ?: '#3a92c3' }};}
h2 {font-family: tahoma; font-size: 15px}
table {border-spacing:0}
table.aa tr td {padding: 5px 5px 5px 5px;}
table.top {border-top: solid 2px #eee}
table.po tr.n td {padding:5px; font-size: 10px}
table.po tr.p td {padding:5px; font-size: 12px}
.bl {border-left: solid 1px #eee}
.br {border-right: solid 1px #eee}
.bb {border-bottom: solid 1px #eee}
.bw {border-right: solid 1px #eee}
.bl2 {border-left: solid 2px {{ getInvoiceSettings()->invoice_color ?: '#3a92c3'  }}}
.bb2 {border-bottom: solid 2px {{ getInvoiceSettings()->invoice_color ?: '#3a92c3'  }}}
.br2 {border-right: solid 2px {{ getInvoiceSettings()->invoice_color ?: '#3a92c3'  }}}
.bt2top {border-top: solid 2px {{ getInvoiceSettings()->invoice_color ?: '#3a92c3'  }}}
.ct {text-align:center}
.py {width:60px}
.fp {height:100px; width:100%; position:absolute; bottom:30px; left:0; display:table-cell; vertical-align:middle; text-align:center}
.fh {height:100px; width:100%; text-align:center}
@media print {.fh {height:100px; width:100%; position:absolute; bottom:30px; left:0; display:table-cell; vertical-align:middle; text-align:center}}
</style>
</head>
<body>
<table width="100%" border="0">
  <tr>
    <td width="43%">
      @if ( $image = getInvoiceSettings()->logo )
      <img src="{{ $image->resize(null, 100, null, true)->path }}" height="40px" type="" alt="">
      @else
      <h1 class="h-title">{{ env('APP_NAME') }}</h1>
      @endif
    </td>
    <td width="57%" align="right">
      <h1>{{ $invoice->typeName }} <strong>{{ $invoice->number }}</strong></h1>
      @if ( $invoice->type == 'invoice' && $invoice->proform )
      Tento doklad je úhradou proformy č. {{ $invoice->proform->number }}
      @elseif ( $invoice->type == 'return' && $invoice->return )
      K faktúre č. {{ $invoice->return->number }}
      @endif
    </td>
  </tr>
  <tr>
    <td colspan="2" height="20px" width="100%"></td>
  </tr>
</table>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="aa">
  <tr>
    <td width="36%"><small><u>Dodávateľ</u></small>:</td>
    <td class="bt2top bl2"><small><u>Fakturačné údaje:</u></small></td>
    <td class="bt2top br2"><small><u>{{ config('invoices.delivery') ? 'Dodacia adresa:' : '' }}</u></small></td>
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
    <td>IČO: {{ $settings->company_id }}</td>
    <td width="32%" class="bl2">IČO: {{ $invoice->company_id }}</td>
    <td width="32%" class="br2"></td>
  </tr>
  <tr>
    <td>DIČ: {{ $settings->tax_id }}</td>
    <td width="32%" class="bl2">DIČ: {{ $invoice->tax_id }}</td>
    <td width="32%" class="br2"></td>
  </tr>
  <tr>
    <td>IČ DPH: {{ $settings->vat_id }}</td>
    <td width="32%" class="bb2 bl2">IČ DPH: {{ $invoice->vat_id }}</td>
    <td width="32%" class="bb2 br2"></td>
  </tr>
  <tr>
    <td colspan="3">&nbsp;</td>
  </tr>

  <tr>
    <td><strong>Účet dodávateľa</strong></td>
  </tr>

  <tr>
    <td>Čislo účtu: {{ $settings->account }}</td>
    <td>Spôsob úhrady:</td>
    <td>{{ $invoice->payment_method->name }}</td>
  </tr>

  <tr>
    <td>IBAN: {{ $settings->iban }}</td>
    <td><strong>Variabilný symbol:</strong></td>
    <td><strong>{{ $invoice->vs }}</strong></td>
  </tr>

  <tr>
    <td>SWIFT Kód: {{ $settings->swift }}</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>

  <tr>
    <td></td>
  </tr>

  <tr>
    <td>Register: {{ $settings->register }}</td>
    <td valign="top">Dátum vystavenia</td>
    <td valign="top">{{ $invoice->created_at->format('d.m.Y') }}</td>
  </tr>
  <tr>
    <td>Číslo vložky: {{ $settings->input }}</td>
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

@if ( !empty($invoice->note) )
<table width="100%" border="0" style="margin: 0 0 20px">
  <tr>
    <td>{{ $invoice->note }}</td>
  </tr>
</table>
@endif

<table width="100%" border="0" class="po">
  <tr class="n">
    <td class="bw" height="30" bgcolor="#eee" align="left"><strong>Položky</strong></td>
    <td class="bw" height="30" bgcolor="#eee" align="left"><strong>Počet ks</strong></td>
    <td class="bw" bgcolor="#eee" align="right"><strong>Cena/ks bez DPH</strong></td>
    <td class="bw" bgcolor="#eee" align="right"><strong>DPH</strong></td>
    <td bgcolor="#eee" align="right"><strong>Cena spolu s DPH</strong></td>
  </tr>
  @php
  $with_tax = [];
  $without_tax = [];
  @endphp

  @foreach( $items as $item )
    <tr class="p">
        <td class="bb bl" height="30" align="left">{{ $item->name }}</td>
        <td class="bb">{{ $item->quantity }}</td>
        <td class="bb" align="right">{{ priceFormat($item->price) }} €</td>
        <td class="bb" align="right">{{ $item->vat }} %</td>
        <td class="bb br" align="right">{{ priceFormat( $item->price_vat * $item->quantity ) }} €</td>
    </tr>

    @php
      foreach ([&$with_tax, &$without_tax] as &$value) {
        if ( ! array_key_exists(''.$item->vat, $value) )
          $value[$item->vat] = 0;
      }

      $with_tax[$item->vat] += $item->price_vat * $item->quantity;
      $without_tax[$item->vat] += calculateWithoutVat($item->price_vat * $item->quantity, $item->vat);
    @endphp
  @endforeach
</table>

<table width="100%" border="0" class="po">
  <tr style="width: 100%">
    <td style="width: 50%; padding-top: 30px">
      <p>Doklad vystavil: {{ $settings->sign }}</p>
      <br>
      @if ( $image = getInvoiceSettings()->signature )
      <img src="{{ $image->resize(null, 180, null, true)->path }}" height="180px">
      @endif
    </td>
    <td style="width: 50%">
      <table width="100%" border="0" class="po">
        <tr>
          <td height="30" colspan="{{ $invoice->vat ? 4 : 3 }}">&nbsp;</td>
        </tr>
        <tr class="n">
          <td class="bw" bgcolor="#eee" align="right"><strong>DPH</strong></td>
          <td class="bw" bgcolor="#eee" align="right"><strong>Bez DPH</strong></td>
          <td class="bw" bgcolor="#eee" align="right"><strong>DPH</strong></td>
          <td bgcolor="#eee" align="right"><strong>S DPH</strong></td>
        </tr>
        @php
          ksort($with_tax)
        @endphp
        @foreach( $with_tax as $tax => $price )
        <tr class="p">
          <td class="bb bl" align="right">{{ $tax }} %</td>
          <td class="bb" align="right">{{ priceFormat($without_tax[$tax]) }} €</td>
          <td class="bb" align="right">{{ priceFormat($price - $without_tax[$tax]) }} €</td>
          <td class="bb br" align="right">{{ priceFormat($price) }} €</td>
        </tr>
        @endforeach
      </table>

      <table width="100%" border="0" class="po">
        <tr>
          <td height="10" colspan="2">&nbsp;</td>
        </tr>
        <tr class="p">
          <td bgcolor="#eee" align="left"><h2><strong>Celkom k úhrade @if ( $invoice->paid_at ) (zaplatené)@endif</strong></h2></td>
          <td bgcolor="#eee" align="right"><h2><strong>{{ priceFormat( ! $invoice->paid_at ? $invoice->price_vat : 0 ) }} €</strong></h2></td>
        </tr>
      </table>
    </td>
  </tr>
</table>

@if ( ! $settings->vat )
<table width="100%" border="0" style="margin-top: 10px">
  <tr>
    <td height="10" align="right">Nie sme platci DPH.</td>
  </tr>
</table>
@endif

<table width="100%" border="0" class="po ct">
  <tr>
    <td height="10" colspan="{{ $invoice->vat ? 5 : 4 }}">&nbsp;</td>
  </tr>
  <tr class="p">
    <td width="50%">&nbsp;</td>
    <td width="10%">&nbsp;</td>
    <td width="10%">&nbsp;</td>
    <td width="10%">&nbsp;</td>
    @if ( $invoice->vat )
    <td width="10%">&nbsp;</td>
    @endif
  </tr>
</table>



<table width="100%" border="0" class="po">
  <tr class="p">
    <td height="14" colspan="3"><small>{{ $invoice->type == 'proform' ? 'Proforma faktúra neslúži ako daňový doklad. Faktúra (daňový doklad) bude vystavená po prijatí platby.' : '' }}</small></td>
  </tr>
  <tr class="p">
    <td height="6" colspan="3" align="left"></td>
  </tr>
  <tr class="p"></tr>
</table>


</body>
</html>