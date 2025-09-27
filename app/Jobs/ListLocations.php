<?php

namespace App\Jobs;

use App\Models\Location;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ListLocations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private ?User $user = null)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        if($this->user){
            return $this->user->locations;
        }

        return Location::all();
    }
}
