<?php
declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

final class IpService
{
    private string $ipv4 = '';
    public function getIpv4(): string
    {
        if (!$this->ipv4) {
            $this->ipv4 = $this->getIp();
        }

        return $this->ipv4;
    }

    public function getIpv6(): string
    {
        return $this->getIp(true);
    }

    private function getIp(bool $is6 = false): string
    {
        $processParts = [
            'dig',
            ($is6) ? '-6' : '-4',
            '+short',
            'myip.opendns.com',
            ($is6) ? '' : 'AAAA',
            '@resolver1.opendns.com'
        ];

        $process = new Process($processParts);
        $process->run();

        if (!$process->isSuccessful()) {
            Log::error($process->getErrorOutput());

            return '';
        }

        return trim($process->getOutput());
    }
}
