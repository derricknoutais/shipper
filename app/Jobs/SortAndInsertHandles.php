<?php

namespace App\Jobs;

use App\Handle;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SortAndInsertHandles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Inserer les Nouveaux Handles
        $distinct_handles = DB::table('products')
            ->distinct()
            ->get('handle_name')
            ->map(function ($handle) {
                return $handle->handle_name;
            });
        $handles = Handle::get('name')->map(function ($handle) {
            return $handle->name;
        });
        $diff = array_diff($distinct_handles->toArray(), $handles->toArray());
        foreach ($diff as $handle_name) {
            Handle::create([
                'name' => $handle_name,
            ]);
        }
    }
}
