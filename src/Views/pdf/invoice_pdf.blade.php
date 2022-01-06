<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>{{ $invoice->number }}</title>
<style type="text/css">
body, table {font-family: tahoma; font-size: 11px; color: black; margin:0; padding:0}
h1 {font-family: tahoma; font-size: 15px; color: #000; font-weight:normal}
h1.h-title {font-family: tahoma; font-size: {{ config('invoices.logo_size', 48) }}px; color: #000; font-weight:bold;color: {{ $settings->invoice_color ?: '#3a92c3' }};}
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
.bl2 {border-left: solid 2px {{ $settings->invoice_color ?: '#3a92c3'  }}}
.bb2 {border-bottom: solid 2px {{ $settings->invoice_color ?: '#3a92c3'  }}}
.br2 {border-right: solid 2px {{ $settings->invoice_color ?: '#3a92c3'  }}}
.bt2top {border-top: solid 2px {{ $settings->invoice_color ?: '#3a92c3'  }}}
.ct {text-align:center}
.py {width:60px}
.fp {height:100px; width:100%; position:absolute; bottom:30px; left:0; display:table-cell; vertical-align:middle; text-align:center}
.fh {height:100px; width:100%; text-align:center}
@media print {.fh {height:100px; width:100%; position:absolute; bottom:30px; left:0; display:table-cell; vertical-align:middle; text-align:center}}
@php
  $signatureHeight = $settings->signature_height ?: config('invoices.signature_height');
  $logoHeight = $settings->logo_height ?: config('invoices.logo_height');
@endphp
</style>
</head>
<body>
<table width="100%" border="0">
  <tr>
    <td width="30%">
      @if ( $image = $settings->logo )
      <img src="{{ $image->resize(null, $logoHeight * 2, null, true)->path }}" height="{{ $logoHeight }}px" type="" alt="">
      @else
      <h1 class="h-title">{{ env('APP_NAME') }}</h1>
      @endif
    </td>
    <td width="70%" align="right">
      <h1>
        {{ $invoice->typeName }} <strong>{{ $invoice->number }}</strong>
        @if ( $invoice->type == 'proform' )
        <br><small style="font-size: 11px">{!! _('Proforma faktúra neslúži ako daňový doklad.<br>Faktúra (daňový doklad) bude vystavená po prijatí platby.') !!}</small>
        @endif
      </h1>
      @if ( $invoice->type == 'invoice' && $invoice->proform )
      {{ _('Tento doklad je úhradou proformy č.') }} {{ $invoice->proform->number }}
      @elseif ( $invoice->type == 'return' && $invoice->return )
      {{ _('K faktúre č.') }} {{ $invoice->return->number }}
      @endif
    </td>
  </tr>
  <tr>
    <td colspan="2" height="20px" width="100%"></td>
  </tr>
</table>
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

@if ( !empty($invoice->note) )
<table width="100%" border="0" style="margin: 0 0 20px">
  <tr>
    <td>{{ $invoice->note }}</td>
  </tr>
</table>
@endif

<table width="100%" border="0" class="po">
  <tr class="n">
    <td class="bw" height="30" bgcolor="#eee" align="left"><strong>{{ _('Položky') }}</strong></td>
    <td class="bw" height="30" bgcolor="#eee" align="left"><strong>{{ _('Počet ks') }}</strong></td>
    <td class="bw" bgcolor="#eee" align="right"><strong>{{ _('Cena/ks bez DPH') }}</strong></td>
    <td class="bw" bgcolor="#eee" align="right"><strong>{{ _('DPH') }}</strong></td>
    <td bgcolor="#eee" align="right"><strong>{{ _('Cena spolu s DPH') }}</strong></td>
  </tr>
  @php
  $with_tax = [];
  $without_tax = [];
  @endphp

  @foreach( $items as $item )
    @php
      foreach ([&$with_tax, &$without_tax] as &$value) {
        if ( ! array_key_exists(''.$item->vat, $value) )
          $value[$item->vat] = 0;
      }

      //Round order item price by configuration
      $itemTaxPrice = canRoundSummary() ? $item->price_vat * $item->quantity
                                        : calculateWithVat($item->price * $item->quantity, $item->vat);

      $without_tax[$item->vat] += $item->price * $item->quantity;
      $with_tax[$item->vat] += $itemTaxPrice;
    @endphp

    <tr class="p">
        <td class="bb bl" height="30" align="left">{{ $item->name }}</td>
        <td class="bb">{{ $item->quantity }}</td>
        <td class="bb" align="right">{{ priceFormat($item->price) }} €</td>
        <td class="bb" align="right">{{ $item->vat }} %</td>
        <td class="bb br" align="right">{{ priceFormat( $itemTaxPrice ) }} €</td>
    </tr>

  @endforeach
</table>

<table width="100%" border="0" class="po">
  <tr style="width: 100%">
    <td style="width: 50%;" valign="top">
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
    </td>
    <td style="width: 50%" valign="top">
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
        @php
          ksort($with_tax);

          $totalWithVat = 0;
        @endphp
        @foreach( $with_tax as $tax => $price )
        <tr class="p">
          <td class="bb bl" align="right">{{ $tax }} %</td>
          <td class="bb" align="right">{{ priceFormat($without_tax[$tax]) }} €</td>
          <td class="bb" align="right">{{ priceFormat($price - $without_tax[$tax]) }} €</td>
          <td class="bb br" align="right">{{ priceFormat($price) }} €</td>
          <?php $totalWithVat += $price ?>
        </tr>
        @endforeach
      </table>

      <table width="100%" border="0" class="po">
        <tr>
          <td height="10" colspan="2">&nbsp;</td>
        </tr>
        <tr class="p">
          <td bgcolor="#eee" align="left"><h2><strong>{{ _('Celkom k úhrade') }} @if ( $invoice->paid_at ) {{ _('(zaplatené)') }}@endif</strong></h2></td>
          <td bgcolor="#eee" align="right"><h2><strong>{{ priceFormat( ! $invoice->paid_at ? $totalWithVat : 0 ) }} €</strong></h2></td>
        </tr>
      </table>

      <table style="margin-top: 10px;" width="100%">
        <tr>
          <td align="left" valign="top">
            @if ( ! $settings->vat )
            {{ _('Nie sme platci DPH.') }}
            @endif
          </td>
          <td align="right" valign="top">
            <p>{{ _('Doklad vystavil') }}: {{ $settings->sign }}</p>
          </td>
        </tr>
        @if ( $image = $settings->signature )
        <tr>
          <td colspan="2" align="right">
            <img src="{{ $image->resize(null, $signatureHeight * 2, null, true)->path }}" height="{{ $signatureHeight }}px">
          </td>
        </tr>
        @endif
      </table>
    </td>
  </tr>
</table>

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
</body>
</html>