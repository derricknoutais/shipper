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
    public $timeout = 0;
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
        for ($j = 1; $j <= 25; $j++) {
            $response = $client->request('GET', 'https://stapog.vendhq.com/api/products?page_size=200&page=' . $j, ['headers' => $headers]);
            $data = json_decode((string) $response->getBody(), true);
            array_push($pages, $data['products']);
            Log::info('Finished Rotation ' . $j);
        }
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
                } else {
                    Product::find($product['id'])->update([
                        'variant_option_one_name' => $product['variant_option_one_name'],
                        'variant_option_one_value' => $product['variant_option_one_value'],
                        'variant_option_two_name' => $product['variant_option_two_name'],
                        'variant_option_two_value' => $product['variant_option_two_value'],
                        'variant_option_three_name' => $product['variant_option_three_name'],
                        'variant_option_three_value' => $product['variant_option_three_value']
                    ]);
                }
            }
        }
    }
}
