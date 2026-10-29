<?php

namespace App\Jobs;

use App\Models\CommandRun;
use App\Services\Admin\ServerCommands;
use Illuminate\Foundation\Bus\Dispatchable;

/** Runs a CommandRun started in the admin panel (dispatched after the response) */
final class RunServerCommand
{
    use Dispatchable;

    public function __construct(private readonly int $runId)
    {
    }

    public function handle(): void
    {
        $run = CommandRun::find($this->runId);
        if ($run?->status === CommandRun::STATUS_RUNNING) {
            ServerCommands::run($run);
        }
    }
}
