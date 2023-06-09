<?php

namespace App\Jobs;

use App\Handle;
use App\Product;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Log;

class UpdateProducts implements ShouldQueue
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
        $client = new Client();
        $headers = [
            "Authorization" => "Bearer " . env('VEND_TOKEN'),
            'Accept'        => 'application/json',
        ];
        $pages = array();
        $nb_prod = 0;
        for ($j = 16; $j <= 30; $j++) {
            $response = $client->request('GET', 'https://stapog.vendhq.com/api/products?page_size=200&page=' . $j, ['headers' => $headers]);
            $data = json_decode((string) $response->getBody(), true);
            array_push($pages, $data['products']);
            $nb_prod += sizeof($data['products']);
            Log::info('Finished Rotation ' . $j . 'with ' . sizeof($data['products']) . ' products soit ' . $nb_prod . ' au total' );
        }
        $k = 0;
        $nb_pages = sizeof($pages);
        Log::info('Size Of Pages ' . sizeof($pages)  );
        foreach ($pages as $products) {
            foreach ($products as $product) {
                if(!$handle = Handle::where('name' , $product['handle'])->first()){
                    $handle = Handle::create([
                        'name' => $product['handle']
                    ]);
                }
                if (! Product::where('id', $product['id'])->first()) {
                    $prod = Product::create([
                        'id' => $product['id'],
                        'handle_id' => $handle->id,
                        'name' => $product['name'],
                        'sku' => $product['sku'],
                        'price' => $product['price'],
                        'supply_price' => $product['supply_price'],
                        'variant_option_one_name' => $product['variant_option_one_name'],
                        'variant_option_one_value' => $product['variant_option_one_value'],
                        'variant_option_two_name' => $product['variant_option_two_name'],
                        'variant_option_two_value' => $product['variant_option_two_value'],
                        'variant_option_three_name' => $product['variant_option_three_name'],
                        'variant_option_three_value' => $product['variant_option_three_value']
                    ]);
                    if( isset($product['inventory']) && isset($product['inventory'][0]['count'])){
                        Product::find($product['id'])->update([
                            'quantity' => ( (int) $product['inventory'][0]['count'] )
                        ]) ;
                    }
                    if($prod){
                        $k += 1;
                    }
                } else {
                    Product::find($product['id'])->update([
                        'variant_option_one_name' => $product['variant_option_one_name'],
                        'variant_option_one_value' => $product['variant_option_one_value'],
                        'variant_option_two_name' => $product['variant_option_two_name'],
                        'variant_option_two_value' => $product['variant_option_two_value'],
                        'variant_option_three_name' => $product['variant_option_three_name'],
                        'variant_option_three_value' => $product['variant_option_three_value']
                    ]);
                    $k += 1;
                }
            }
            Log::info('Already Inserted ' . $k . ' products');
        }
        Log::info('Already Done with ' . $nb_pages . ' pages');
    }
}
