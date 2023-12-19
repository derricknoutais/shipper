<?php

namespace App\Jobs;

use App\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class PullAndInsertArticlesFromFidbak implements ShouldQueue
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
        $articles = Http::timeout(6000)
            ->get(env('FIDBAK_URL') . '/api/articles')
            ->json();
        Redis::set('pulled_articles', json_encode($articles));
        Article::all()->map->delete();
        foreach (array_chunk($articles, 1000) as $data) {
            DB::table('articles')->insert($data);
        }
    }
}
