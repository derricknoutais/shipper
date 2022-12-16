<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class InsertPulledProductsToDatabase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Recupere les produits recuperes de Vend stockes dans Redis ()
        $prods = json_decode(Redis::get('pulldb_products'), true);
        // Transforme prods en collection
        $final_prods = collect($prods)->map( function ($item) {
            // Transforme chaque produit en collection pour pouvoir
            $t = collect($item);
            // Trier et Retourner les donnees  ci-dessous
            return $t->only([
                'id', 'source_id', 'source_variant_id', 'variant_parent_id', 'name', 'variant_name', 'handle',
                'sku', 'supplier_code', 'active', 'ecwid_enabled_webstore', 'has_inventory', 'is_composite', 'description',
                'image_url', 'created_at', 'updated_at', 'deleted_at', 'source', 'account_code', 'account_code_purchase',
                'supply_price', 'version'
            ]);
        });
        // Inserer Final Produits dans Database
        foreach(array_chunk($final_prods->toArray(), 100) as $chunk){
            DB::table('products')->insertOrIgnore($final_prods->toArray());
        }

    }
}
