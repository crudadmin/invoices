<?php

namespace Gogol\Invoices\Helpers\Banks\Fio;

use Exception;
use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleClient;
use Gogol\Invoices\Helpers\Banks\BankAccount;

class FioBank extends BankAccount
{
    public function setClient($token)
    {
        $this->client = new GuzzleClient([
            'base_uri' => 'https://fioapi.fio.cz',
        ]);
    }

    public function limitFrom($from)
    {
        if ( $this->syncAllTransactions ) {
            return $from;
        }

        // Limit max 90 days ago, because Fio API has limit for 90 days
        return max($from, now()->subDays(90));
    }

    public function getTransactions($from, $to)
    {
        $from = $this->limitFrom($from);

        $url = 'https://fioapi.fio.cz/v1/rest/periods/'.$this->account->token.'/'.$from->format('Y-m-d').'/'.$to->format('Y-m-d').'/transactions.json';

        try {
            $response = $this->get($url);

            $transactions = $response['accountStatement']['transactionList']['transaction'] ?? [];

            return array_map(function($transaction) {
                return [
                    'id' => $transaction['column22']['value'],
                    'date' => (new Carbon($transaction['column0']['value'])),
                    'vs' => $transaction['column5']['value'] ?? null,
                    'amount' => $transaction['column1']['value'],
                    'currency_code' => $transaction['column14']['value'],
                ];
            }, $transactions);
        } catch (Exception $e) {
            $this->fioBankAutoError($e);
        }
    }

    private function fioBankAutoError($e)
    {
        if ( $e->getCode() == 409 ) {
            throw new Exception(_('Prekročeny limit požiadaviek na API Fio. Skúste znova o 30 sekúnd.'));
        }

        $message = (string)$e->getResponse()->getBody();

        throw new Exception($message ? $message : $e->getMessage());
    }
}