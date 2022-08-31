<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td width="36%" valign="top">
            @include('invoices::pdf.billing_supplier')
        </td>
        <td width="64%" valign="top">
            @include('invoices::pdf.billing_customer')
        </td>
    </tr>
    <tr class="p" height="30px">
        <td colspan="2">&nbsp;</td>
    </tr>
</table>