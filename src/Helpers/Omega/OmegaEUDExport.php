<?php

namespace Gogol\Invoices\Helpers\Omega;

use Gogol\Invoices\Helpers\Omega\OmegaExport;
use Store;

class OmegaEUDExport extends OmegaExport
{
    public function getRows()
    {
        $rows = [
            ['R00', 'T00'],
        ];

        foreach ($this->invoices as $invoice) {
            $rows[] = [
                'R01', //A       R01
                $this->getInvoiceType($invoice), //B       typ dokladu
                'OF', //C       kod evidencie - tally code
                $this->getInvoiceNumberSequence($invoice), //D       kod ciselneho radu - sequence code
                $invoice->number, //E       cislo interne - internal number
                $invoice->number, //F       externe cislo - external number
                $invoice->company_name, //G       meno firmy partnera - company name of partner
                $invoice->company_id, //H       ICO - REG
                $invoice->company_tax_id, //I       DIC/DU - TAX partner
                $invoice->created_at->format('d.m.Y'), //J       datum vystavenia - date of issue
                $invoice->created_at->format('d.m.Y'), //K       datum prijatia - date of receipt
                $invoice->payment_date->format('d.m.Y'), //L       datum splatnosti - due date
                $invoice->delivery_at?->format('d.m.Y'), //M       DUZP
                $invoice->delivery_at?->format('d.m.Y'), //N       DUUP
                'EUR', //O       mena - currency
                '', //P       mnozstvo jednotky - quantity of unit currency
                '', //Q       kurz NBS - exchange rate ECB
                '', //R       kurz banka - exchange rate bank
                $invoice->price_vat, //S       suma spolu CM - amount in all FC
                '', //T       suma spolu TM - amount in all DC
                self::VAT_LOWER, //U       Sadzba Nizsia - TAX rate lower
                self::VAT_HIGHER, //V       Sadzba Vyssia - TAX rate higher
                $this->getInvoiceTaxSum($invoice, self::VAT_LOWER), //W       Zaklad Nizsia - VAT basis in lower VAT
                $this->getInvoiceTaxSum($invoice, self::VAT_HIGHER), //X       Zaklad Vyssia - VAT basis in higher VAT
                '', //Y       Zaklad 0 - VAT basis in null VAT
                '', //Z       Zaklad Neobsahuje - basis in VAT free
                $this->getInvoiceTaxSum($invoice, self::VAT_LOWER, 'price_vat') - $this->getInvoiceTaxSum($invoice, self::VAT_LOWER, 'price'), //AA      Suma DPH nizsia - Amount VAT lower
                $this->getInvoiceTaxSum($invoice, self::VAT_HIGHER, 'price_vat') - $this->getInvoiceTaxSum($invoice, self::VAT_HIGHER, 'price'), //AB      Suma DPH vyssia - Amount VAT higher
                '', //AC      Halierove vyrovnanie - Price correction
                '', //AD  >>  zaevidoval - registered by
                '', //AE      konstantny symbol - constant symbol
                '', //AF      specificky symbol - specific symbol
                $invoice->vs, //AG      interne cislo uhradzaneho dokladu
                '', //AH      externe cislo uhradzaneho dokladu
                '', //AI      znamienko (len pre PD, BV) - sign
                '', //AJ      cislo uctu partnera - suppliers number of bank account
                '', //AK      interne cislo partnera - internal partner number
                '', //AL      kod partnera - code of partner
                '', //AM      cas vystavenia - time of issue
                $invoice->note, //AN      poznamka - comment
                '', //AO      text hlavicky - text header
                '', //AP      pocet dni splatnosti - number of days due
                '', //AQ      prijate od/vyplatene komu - received from / paid to
                '', //AR      Kod IC DPH - code of VAT
                $invoice->company_vat_id, //AS      IC DPH - VAT
                '', //AT      Suma preddavku - amount of advance
                '', //AU      IČO poradové číslo -  REG order number
                '', //AV      kód schválil - approved by - code
                '', //AW      schválil - approved by
                '', //AX      kód zaevidoval - registered by - code
                '', //AY      príznak neúčtovať - do not booking flag
                '', //AZ      kód zaúčtoval - booked by - code
                '', //BA      zaúčtoval - booked by
                '', //BB      číslo dokladu pre KV DPH - number fo KV VAT
                '', //BC      čísla pôvodných dokladov - origin number
                '', //BD      DDT
                '', //BE      Ucet partnera
                '', //BF      rezervované
                '', //BG      textový príznak importu/exportu - Import/Export code
                '', //BH      IDCislo
                '', //BI      Uplatňovanie DPH podľa úhrad
                '', //BJ      IBAN
                '', //BK      JeOSS
                '', //BL      Kod štátu OSS
                '', //BM      Smerovanie OSS
                '', //BN      Kód štátu prevádzky OSS
                '', //BO      Kvartál pre zaradenie do OSS
                '', //BP      Rok pre zaradenie do OSS
            ];
        }

        return $rows;
    }

    private function getInvoiceType($invoice)
    {
        if ( $invoice->type == 'invoice' ){
            return 100; //OF
        } else if ( $invoice->type == 'return' ){
            return 120; // OD
        }
    }
}