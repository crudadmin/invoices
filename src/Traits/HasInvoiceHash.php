<?php

namespace Gogol\Invoices\Traits;

use Illuminate\Support\Str;

trait HasInvoiceHash
{
    public function generateHash($save = false)
    {
        if ( $hash = ($this->attributes['hash'] ?? null) ) {
            return $hash;
        }

        $max = (int)($this->getFieldParam('hash', 'max') ?: 32);

        $hash = $this->hash = strtolower(Str::random($max));

        if ( $save === true ) {
            $this->save();
        }

        return $hash;
    }

    /**
     * Generate hash with expiration period.
     * Default hash is valid only for 1 year.
     *
     * @param  mixed $expirationSeconds
     * @return void
     */
    public function getTimeHash($expirationSeconds = null)
    {
        // Default hash expiration is 1 year
        $expirationSeconds = $expirationSeconds ?: (3600 * 24 * 365);

        $hash = $this->generateHash();

        if (is_numeric($expirationSeconds) && $expirationSeconds > 0) {
            $timeHash = (int)floor(time()/$expirationSeconds);

            $hash = $hash . $timeHash;
        }

        return hash_hmac('sha256', $hash . $timeHash, config('app.key'));
    }
}