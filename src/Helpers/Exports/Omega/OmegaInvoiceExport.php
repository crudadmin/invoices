<?php

namespace Gogol\Invoices\Helpers\Exports\Omega;

use Gogol\Invoices\Helpers\Exports\Omega\OmegaExport;
use Store;

class OmegaInvoiceExport extends OmegaExport
{
    public function add($zip)
    {
        //Add money s3 export
        $zip->addFromString(
            './omega_faktury_'.$this->exportInterval.'.txt',
            $this->getCsvString()
        );
    }

    public function getRows()
    {
        $rows = [
            ['R00', 'T01'],
        ];

        $lower = $this->getOmegaRates()['lower'];
        $higher = $this->getOmegaRates()['higher'];

        foreach ($this->invoices as $invoice) {
            $rows[] = [
                'R01', //A hlavicka
                $invoice->number, //B       cislo dokladu - receipt number
                $invoice->company_name, //C       meno partnera - partner's name
                $invoice->company_id, //D       ICO -  REG
                $invoice->created_at->format('d.m.Y'), //E       datum vystavenia/datum prijatia
                $invoice->payment_date->format('d.m.Y'), //F       datum splatnosti - due date
                $invoice->delivery_at?->format('d.m.Y'), //G       DUZP
                $this->getInvoiceTaxSum($invoice, $lower), //H       Zaklad Nizsia - VAT basis in lower VAT
                $this->getInvoiceTaxSum($invoice, $higher), //I       Zaklad Vyssia - VAT basis in higher VAT
                null, //J       Zaklad 0 - VAT basis in null VAT
                null, //K       Zaklad Neobsahuje - basis in VAT free
                $lower, //L       Sadzba Nizsia - TAX rate lower
                $higher, //M       Sadzba Vyssia - TAX rate higher
                $this->getInvoiceTaxSum($invoice, $lower, 'price_vat') - $this->getInvoiceTaxSum($invoice, $lower, 'price'), //N       Suma DPH nizsia - Amount VAT lower
                $this->getInvoiceTaxSum($invoice, $higher, 'price_vat') - $this->getInvoiceTaxSum($invoice, $higher, 'price'), //O       Suma DPH vyssia - Amount VAT higher
                null, //P       Halierove vyrovnanie - Price correction
                $invoice->price_vat, //Q       Suma spolu CM - Amount in all in foreign currency
                $this->getInvoiceType($invoice), //R   >>  typ dokladu
                'OF', //S       kod Ev- tally code
                $this->getInvoiceNumberSequence($invoice), //T       kod CR - code of sequence
                $invoice->client_id, //U       interne cislo partnera - internal partner number
                $invoice->client_id, //V       kod partnera - code of partner
                null, //W       stredisko - centre partner
                null, //X       prevadzka -plent partner
                $invoice->street, //Y       ulica - street
                $invoice->zipcode, //Z       PSC - postal code
                $invoice->city, //AA      mesto - city
                $invoice->company_tax_id, //AB      DIC/DU - TAX partner
                $invoice->created_at->format('H:i:s'), //AC      cas vystavenia - time of issue
                null, //AD      dod. Podmienky - terms of delivery and payments
                null, //AE      uvod - introduction
                null, //AF      zaver -completion, ending
                null, //AG      dod. List - bill of delivery
                $invoice->vs, //AH      cislo objednavky - order number
                null, //AI      vystavil - signed by
                null, //AJ      KS - constant symbol
                null, //AK      SS - specific symbol
                null, //AL      forma uhrady - payment
                null, //AM      sposob dopravy - shipment
                'EUR', //AN      Mena - currency
                null, //AO      Mnozstvo jednotky - quantity of unit currency
                null, //AP      Kurz - exchange rate
                $invoice->price_vat, //AQ      Suma spolu TM - amount in all - domestic currency
                null, //AR      Zakazkovy list - bill of custom-made
                $invoice->note, //AS      poznamka -comment
                null, //AT      predmet fakturacie - subject of invoicing
                null, //AU      partner stat - partner country
                $invoice->company_vat_id, //AV      Kod IC DPH - code of VAT
                $invoice->company_vat_id, //AW      IC DPH - VAT
                null, //AX      Dodavatel cislo uctu - suppliers number of bank account
                null, //AY      Dodavatel banka - supplier's name of bank
                null, //AZ      Dodavatel pobocka - supplier's branch of bank
                null, //BA      partner stat - partner country
                null, //BB      Kod vystavil - code of signed by
                null, //BC      Partner meno skratka - short name of partner
                null, //BD      Dodavatel  SWIFT - SWIFT of suppliers
                null, //BE      Dodavatel IBAN - IBAN of suppliers
                null, //BF      Dodavatel kod statu DPH - code country in VAT of suppliers
                null, //BG      Dodavatel IC pre DPH - VAT of suppliers
                null, //BH      Dodavatel stat - country of suppliers
                -Store::getRounding(), //BI      Zaokruhlenie - round
                null, //BJ      Sposob zaokruhlenia - round mode
                $invoice->company_id, //BK      IČO poradové číslo -  REG order number
                -Store::getRounding(), //BL      Zaokruhlenie položky - round of item
                null, //BM      Sprievodny text k preddavku - Accompanying text to advance
                null, //BN      Suma preddavku - amount of advance
                1, //BO      Spôsob výpočtu DPH - VAT calculation method
                null, //BP      Starý spôsob výpočtu DPH
                $invoice->created_at->format('d.m.Y'), //BQ      Datum vystavenia DF
                null, //BR      Úhradené cez ECR - paid via ECR
                $invoice->vs, //BS      VS
                $invoice->delivery_company_name ?: $invoice->company_name, //BT      Poštová adresa - Kontaktná osoba
                $invoice->delivery_company_name ?: $invoice->company_name, //BU      Poštová adresa - Firma
                null, //BV      Poštová adresa - Stredisko
                null, //BW      Poštová adresa - Prevádzka
                $invoice->delivery_street ?: $invoice->street, //BX      Poštová adresa - Ulica
                $invoice->delivery_zipcode ?: $invoice->zipcode, //BY      Poštová adresa - PSČ
                $invoice->delivery_city ?: $invoice->city, //BZ      Poštová adresa - Mesto
                null, //CA
                null, //CB      Typ zľavy za doklad
                null, //CC      Zľava za doklad
                null, //CD      rezervované
                null, //CE      Kontaktná osoba
                null, //CF      Telefón
                null, //CG      Uplatňovanie DPH podľa úhrad
                null, //CH      Doklad obsahuje makrokarty vystavené po starom
                null, //CI      IBAN partnera
                null, //CJ      Číslo účtu partnera
                null, //CK      JeOSS
                null, //CL      Kod štátu OSS
                null, //CM      Smerovanie OSS
                null, //CN      Kód štátu prevádzky OSS
                null, //CO      Kvartál pre zaradenie do OSS
                null, //CP      Rok pre zaradenie do OSS
            ];

            foreach ($invoice->items as $item) {
                $rows[] = [
                    'R02', // A       R02
                    $item->name, // B       nazov polozky - name of item
                    $item->quantity, // C       mnozstvo - quantity of item
                    null, // D       MJ - unit
                    $item->price, // E       jedn. cena bez DPH - unit price without VAT
                    $this->getItemVat($item), // F   >>  sadzba DPH -rate of VAT
                    0, // G       skladova cena - price in-store
                    $item->price, // H       cennikova cena - list price
                    0, // I       percento zlava - percent discount
                    null, // J       typ polozky - type of item
                    null, // K       cudzi nazov - foreign name
                    null, // L       EAN
                    null, // M       PLU
                    ENV('OMEGA_S_UCET', '604'), // N       S ucet - synthetic account
                    ENV('OMEGA_A_UCET', '030'), // O       A ucet - analytic account
                    null, // P       colny sadzobnik - tariff
                    null, // Q       JKPOV
                    null, // R       cislo karty/sluzby - item/service number
                    null, // S       volna polozka - free item
                    null, // T       nazov skladu - name of store
                    null, // U       kod stredisko - code of center
                    null, // V       nazov stredisko - name of center
                    null, // W       kod zakazka -code of order
                    null, // X       nazov zakazka - name of order
                    null, // Y       kod cinnost - code of operation
                    null, // Z       nazov cinnost - name of operation
                    null, // AA      kod pracovnik - code of worker
                    null, // AB      meno pracovnik - name of worker
                    null, // AC      priezvisko pracovnik - surname of worker
                    null, // AD      typ DPH - type of VAT
                    null, // AE      Pripravene - ready
                    null, // AF      Dodane - delivered
                    null, // AG      Vybavene - furnished
                    null, // AH      PripraveneMR - ready from last year
                    null, // AI      DodaneMR - delivered in last year
                    null, // AJ      Rezervovane - reserved
                    null, // AK      RezervovaneMR - reserved from last year
                    null, // AL      MJ odvodena - derived unit
                    null, // AM      Mnozstvo z odvodenej MJ -quantity of derived unit
                    null, // AN      cislo stredisko - center number
                    null, // AO      cislo zakazka - order number
                    null, // AP      cislo cinnost - operation number
                    null, // AQ      cislo pracovnik - worker number
                    null, // AR      ExtCisloPolozky - item Ext #
                    -Store::getRounding(), // AS      Zaokruhlenie - round
                    null, // AT      Spôsob zaokruhlenia - round mode
                    null, // AU      bola vybavena rucne - manually furnished
                    null, // AV      nazov zlavy - name of discount
                    null, // AW      cennikova cena s DPH - list price with VAT
                    null, // AX      ceny boli zadavane s DPH - prices was entered with VAT
                    $item->price_vat, // AY      jedn. cena s DPH - unit price with VAT
                    null, // AZ      zlava v EUR bez DPH - discount in EUR without VAT
                    null, // BA      zlava v EUR s DPH - discount in EUR with VAT
                    'A1', // BB      Oddiel KVDPH
                    null, // BC      Druh tovaru KVDPH
                    null, // BD      Kod tovaru KVDPH
                    null, // BE      MJ pre KVDH
                    null, // BF      Mnozstvo KVDPH
                ];
            }
        }

        return $rows;
    }

    private function getInvoiceType($invoice)
    {
        if ( $invoice->type == 'invoice' ){
            return '0';
        } else if ( $invoice->type == 'return' ){
            return '4';
        }
    }
}