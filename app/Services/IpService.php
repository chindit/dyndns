<?php
declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;

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
        $request = Http::get('http://ip' . ($is6 ? 'v6' : '') . '.chindit.be');
        return str_starts_with($request->header('Content-Type'), 'text/plain') ? $request->body() : '';
    }
}
