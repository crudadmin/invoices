<?php

namespace Gogol\Invoices\Helpers;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Throwable;
use Log;

class QRCodeGenerator
{
    protected $qrGenerators = [
        'sk' => 'generatePayBySquareCode',
        'cz' => 'generateCzechQRCode',
    ];

    public function generate($invoice)
    {
        $qrType = strtolower(config('invoices.qrcode_type') ?: '');

        if (
            config('invoices.qrcode', true) !== true
            || !array_key_exists($qrType, $this->qrGenerators)
        ){
            return;
        }

        //IF extensions are not available
        try {
            if ( !($data = $this->{$this->qrGenerators[$qrType]}($invoice)) ) {
                return;
            }
        } catch(Throwable $error){
            Log::error($error);

            return;
        }

        $options = new QROptions([
            'addQuietzone' => false,
        ]);

        $image = (new QRCode($options))->render($data);

        return $image;
    }

    public function generateCzechQRCode($invoice)
    {
        $data = 'SPD*1.0*ACC:'.getInvoiceSettings('iban').'*AM:'.$invoice->price_vat.'*CC:EUR*X-VS:'.$invoice->vs.'*MSG:QRPLATBA';

        return $data;
    }

    public function generatePayBySquareCode($invoice)
    {
        $xzDriverPath = ENV('INVOICES_XZ_PATH') ?: 'xz';

        //Schema
        //https://bsqr.co/schema/
        //https://www.vutbr.cz/www_base/zav_prace_soubor_verejne.php?file_id=206738
        $d = implode("\t", array(
            0 => '',
            1 => '1',
            2 => implode("\t", array(
                true,
                $invoice->price_vat,                    // SUMA
                'EUR',                                  // JEDNOTKA
                '',  // DATUM
                $invoice->vs,                           // VARIABILNY SYMBOL
                '',                                     // KONSTANTNY SYMBOL
                '',                                     // SPECIFICKY SYMBOL
                '',                                     // REFERENCNA HODNOTA PRIJMATELA
                '',                                     // POZNAMKA
                '1',
                getInvoiceSettings('iban'),             // IBAN
                getInvoiceSettings('swift'),            // SWIFT
                '0',
                '0',
            ))
        ));
        $d = strrev(hash("crc32b", $d, TRUE)) . $d;
        $x = proc_open($xzDriverPath." '--format=raw' '--lzma1=lc=3,lp=0,pb=2,dict=128KiB' '-c' '-'", [0 => ["pipe", "r"], 1 => ["pipe", "w"]], $p);
        fwrite($p[0], $d);
        fclose($p[0]);
        $o = stream_get_contents($p[1]);
        fclose($p[1]);
        proc_close($x);

        if ( !$o ){
            return;
        }

        $d = bin2hex("\x00\x00" . pack("v", strlen($d)) . $o);
        $b = "";
        for ($i = 0;$i < strlen($d);$i++) {
            $b .= str_pad(base_convert($d[$i], 16, 2), 4, "0", STR_PAD_LEFT);
        }
        $l = strlen($b);
        $r = $l % 5;
        if ($r > 0) {
            $p = 5 - $r;
            $b .= str_repeat("0", $p);
            $l += $p;
        }
        $l = $l / 5;
        $d = str_repeat("_", $l);
        for ($i = 0;$i < $l;$i += 1) {
            $d[$i] = "0123456789ABCDEFGHIJKLMNOPQRSTUV"[bindec(substr($b, $i * 5, 5))];
        }

        return $d;
    }
}