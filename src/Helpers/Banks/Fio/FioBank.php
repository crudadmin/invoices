<?php

namespace Gogol\Invoices\Helpers\Banks\Fio;

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

    public function getTransactions($from, $to)
    {
        $url = 'https://fioapi.fio.cz/v1/rest/periods/'.$this->account->token.'/'.$from->format('Y-m-d').'/'.$to->format('Y-m-d').'/transactions.json';

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
    }
}