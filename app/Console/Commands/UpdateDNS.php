<?php

namespace App\Console\Commands;

use App\Services\GandiService;
use App\Services\IpService;
use Illuminate\Console\Command;

class UpdateDNS extends Command
{
    protected $signature = 'dns:update';

    protected $description = 'Update DNS';

    public function __construct(private GandiService $gandiService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        return $this->gandiService->updateDns() ? Command::SUCCESS : Command::FAILURE;
    }
}
