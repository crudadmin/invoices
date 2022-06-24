<table width="100%" border="0" class="po">
  <tr style="width: 100%">
    <td style="width: 50%;" valign="top">
      @include('invoices::pdf.summary_left')
    </td>
    <td style="width: 50%" valign="top">
      @include('invoices::pdf.summary_table')
      @include('invoices::pdf.summary_footer')
    </td>
  </tr>
</table>