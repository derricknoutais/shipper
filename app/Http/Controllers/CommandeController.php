<?php

namespace App\Http\Controllers;

use DB;

use App\Product;
use App\Section;
use App\Commande;
use App\Exports\CommandeExport;
use App\Template;
use App\Reorderpoint;
use App\Sectionnable;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Facades\Excel;


class CommandeController extends Controller
{
    public function index(){
        // return 1;
        // $commandes = Commande::with('sections', 'sections.products', 'sections.articles', 'demandes', 'demandes.sectionnables', 'bonsCommandes', 'bonsCommandes.sectionnables', 'factures')->get();
        $commandes = Commande::with('sections', 'sections.products', 'sections.articles', 'bonsCommandes', 'bonsCommandes.sectionnables',)->get();
        return view('commande.index', compact('commandes'));
    }

    public function show(Commande $commande){

        $commande->loadMissing('products', 'templates', 'templates.products', 'sections', 'sections.sectionnables', 'sections.sectionnables.product', 'sections.sectionnables.article', 'sections.sectionnables.bon_commande',  'sections.products', 'demandes', 'demandes.sectionnables', 'bonsCommandes', 'bonsCommandes.sectionnables', 'factures');


        $sections = Section::where('commande_id', $commande->id)->pluck('id');
        $id_articles = Sectionnable::where(['sectionnable_type' => 'App\Article'])->whereIn('section_id', $sections)->get();


        if($pull = Redis::get('pulldb_products')){
            $products = $pull;
        } else {
            $products = Product::all();
        }

        $templates = Template::with('products')->get();
        $commandes = Commande::all();
        foreach($commande->sections as $section){

        }


        return view('commande.show', compact('commande', 'products','templates', 'id_articles', 'commandes' ));
    }

    public function store(Request $request){
        $commande = Commande::create([
            'name' => $request->name
        ]);

        if($commande)
            return redirect()->back();
    }

    public function updateTraduction(Request $request){
        DB::table('sectionnables')->where('id', $request['pivot']['id'])->update([
            'traduction' => $request['pivot']['traduction']
        ]);
    }

    public function export(Commande $commande)
    {
        return Excel::download(new CommandeExport($commande->id), $commande->name . '.xlsx');
    }

    public function addProduct( Request $request ){
        DB::table('commandables')->insert([
            'commande_id' => $request['commande_id'],
            'commandable_id' => $request['product_id'],
            'commandable_type' => 'App\Product'
        ]);
        return 'OK';
    }

    public function addTemplate( Request $request ){

        $template = Template::find($request->template_id)->with('products')->first();

        foreach ($template->products as $product) {
            DB::table('commande_product')->insert([
                'commande_id' => $request->commande_id,
                'product_id' => $product->id,
                'section' => $template->name
            ]);
        }

        DB::table('commandables')->insert([
            'commande_id' => $request->commande_id,
            'commandable_id' => $request->template_id,
            'commandable_type' => 'App\Template'
        ]);
        return 'OK';
    }

    public function consignment(){
        $client = new Client();
        $headers = [
            "Authorization" => "Bearer CjOC4V9CKof2GyEEdPE0Y_E4t742kylC76bxK7oX",
            'Accept'        => 'application/json',
        ];

        // $response = $client->request('GET', 'https://stapog.vendhq.com/api/2.0/inventory?page_size=4000', ['headers' => $headers]);
        $response = $client->request('GET', 'https://stapog.vendhq.com/api/consignment?page_size=200&since=2019-07-26T00:00:01', ['headers' => $headers]);
        return $data = json_decode((string) $response->getBody(), true);
        $consignments = array();
        $products = array();

        foreach ($data['consignments'] as $consignment) {
            if($consignment['type'] == 'SUPPLIER' && $consignment['status'] == 'OPEN'){
                array_push($consignments, $consignment);
            }
        }
        foreach ($consignments as $consignment) {
            $response = $client->request('GET', 'https://stapog.vendhq.com/api/2.0/consignments/' . $consignment['id'] . '/products', ['headers' => $headers]);
            $data = json_decode((string) $response->getBody(), true);
            array_push($products, $data['data']);
        }

        // return $data['consignments'];

    }

    public function addReorderPoint( Request $request){
        $client = new Client();
        $headers = [
            "Authorization" => "Bearer CjOC4V9CKof2GyEEdPE0Y_E4t742kylC76bxK7oX",
            'Accept'        => 'application/json',
        ];
        // Fetch les inventaires de chaque produit
        $response = $client->request('GET', 'https://stapog.vendhq.com/api/2.0/inventory?page_size=4000', ['headers' => $headers]);
        return $data = json_decode((string) $response->getBody(), true);
        // return sizeof($data['data']);

        //Crée le reorder-point
        $reorderpoint = Reorderpoint::create([
            'commande_id' => 2
        ]);

        $productsToPush = array();
        $weird = array();

        // Fetch les consignments (commandes, les inventaires)

        $response = $client->request('GET', 'https://stapog.vendhq.com/api/consignment?page_size=200&since=2019-07-26T00:00:01', ['headers' => $headers]);
        $data2 = json_decode((string) $response->getBody(), true);
        $consignments = array();
        $productsOfConsignments = array();

        // Envoie les produits commandés dans l'array consignments
        foreach ($data2['consignments'] as $consignment) {
            if($consignment['type'] == 'SUPPLIER' && $consignment['status'] == 'OPEN'){
                array_push($consignments, $consignment);
            }
        }

        // Fetch les produits contenus dans chaque commande
        foreach ($consignments as $consignment) {
            $response = $client->request('GET', 'https://stapog.vendhq.com/api/2.0/consignments/' . $consignment['id'] . '/products?page_size=200', ['headers' => $headers]);
            $cons_prod = json_decode((string) $response->getBody(), true);
            foreach ($cons_prod['data'] as $prod) {
                array_push($productsOfConsignments, $prod);
            }
        }

        // Prends les produits dans la base de données
        $produx = Product::pluck('id')->toArray();

        // Filtre de tous les produits ceux qui sont en dessous ou égal du reorder-point
        $productsToPush = array_filter($data['data'], function($stock){
            if(isset($stock['inventory_level']) && isset($stock['reorder_point'])){
                return $stock['inventory_level'] <= $stock['reorder_point'];
            }
        });

        // return sizeof($productsToPush);

        $ptp = array_column($productsToPush, 'product_id');
        $poc = array_column($productsOfConsignments, 'product_id');

        $lowStock = array_uintersect($ptp, $produx, function($a, $b){
            if ($a === $b) {
                return 0;
            }
            return ($a > $b) ? 1 : -1;
        });
        // return sizeof($lowStock);
        // Low Stock Et les Produits des Commandes


        $final = array();
        $lowStock2 = array();
        foreach($lowStock as $key => $value){
            array_push($lowStock2, $value);
        }
        // return $lowStock2;

        $count = 0;
        $pushMe = [];
        // Compare reorderpoints avec produits commandés
        foreach ($lowStock2 as $stock) {
            $found = false;
            foreach( $poc as $product){
                if($product == $stock){
                    $found = true;
                    break;
                }
            }
            if(! $found && $stock != '06bf537b-c771-11e7-ff13-0d97b30d0d02' && $stock != '06bf537b-c771-11e7-ff13-0d97801203a8'
                && $stock != '06bf537b-c771-11e7-ff13-09b53ed39106' && $stock != '06bf537b-c771-11e6-ff13-fb60295d812c'){
                array_push($pushMe , [
                    'commande_id' => 2,
                    'product_id' => $stock,
                    'section' => 'Reorder Point',
                ]);
            }
        }
        // return sizeof($pushMe);
        $reorderpoint_commandable = DB::table('commandables')->insert([
            'commande_id' => 2,
            'commandable_id' => $reorderpoint->id,
            'commandable_type' => 'App\Reorderpoint'
        ]);

        $product_reorderpoint = DB::table('commande_product')->insert($pushMe);
    }

    public function addQuantities(Request $request)
    {
        // return $request->products;
        // return $request->templates;
        $ids = array();
        $updates = array();
        foreach($request->products as $product){
            if(isset($product['quantity'])){
                DB::table('commande_product')->where('id', $product['pivot']['id'])->update([
                    'quantity' => $product['quantity']
                ]);
            }
        }

        foreach ($request->templates as $template) {
            foreach($template['products'] as $product){
                if(isset($product['quantity'])){
                    DB::table('commande_product')->where('product_id', $product['id'])->update([
                        'quantity' => $product['quantity']
                    ]);
                }
            }
        }
    }

}
