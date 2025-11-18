<?php

namespace Gogol\Invoices\Helpers\Exports\Omega;

use Gogol\Invoices\Helpers\Exports\Omega\OmegaExport;

class OmegaEUDExport extends OmegaExport
{
    public function add($zip)
    {
        //Add money s3 export
        $zip->addFromString(
            './omega_eud_'.$this->exportInterval.'.txt',
            $this->getCsvString()
        );
    }

    public function getRows()
    {
        $rows = [
            ['R00', 'T00'],
        ];

        foreach ($this->invoices as $invoice) {
            $summary = $invoice->getPricesSummary();
            $rates = $this->getOmegaRates($invoice);
            $lower = $rates['medium'] ?? $rates['lower'];
            $higher = $rates['higher'];

            //TESTING
            // if ( $invoice->number != 'FV-202200XXXX' ){
            //     continue;
            // }

            $rows[] = [
                'R01', //A       R01
                $this->getInvoiceType($invoice), //B       typ dokladu
                'OF', //C       kod evidencie - tally code
                $this->getInvoiceNumberSequence($invoice), //D       kod ciselneho radu - sequence code
                $invoice->number, //E       cislo interne - internal number
                $invoice->vs, //F       externe cislo - external number
                $invoice->company_id ? $invoice->company_name : 'Maloobchodný predaj', //G       meno firmy partnera - company name of partner
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
                $lower, //U       Sadzba Nizsia - TAX rate lower
                $higher, //V       Sadzba Vyssia - TAX rate higher
                $this->getInvoiceTaxSum($invoice, $lower), //W       Zaklad Nizsia - VAT basis in lower VAT
                $this->getInvoiceTaxSum($invoice, $higher), //X       Zaklad Vyssia - VAT basis in higher VAT
                $this->getInvoiceTaxSum($invoice, 0), //Y       Zaklad 0 - VAT basis in null VAT
                '', //Z       Zaklad Neobsahuje - basis in VAT free
                $this->getInvoiceTaxSum($invoice, $lower, true) - $this->getInvoiceTaxSum($invoice, $lower), //AA      Suma DPH nizsia - Amount VAT lower
                $this->getInvoiceTaxSum($invoice, $higher, true) - $this->getInvoiceTaxSum($invoice, $higher), //AB      Suma DPH vyssia - Amount VAT higher
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
                $invoice->company_name, //AO      text hlavicky - text header
                '', //AP      pocet dni splatnosti - number of days due
                '', //AQ      prijate od/vyplatene komu - received from / paid to
                '', //AR      Kod IC DPH - code of VAT
                $invoice->company_vat_id, //AS      IC DPH - VAT
                '', //AT      Suma preddavku - amount of advance
                $invoice->company_id ? '' : 10, //AU      IČO poradové číslo -  REG order number
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
                isset($rates['medium']) ? $rates['lower'] : '',
                isset($rates['medium']) ? $this->getInvoiceTaxSum($invoice, $rates['lower']) : '', //Zaklad nizsia 2
                isset($rates['medium']) ? $this->getInvoiceTaxSum($invoice, $rates['lower'], true) - $this->getInvoiceTaxSum($invoice, $rates['lower']) : '', //Sadzba nizsia 2
            ];

            $rows[] = [
                'R02', // R02
                '0', // typ položky = 0 - item type = 0
                '311', // MD synteticky ucet - MD synthetic account
                '030', // MD analyticky ucet - MD analytical account
                '', // DAL synteticky ucet - DAL synthetic account
                '', // DAL analyticky ucet - DAL analytical account
                $invoice->price_vat, // suma TM - amount domestic currency
                $invoice->price_vat, // suma CM - amount foreign currency
                $invoice->company_name, // text polozky - text of item
                'S', // kod typu sumy - item type code
                '', // cislo uhradzaneho dokladu - number of reimbursement document
                '', // cislo odpocitavaneho dokladu - number of counting downdocument
                'X', // kod stredisko - code of center
                '(Nedefinované)', // nazov stredisko - name of center
                'X', // kod zakazka -code of order
                '(Nedefinované)', // nazov zakazka - name of order
                'X', // kod cinnost - code of operation
                '(Nedefinované)', // nazov cinnost - name of operation
                'X', // kod pracovnik - code of worker
                '(Nedefinované)', // meno pracovnik - name of worker
                '', // priezvisko pracovnik - surname of worker
                '', // cislo stredisko - center number
                '', // cislo zakazka - order number
                '', // cislo cinnost - operation number
                '', // cislo pracovnik - worker number
                'X', // Oddiel KV DPH - Part of VAT report
                '', // Druh tovaru KV DPH - Product type for VAT report
                '', // Kod tovaru KV DPH - Product code for VAT report
                '', // Merna jednotka KV DPH - Unit for VAT report
                '0.0000', // Mnozstvo KV DPH - Amount for VAT report
                '0', // ID strediska
                '', // ID zákazky
                '', // ID činnosti
                '', // ID pracovníka
                '', // Úhrada opačným znamienkom - Opposite sign payment
                '', // IDCislo uhradzaneho dokladu
                '', // IDCislo odpocitavaneho dokladu
            ];

            foreach ($invoice->items()->pluck('vat')->sort()->unique()->values() as $vat) {
                $rateKeys = [
                  0 => ['V', null],
                  5 => ['01', '02'],
                  19 => ['01', '02'],
                  23 => ['03', '04'],
                ];

                // If is medium vat, then for small use additional keys
                if ( isset($rates['medium']) && $vat == $rates['lower'] ) {
                    $rateKeys[$vat] = ['01a', '02a'];
                }

                $vatRatesAccounts = [
                    5 => 205,
                    19 => 219,
                    23 => 223,
                ];

                $rows[] = [
                    'R02',
                    '0',
                    '',
                    '',
                    '604',
                    '030',
                    $summary['withoutTax'][$vat],
                    $summary['withoutTax'][$vat],
                    $invoice->company_name, // text polozky - text of item
                    $rateKeys[$vat][0],
                    '',
                    '',
                    'X',
                    '(Nedefinované)',
                    'X',
                    '(Nedefinované)',
                    'X',
                    '(Nedefinované)',
                    'X',
                    '(Nedefinované)',
                    '',
                    '',
                    '',
                    '',
                    '',
                    $invoice->company_id ? 'A1' : 'D2',
                    '',
                    '',
                    '',
                    '0.0000',
                    '0',
                    '',
                    '',
                    '', // ID pracovníka
                    '', // Úhrada opačným znamienkom - Opposite sign payment
                    '', // IDCislo uhradzaneho dokladu
                    '', // IDCislo odpocitavaneho dokladu
                ];

                if ( $vat > 0 ) {
                    $rows[] = [
                        'R02',
                        '0',
                        '',
                        '',
                        '343',
                        $vatRatesAccounts[$vat],
                        $summary['tax'][$vat],
                        $summary['tax'][$vat],
                        $vat.'% - Sadzba DPH - DPH',
                        $rateKeys[$vat][1],
                        '',
                        '',
                        'X',
                        '(Nedefinované)',
                        'X',
                        '(Nedefinované)',
                        'X',
                        '(Nedefinované)',
                        'X',
                        '(Nedefinované)',
                        '',
                        '',
                        '',
                        '',
                        '',
                        $invoice->company_id ? 'A1' : 'D2',
                        '',
                        '',
                        '',
                        '0.0000',
                        '0',
                        '',
                        '',
                        '', // ID pracovníka
                        '', // Úhrada opačným znamienkom - Opposite sign payment
                        '', // IDCislo uhradzaneho dokladu
                        '', // IDCislo odpocitavaneho dokladu
                    ];
                }
            }
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