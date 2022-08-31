<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td width="36%" valign="top">
            @include('invoices::pdf.billing_supplier')
        </td>
        <td width="64%" valign="top">
            @include('invoices::pdf.billing_customer')
        </td>
    </tr>
    <tr>
        <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td width="36%" valign="top">
            @include('invoices::pdf.billing_supplier_info')
        </td>
        <td width="64%" valign="top">
            @include('invoices::pdf.billing_customer_info')
        </td>
    </tr>
    <tr class="p">
        <td colspan="2" style="padding-bottom: 30px">&nbsp;</td>
    </tr>
</table>