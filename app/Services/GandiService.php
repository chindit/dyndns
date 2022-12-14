<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class GandiService
{
    public function __construct(private IpService $ipService)
    {
    }

    public function updateDns(): bool
    {
        if (Cache::get('ipv4') === $this->ipService->getIpv4()) {
            return true;
        }

        return $this->doUpdate();
    }

    private function doUpdate(): bool
    {
        $response =Http::withHeaders(['Authorization' => 'Apikey ' . config('app.gandi.key')])
            ->get(sprintf('%s%s/records/%s', config('app.gandi.url'), config('app.gandi.domain'), config('app.gandi.subdomain')));
        if (!$response->successful()) {
            return false;
        }

        $records = new Collection($response->json());

        $records = $records->map(function (array $record) {
            switch ($record['rrset_type']) {
                case 'A':
                    $ip = $this->ipService->getIpv4();
                    if ($ip) {
                        $record['rrset_values'] = [$ip];
                    }
                    break;
                case 'AAAA':
                    $ip = $this->ipService->getIpv6();
                    if ($ip) {
                        $record['rrset_values'] = [$ip];
                    }
                    break;
                default:
                    break;
            }

            return $record;
        });

        $url = sprintf('%s%s/records/%s', config('app.gandi.url'), config('app.gandi.domain'), config('app.gandi.subdomain'));
        $update = Http::withHeaders(['Authorization' => 'Apikey ' . config('app.gandi.key')])
            ->put($url, ['items' => $records]);

        Log::debug('URL: ' . $url);
        Log::debug('GANDI-BODY: ' . json_encode(['items' => $records]));

        $isSuccess = $update->successful();

        if ($isSuccess) {
            Cache::put('ipv4', $this->ipService->getIpv4());
        } else {
            Log::error($update->body());
        }

        Log::debug($update->body());

        return $isSuccess;
    }
}
