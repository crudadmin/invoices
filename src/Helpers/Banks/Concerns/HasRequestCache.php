<?php

namespace Gogol\Invoices\Helpers\Banks\Concerns;

use Illuminate\Support\Facades\Cache;

trait HasRequestCache
{
    public function get($path, $params = [])
    {
        return Cache::remember($this->cacheKey($path, $params), now()->addMinutes(30), function () use ($path, $params) {
            return $this->getFresh($path, $params);
        });
    }

    public function getFresh($path, $params = [])
    {
        $response = $this->client->get($path, [
            'query' => $params,
        ]);

        $response = $response->getBody()->getContents();

        return json_decode($response, true);
    }

    public function postFresh($path, $params = [])
    {
        $response = $this->client->post($path, [
            'json' => $params,
        ]);

        $response = $response->getBody()->getContents();

        return json_decode($response, true);
    }

    public function deleteFresh($path, $params = [])
    {
        $response = $this->client->delete($path, [
            'json' => $params,
        ]);

        $response = $response->getBody()->getContents();

        return json_decode($response, true);
    }

    public function cacheKey($path, $params = [])
    {
        return implode('.', [
            get_class($this),
            $this->account->getKey(),
            $path,
            md5(json_encode($params)),
        ]);
    }
}