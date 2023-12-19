<?php

namespace App\Jobs;

use App\Handle;
use App\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

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
        Log::info('%%%%% Starting to Insert Products %%%%');

        $products = json_decode(Redis::get('pulled_products'), true);
        // return sizeof($products);

        // Transforme prods en collection
        $final_prods = collect($products)->map(function ($item) {
            // Transforme chaque produit en collection pour pouvoir
            $t = collect($item);
            $t['handle_name'] = $t['handle'];
            $variants = json_decode($t['variant_options'], true);
            $variant_keys = ['variant_option_one_value', 'variant_option_two_value', 'variant_option_three_value'];
            for ($i = 0; $i < 3; $i++) {
                if (isset($variants[$i])) {
                    $t[$variant_keys[$i]] = $variants[$i]['value'];
                } else {
                    $t[$variant_keys[$i]] = null;
                }
            }

            // Trier et retourner les donnees ci-dessous
            return $t->only(['id', 'name', 'variant_parent_id', 'variant_name', 'variant_option_one_value', 'variant_option_two_value', 'variant_option_three_value', 'handle_name', 'sku', 'price_including_tax', 'price_excluding_tax', 'active', 'has_inventory', 'is_composite', 'description', 'created_at', 'updated_at', 'deleted_at', 'source', 'supply_price', 'version', 'type', 'is_active']);
            return $t->only(['id', 'source_id', 'source_variant_id', 'variant_parent_id', 'name', 'variant_name', 'variant_options', 'variant_option_one_value', 'variant_option_two_value', 'variant_option_three_value', 'handle', 'sku', 'price_including_tax', 'price_excluding_tax', 'supplier_code', 'active', 'ecwid_enabled_webstore', 'has_inventory', 'is_composite', 'description', 'image_url', 'created_at', 'updated_at', 'deleted_at', 'source', 'account_code', 'account_code_purchase', 'supply_price', 'version', 'type', 'product_category', 'supplier', 'brand', 'categories', 'images', 'skuImages', 'has_variants', 'variant_count', 'button_order', 'loyalty_amount', 'product_codes', 'product_suppliers', 'packaging', 'weight', 'weight_unit', 'length', 'width', 'height', 'dimensions_unit', 'attributes', 'is_active', 'image_thumbnail_url', 'product_type_id', 'supplier_id', 'brand_id', 'tag_ids']);
        });
        // Product::all()->map->delete();
        // Inserer Final Produits dans Database
        // DB::table('products')->upsert($final_prods->toArray(), ['id'], ['id', 'name', 'variant_parent_id', 'variant_name', 'variant_option_one_value', 'variant_option_two_value', 'variant_option_three_value', 'handle_name', 'sku', 'price_including_tax', 'price_excluding_tax', 'active', 'has_inventory', 'is_composite', 'description', 'created_at', 'updated_at', 'deleted_at', 'source', 'supply_price', 'version', 'type', 'is_active']);
        Product::all()->map->delete();
        foreach (array_chunk($final_prods->toArray(), 100) as $data) {
            Log::info(sizeof($data));
            DB::table('products')->insert($data);
            // DB::table('products')->upsert($data, ['id'], ['id', 'name', 'variant_parent_id', 'variant_name', 'variant_option_one_value', 'variant_option_two_value', 'variant_option_three_value', 'handle_name', 'sku', 'price_including_tax', 'price_excluding_tax', 'active', 'has_inventory', 'is_composite', 'description', 'created_at', 'updated_at', 'deleted_at', 'source', 'supply_price', 'version', 'type', 'is_active']);
        }
        Log::info('%%%%% Done Inserting Products %%%%%');
    }
}
