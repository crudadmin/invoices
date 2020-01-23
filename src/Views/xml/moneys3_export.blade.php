<?php echo '<?xml version="1.0" encoding="UTF-8"?>' ?>
<MoneyData ICAgendy="{{ getInvoiceSettings('company_id') }}" KodAgendy="" HospRokOd="{{ $export->from->format('Y-m-d') }}" HospRokDo="{{ $export->to->format('Y-m-d') }}" description="faktúry prijaté a vystavené" ExpZkratka="_FP+FV" ExpDate="2017-09-16" ExpTime="10:39:00" VyberZaznamu="0">
    <SeznamFaktVyd>
        @foreach( $invoices as $invoice )
        <FaktVyd>
            <Doklad>{{ $invoice->number }}</Doklad>
            <GUID>{{ $invoice->getGuid() }}</GUID>
            <Rada>{{ !$invoice->isInvoice ? '0' : '20rr' }}</Rada>
            <CisRada>{{ (int)substr($invoice->number, 4) }}</CisRada>
            <Popis>{{ ($first = $invoice->items->first()) ? $first->name : 'Online objednávka' }}</Popis>
            <Vystaveno>{{ $invoice->created_at->format('Y-m-d') }}</Vystaveno>
            @if ( $invoice->isInvoice )
            <DatUcPr>{{ $invoice->created_at->format('Y-m-d') }}</DatUcPr>
            @endif
            <Splatno>{{ $invoice->payment_date ? $invoice->payment_date->format('Y-m-d') : '' }}</Splatno>
            @if ( $invoice->isInvoice || $invoice->isReturn )
            <Doruceno>{{ $invoice->created_at->format('Y-m-d') }}</Doruceno>
            <DatSkPoh>{{ $invoice->created_at->format('Y-m-d') }}</DatSkPoh>
            @endif
            <KonstSym>0</KonstSym>
            <ZjednD>0</ZjednD>
            <PlnenDPH>0</PlnenDPH>
            <VarSymbol>{{ $invoice->vs }}</VarSymbol>
            <CObjednavk>{{ $invoice->order_id ?: $invoice->vs }}</CObjednavk>
            <Ucet>{{ getInvoiceSettings('bank_name') }}</Ucet>
            <Druh>{{ $invoice->isInvoice || $invoice->isReturn ? 'N' : 'L' }}</Druh>
            <Dobropis>0</Dobropis>
            @if ( $invoice->isInvoice )
            <PredKontac>PRIJMY_D</PredKontac>
            @endif
            <Uhrada>{{ $invoice->payment_type }}</Uhrada>
            <SazbaDPH1>10</SazbaDPH1>
            <SazbaDPH2>20</SazbaDPH2>
            <Proplatit>{{ $invoice->isInvoice && $invoice->paid_at ? 0 : $invoice->price_vat }}</Proplatit>
            <Vyuctovano>0</Vyuctovano>
            <SouhrnDPH>
                <Zaklad0>{{ $invoice->isInvoice ? 0 : $invoice->price }}</Zaklad0>
                <Zaklad5>0</Zaklad5>
                <Zaklad22>0</Zaklad22>
                <DPH5>0</DPH5>
                <DPH22>0</DPH22>
            </SouhrnDPH>
            <Celkem>{{ $invoice->price_vat }}</Celkem>
            <Vystavil></Vystavil>
            <PriUhrZbyv>0</PriUhrZbyv>
            <ValutyProp>0</ValutyProp>
            <SumZaloha>{{ $invoice->isInvoice ? $invoice->price_vat : 0 }}</SumZaloha>
            <SumZalohaC>{{ $invoice->isInvoice ? $invoice->price_vat : 0 }}</SumZalohaC>
            <DodOdb>
                <ObchNazev>{{ $invoice->name }}</ObchNazev>
                <ObchAdresa>
                    <Ulice>{{ $invoice->street }}</Ulice>
                    <Misto>{{ $invoice->city }}</Misto>
                    <PSC>{{ $invoice->zipcode }}</PSC>
                    <Stat>{{ $invoice->country }}</Stat>
                    <KodStatu>SK</KodStatu>
                </ObchAdresa>
                <FaktNazev>{{ $invoice->name }}</FaktNazev>
                <ICO>{{ $invoice->company_id }}</ICO>
                <DIC>{{ $invoice->vat_id }}</DIC>
                <FaktAdresa>
                    <Ulice>{{ $invoice->street }}</Ulice>
                    <Misto>{{ $invoice->city }}</Misto>
                    <PSC>{{ $invoice->zipcode }}</PSC>
                    <Stat>{{ $invoice->country }}</Stat>
                    <KodStatu>SK</KodStatu>
                </FaktAdresa>
                <GUID></GUID>
                <Nazev>{{ $invoice->name }}</Nazev>
                <Tel>
                    <Pred></Pred>
                </Tel>
                <Fax>
                    <Pred></Pred>
                </Fax>
                <PlatceDPH>{{ $invoice->vat_id ? 1 : 0 }}</PlatceDPH>
                <FyzOsoba>{{ $invoice->company_id ? 0 : 1 }}</FyzOsoba>
                <DICSK>{{ $invoice->tax_id }}</DICSK>
            </DodOdb>
            <KonecPrij>
                <Nazev>{{ $invoice->name }}</Nazev>
                <Adresa>
                    <Ulice>{{ $invoice->street }}</Ulice>
                    <Misto>{{ $invoice->city }}</Misto>
                    <PSC>{{ $invoice->zipcode }}</PSC>
                    <Stat>{{ $invoice->country }}</Stat>
                    <KodStatu>SK</KodStatu>
                </Adresa>
                <GUID></GUID>
                <Tel>
                    <Pred></Pred>
                </Tel>
                <Fax>
                    <Pred></Pred>
                </Fax>
                <ICO>{{ $invoice->company_id }}</ICO>
                <DIC>{{ $invoice->tax_id }}</DIC>
                <PlatceDPH>{{ $invoice->vat_id ? 1 : 0 }}</PlatceDPH>
                <FyzOsoba>{{ $invoice->company_id ? 0 : 1 }}</FyzOsoba>
                <DICSK>{{ $invoice->tax_id }}</DICSK>
            </KonecPrij>
            <DopravTuz>0</DopravTuz>
            <DopravZahr>0</DopravZahr>
            <Sleva>0</Sleva>
            <Pojisteno>0</Pojisteno>
            @if ( $invoice->isProform )
            <Vyridit_do>{{ $invoice->payment_date->format('Y-m-d') }}</Vyridit_do>
            <Vyrizeno>{{ $invoice->proformInvoice ? $invoice->proformInvoice->payment_date->format('Y-m-d') : '' }}</Vyrizeno>

            <SeznamZalPolozek>
                @foreach( $invoice->items as $key => $item )
                <Polozka>
                    <Popis>{{ $item->name }}</Popis>
                    <PocetMJ>{{ $item->qty }}</PocetMJ>
                    <ZbyvaMJ>0</ZbyvaMJ>
                    <Cena>{{ $item->price }}</Cena>
                    <SazbaDPH>0</SazbaDPH>
                    <TypCeny>0</TypCeny>
                    <Sleva>0</Sleva>
                    <Vystaveno>{{ $invoice->created_at->format('Y-m-d') }}</Vystaveno>
                    <Vyridit_do>{{ $invoice->payment_date->format('Y-m-d') }}</Vyridit_do>
                    <Vyrizeno>{{ $invoice->proformInvoice ? $invoice->proformInvoice->payment_date->format('Y-m-d') : '' }}</Vyrizeno>
                    <Poradi>{{ $key + 1 }}</Poradi>
                    <Valuty>0</Valuty>
                    <Hmotnost>0</Hmotnost>
                    <CenaPoSleve>1</CenaPoSleve>
                    <NesklPolozka>
                        <TypZarDoby>N</TypZarDoby>
                        <ZarDoba>6</ZarDoba>
                        <PredPC>0</PredPC>
                    </NesklPolozka>
                </Polozka>
                @endforeach
            </SeznamZalPolozek>
            @else
            <SeznamPolozek>
                @foreach( $invoice->items as $key => $item )
                <Polozka>
                    <Popis>{{ $item->name }}</Popis>
                    <PocetMJ>{{ $item->qty }}</PocetMJ>
                    <SazbaDPH>0</SazbaDPH>
                    <Cena>{{ $item->price }}</Cena>
                    <CenaTyp>0</CenaTyp>
                    <Sleva>0</Sleva>
                    <Poradi>{{ $key + 1 }}</Poradi>
                    <Valuty>0</Valuty>
                    <NesklPolozka>
                        <MJ>ks.</MJ>
                        <Zaloha>0</Zaloha>
                        <TypZarDoby>N</TypZarDoby>
                        <ZarDoba>0</ZarDoba>
                        <Protizapis>0</Protizapis>
                        <Hmotnost>0</Hmotnost>
                    </NesklPolozka>
                    <CenaPoSleve>1</CenaPoSleve>
                </Polozka>
                @endforeach
            </SeznamPolozek>
            @endif
            <MojeFirma>
                <Nazev>{{ getInvoiceSettings('company_name') }}</Nazev>
                <Adresa>
                    <Ulice>{{ getInvoiceSettings('street') }}</Ulice>
                    <Misto>{{ getInvoiceSettings('city') }}</Misto>
                    <PSC>{{ getInvoiceSettings('zipcode') }}</PSC>
                    <Stat>{{ getInvoiceSettings('country') }}</Stat>
                    <KodStatu>{{ getInvoiceSettings('country_code', 'SK') }}</KodStatu>
                </Adresa>
                <ObchNazev>{{ getInvoiceSettings('company_name') }}</ObchNazev>
                <ObchAdresa>
                    <Ulice>{{ getInvoiceSettings('street') }}</Ulice>
                    <Misto>{{ getInvoiceSettings('city') }}</Misto>
                    <PSC>{{ getInvoiceSettings('zipcode') }}</PSC>
                    <Stat>{{ getInvoiceSettings('country') }}</Stat>
                    <KodStatu>{{ getInvoiceSettings('country_code', 'SK') }}</KodStatu>
                </ObchAdresa>
                <Tel>
                    <Pred></Pred>
                    <Cislo></Cislo>
                    <Klap></Klap>
                </Tel>
                <Fax>
                    <Pred></Pred>
                    <Cislo></Cislo>
                    <Klap></Klap>
                </Fax>
                <Mobil>
                    <Pred></Pred>
                    <Cislo></Cislo>
                </Mobil>
                <EMail></EMail>
                <WWW>{{ getInvoiceSettings('www') }}</WWW>
                <ICO>{{ getInvoiceSettings('company_id') }}</ICO>
                <DIC>{{ getInvoiceSettings('tax_id') }}</DIC>
                <DanIC>{{ getInvoiceSettings('vat_id', getInvoiceSettings('tax_id')) }}</DanIC>
                <Banka>{{ getInvoiceSettings('bank_name') }}</Banka>
                <Ucet>{{ getInvoiceSettings()->accountNumber['account'] }}</Ucet>
                <KodBanky>{{ getInvoiceSettings()->accountNumber['code'] }}</KodBanky>
                <KodPartn></KodPartn>
                <FyzOsoba>0</FyzOsoba>
                <SpisovaZnacka>{{ getInvoiceSettings('register') . ' - ' . getInvoiceSettings('input') }}</SpisovaZnacka>
                <MenaSymb>€</MenaSymb>
                <MenaKod>EUR</MenaKod>
            </MojeFirma>
        </FaktVyd>
        @endforeach
    </SeznamFaktVyd>
    <SeznamFaktVyd_DPP />
</MoneyData>