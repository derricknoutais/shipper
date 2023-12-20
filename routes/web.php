<?php

// use DB;
use App\Sales;
use App\Handle;
use App\Article;
use App\Demande;
use App\Facture;
use App\Product;
use App\Section;
use App\Commande;
use Carbon\Carbon;
use App\BonCommande;
use App\Consignment;
use App\Fournisseur;
use App\Sectionnable;
use GuzzleHttp\Client;
use App\Jobs\SyncSales;
use App\Events\SectionAdded;
use App\Jobs\UpdateProducts;
use Illuminate\Http\Request;
use App\Jobs\SortAndInsertHandles;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use App\Jobs\InsertPulledProductsToDatabase;
use App\Jobs\PullAndInsertArticlesFromFidbak;
use App\Jobs\PullProductsFromPullDBIntoRedis;

header('Access-Control-Allow-Origin:  *');
header('Access-Control-Allow-Methods:  POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers:  X-CSRF-TOKEN, X-Requested-With, Content-Type, X-Auth-Token, Origin, Authorization');

if (env('APP_ENV') === 'local') {
    Auth::loginUsingId(1);
}

Route::get('/section/{section_id}', function ($section_id) {
    return Sectionnable::where('section_id', $section_id)->count();
});

// Welcome
Route::get('/', function () {
    // PullProductsFromPullDBIntoRedis::dispatch();
    // InsertPulledProductsToDatabase::dispatch();
    return view('accueil');
});
Route::get('/tesst', function () {
    // $job = new SyncSales(Carbon::now()->subDays(1));
    SyncSales::dispatch(
        Carbon::now()
            ->subDays(1)
            ->format('Y-m-dH:i:s'),
    );
    // return 1;
});
Route::get('/update-products', function () {
    PullProductsFromPullDBIntoRedis::dispatch();
    InsertPulledProductsToDatabase::dispatch();
});

Route::get('/test', function () {
    Log::info('***** Starting to handle Handles *****');
    // Inserer les Nouveaux Handles
    return $distinct_handles = DB::table('products')
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
    Log::info('***** Done Handling Handles *****');
});

Route::middleware(['auth'])->group(function () {
    Route::resource('/product', 'ProductController');

    // Handles
    Route::resource('/handle', 'HandleController');

    // Templates
    Route::resource('/template', 'TemplateController');
    Route::get('/template/type/{type}', 'TemplateController@type');
    Route::post('/product-template', 'ProductTemplateController@addProduct');
    Route::post('/product-template/delete', 'ProductTemplateController@removeProduct');
    Route::put('product-template', function (Request $request) {
        foreach ($request->all() as $prodTemp) {
            DB::table('product_template')
                ->where(['template_id' => $prodTemp['template_id'], 'product_id' => $prodTemp['product_id']])
                ->update([
                    'quantite' => $prodTemp['quantite'],
                ]);
        }
        return $request->all();
    });
    // Commandes
    Route::resource('/commande', 'CommandeController');
    Route::get('commandes', 'CommandeController@index');
    Route::post('/product-commande', 'CommandeController@addProduct');
    Route::post('/template-commande', 'CommandeController@addTemplate');
    Route::get('/dernières-commandes/{sectionnable_type}/{selected_article}', function ($sectionnable_type, $selected_article) {
        // return $selected_article;
        if ($sectionnable_type === 'Product') {
            $commande_en_cours = Commande::where('state', '<>', 'Terminé')->pluck('id');
            $sections_en_cours = Section::whereIn('commande_id', $commande_en_cours)->pluck('id');
            $sectionnables = Sectionnable::where('sectionnable_id', $selected_article)
                ->whereIn('section_id', $sections_en_cours)
                ->pluck('id');
            $bc_sectionnable_pluck = DB::table('bon_commande_sectionnable')
                ->whereIn('sectionnable_id', $sectionnables)
                ->pluck('bon_commande_id');
            $bc_sectionnable = DB::table('bon_commande_sectionnable')
                ->whereIn('sectionnable_id', $sectionnables)
                ->get();
            $bon_commande = BonCommande::whereIn('id', $bc_sectionnable_pluck)->get();
            foreach ($bon_commande as $bc) {
                $bc['commande'] = Commande::find($bc->commande_id);
                foreach ($bc_sectionnable as $sec) {
                    if ($sec->bon_commande_id === $bc->id) {
                        $bc['sectionnable'] = $sec;
                    }
                }
            }

            return $bon_commande;
        }
        // return $selected_article;
    });
    Route::post('/ajouter-non-dispo', function (Request $request) {
        $commande = Commande::find($request->commande);
        $sectionnables = Sectionnable::whereIn('section_id', $commande->sections->pluck('id'))
            ->doesntHave('bon_commande')
            ->get();
        $array = [];
        foreach ($sectionnables as $sectionnable) {
            $array[] = [
                'section_id' => $request->section,
                'sectionnable_id' => $sectionnable->sectionnable_id,
                'sectionnable_type' => $sectionnable->sectionnable_type,
                'quantite' => $sectionnable->quantite,
                'conflit' => $sectionnable->conflit,
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }

        DB::table('sectionnables')->insertOrIgnore($array);
    });
    Route::post('/commande/{commande}/update-exchange-rate', function (Request $request, Commande $commande) {
        $commande->update([
            'currency_exchange_rate' => $request['rate'],
        ]);
    });

    /**
     ************* SECTION ***************
     * Chaque commande a plusieurs sections
     */
    Route::resource('/section', 'SectionController');
    Route::post('/product-section', 'SectionController@addProduct');

    // Sectionnables
    // Supprimer des sectionnables
    Route::delete('/sectionnable/{sectionnable}/{section}', 'SectionController@destroySectionnable');
    Route::patch('/sectionnable', 'SectionController@patchSectionnable');
    Route::get('/section-product/delete/{article}/{section}', function ($article, $section) {
        $var = DB::table('sectionnables')
            ->where(['section_id' => $section, 'sectionnable_id' => $article])
            ->delete();
        return $var;
    });
    Route::get('/prendre-offre-de/{commande_offre}/pour/{this_commande}', function (Commande $commande_offre, Commande $this_commande) {
        $this_commande->loadMissing('sections', 'sections.sectionnables', 'sections.sectionnables.demandes');
        $this_sectionnables = Sectionnable::whereIn('section_id', Section::where('commande_id', $this_commande->id)->pluck('id'))
            ->with('demandes')
            ->get();
        $offre_sectionnables = Sectionnable::whereIn('section_id', Section::where('commande_id', $commande_offre->id)->pluck('id'))
            ->with('demandes')
            ->get();
        $array = [];
        foreach ($this_sectionnables as $sectionnable) {
            foreach ($offre_sectionnables as $sectionnable_correspondant) {
                if ($sectionnable_correspondant['sectionnable_id'] === $sectionnable->sectionnable_id) {
                    foreach ($sectionnable_correspondant->demandes as $demande_trouvee) {
                        foreach ($sectionnable->demandes as $demande_cherchee) {
                            if ($demande_trouvee->fournisseur_id === $demande_cherchee->fournisseur_id) {
                                array_push($array, $demande_cherchee->id);
                                DB::table('demande_sectionnable')
                                    ->where('id', $demande_cherchee->pivot->id)
                                    ->update([
                                        'quantite_offerte' => $sectionnable->quantite,
                                        'offre' => $demande_trouvee->pivot->offre,
                                        'differente_offre' => $demande_trouvee->pivot->differente_offre,
                                        'reference_differente_offre' => $demande_trouvee->pivot->reference_differente_offre,
                                    ]);
                            }
                        }
                    }
                }
            }
        }
        return $array;
    });
    // Prépa-Demande
    Route::post('/demande-sectionnable', 'DemandeController@addSectionnable');
    Route::post('/demande/sectionnable', 'DemandeController@storeSectionnable');
    Route::get('/commande/{commande}/prepa-demande', 'DemandeController@showPrepaDemande');
    Route::patch('/prepa-demande-traduction', 'CommandeController@updateTraduction');

    Route::put('/demande-sectionnable', 'DemandeController@updateSectionnable');
    Route::patch('/demande-sectionnable', 'DemandeController@patchSectionnable');
    Route::put('/demande-sectionnable', 'DemandeController@updateSectionnable');
    Route::patch('/demande-sectionnable-traduction', 'DemandeController@updateTraduction');
    Route::get('/commande/{commande}/dispatch-produits-dans-demandes', 'DemandeController@dispatchSectionnables');

    // Demandes
    Route::resource('/demande', 'DemandeController');
    Route::get('/commande/{commande}/demandes', 'DemandeController@index');
    Route::delete('demande-sectionnable/{id}', 'DemandeController@destroySectionnable');
    Route::post('/import', 'DemandeController@import');
    Route::put('demande/{demande}/update-product', 'DemandeController@updateProduct');
    Route::get('demande/export/{demande}', 'DemandeController@export');

    // Conflits
    Route::get('/commande/{commande}/conflits', 'BonCommandeController@showConflit');
    Route::post('/commande/{commande}/résoudre-conflit', function (Commande $commande, Request $request) {
        // Fonction Résoudre Conflit

        /*
                    Si le bon de commande du fournisseur pour le produit choisi existe,
                    Nous allons simplement inséré le produit en question à l'intérieur
                */

        $traduction = null;
        if ($request['element']['pivot']['traduction']) {
            $traduction = $request['element']['pivot']['traduction'];
        } else {
            $traduction = $request['element']['traduction'];
        }
        if ($bc = BonCommande::where('demande_id', $request['element']['demande_id'])->first()) {
            DB::table('bon_commande_sectionnable')->insert([
                'bon_commande_id' => $bc->id,
                'sectionnable_id' => $request['element']['sectionnable_id'],
                'quantite' => $request['element']['quantite_offerte'],
                'prix_achat' => $request['element']['offre'],
                'traduction' => $traduction,
            ]);
            /* Dans le cas contraire */
        } else {
            $bc = BonCommande::create([
                'commande_id' => $commande->id,
                'nom' => 'Bon Commande ' . Demande::find($request['element']['demande_id'])['nom'],
                'demande_id' => $request['element']['demande_id'],
            ]);
            DB::table('bon_commande_sectionnable')->insert([
                'bon_commande_id' => $bc->id,
                'sectionnable_id' => $request['element']['sectionnable_id'],
                'quantite' => $request['element']['quantite_offerte'],
                'prix_achat' => $request['element']['offre'],
                'traduction' => $traduction,
            ]);
        }

        DB::table('demande_sectionnable')
            ->where('id', $request['element']['id'])
            ->update([
                'checked' => 1,
            ]);

        DB::table('sectionnables')
            ->where('id', $request['element']['sectionnable_id'])
            ->update([
                'conflit' => 0,
            ]);
    });
    Route::get('/erase-conflits', function () {
        $sectionnables = App\Sectionnable::all();
        foreach ($sectionnables as $sectionnable) {
            $sectionnable->update([
                'conflit' => 0,
            ]);
        }
    });

    // Bons de Commandes
    Route::post('/creer-bon-commande', function (Request $request) {
        $bc = BonCommande::create([
            'nom' => 'Bon Commande ' . $request['fournisseur']['nom'],
            'commande_id' => $request['commande_id'],
            'demande_id' => $request['id'],
            'fournisseur_id' => $request['fournisseur']['id'],
            'facture_id' => null,
        ]);
        Demande::find($request['id'])->update([
            'bon_commande_id' => $bc->id,
        ]);
    });
    Route::patch('/transfer-sectionnable-to-bon-commandes', function (Request $request) {
        if (
            $bc = BonCommande::where([
                'demande_id' => $request['dem']['pivot']['demande_id'],
            ])->first()
        ) {
            DB::table('bon_commande_sectionnable')
                ->where('id', $request['sectionnable']['bon_commande'][0]['pivot']['id'])
                ->update([
                    'bon_commande_id' => $bc->id,
                    'quantite' => $request['dem']['pivot']['quantite_offerte'],
                    'prix_achat' => $request['dem']['pivot']['offre'],
                ]);
        } else {
            $bc = BonCommande::create([
                'nom' => $request['dem']['nom'],
                'fournisseur_id' => $request['dem']['fournisseur_id'],
                'commande_id' => $request['dem']['commande_id'],
                'demande_id' => $request['dem']['id'],
            ]);
            DB::table('bon_commande_sectionnable')
                ->where('id', $request['sectionnable']['bon_commande'][0]['pivot']['sectionnable_id'])
                ->first()
                ->update([
                    'bon_commande_id' => $bc->id,
                ]);
        }
    });
    Route::get('/commande/{commande}/bons-commandes', 'BonCommandeController@index');
    Route::get('/commande/{commande}/bons-commandes/{bc}', 'BonCommandeController@show');
    Route::get('/commande/{commande}/générer-bons', 'BonCommandeController@générerBons');
    Route::get('/commande/{commande}/export', 'CommandeController@export');
    Route::get('/bons-commandes/export/{bon_commande}', 'BonCommandeController@export');
    Route::get('/commande/{commande}/export-all-bons-commandes', 'BonCommandeController@exportall');
    Route::post('/bon-commande/sectionnable', 'BonCommandeController@storeSectionnable');
    Route::post('/bon-commande/{bc}/add-sectionnable', 'BonCommandeController@addSectionnable');
    Route::put('/bon-commande/{sectionnable}', 'BonCommandeController@updateSectionnable');
    Route::put('/bon-commande/sectionnables', 'BonCommandeController@updateAllSectionnable');
    Route::delete('/bon-commande/sectionnable/{sectionnable}', 'BonCommandeController@destroySectionnable');
    Route::get('/bon-commande/{bc}/create-invoice', 'BonCommandeController@createInvoice');

    // Factures
    Route::get('/commande/{commande}/factures', 'FactureController@index');
    Route::get('facture/{facture}', 'FactureController@show');
    Route::put('/facture/{sectionnable}', 'FactureController@updateSectionnable');
    Route::delete('/facture/sectionnable/{sectionnable}', 'FactureController@destroySectionnable');
    Route::post('/facture/sectionnable', 'FactureController@storeSectionnable');

    // Fournisseurs
    Route::resource('/fournisseur', 'FournisseurController');
    Route::post('product-fournisseur', function (Request $request) {
        // $product = Product::where('id', $request['product']['id'])->first();
        $found = DB::table('product_fournisseur')
            ->where(['product_id' => $request['product']['id']])
            ->delete();
        foreach ($request['product']['fournisseurs'] as $fournisseur) {
            DB::table('product_fournisseur')->insert([
                'fournisseur_id' => $fournisseur['id'],
                'product_id' => $request['product']['id'],
            ]);
        }
    });
    Route::post('product-fournisseur-bulk', function (Request $request) {
        $data = [];
        foreach ($request['products'] as $product_id) {
            foreach ($request['fournisseurs'] as $fournisseur) {
                $data[] = [
                    'fournisseur_id' => $fournisseur['id'],
                    'product_id' => $product_id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
        }
        DB::table('product_fournisseur')->insertOrIgnore($data);
    });

    Route::get('/remettre-a-zero', function () {
        DB::table('demande_sectionnable')
            ->whereIn('demande_id', [4, 5, 6, 7, 8])
            ->update([
                'checked' => 0,
            ]);
        $bcs = DB::table('bon_commandes')
            ->whereIn('demande_id', [4, 5, 6, 7, 8])
            ->pluck('id');
        DB::table('bon_commande_sectionnable')
            ->whereIn('bon_commande_id', $bcs)
            ->delete();
        DB::table('bon_commandes')
            ->whereIn('demande_id', [4, 5, 6, 7, 8])
            ->delete();
    });

    Route::get('/kd', function () {
        return App\Sectionnable::all();
    });

    Route::get('/reorderpoint-commande', 'CommandeController@addReorderPoint');
    // Route::get('/test','CommandeController@consignment');

    Route::post('/commande-quantité', 'CommandeController@addQuantities');

    Route::get('/t', function () {
        $produits = App\Product::where('handle', 'PlateauDembrayageAisin')->get();
        return view('work', compact('produits'));
    });

    Route::put('article-update', function (Request $request) {
        // return $request->all();
        $art = DB::table('sectionnables')
            ->where('id', $request['article']['id'])
            ->update([
                'quantite' => $request['article']['quantite'],
            ]);
    });

    Route::get('/quantite-vendue/{product}', function ($product) {
        return Sales::where('product_id', $product)->first();
    });

    Route::get('/consignment/{product}', function ($product) {
        return $cons = DB::table('consignment_product')
            ->where('product_id', $product)
            ->sum('count');
    });

    Route::get('/subzero/{product}/{apres?}/{avant?}', function ($product, $apres = null, $avant = null) {
        // $client = new Client();
        // $headers = [
        //     'Authorization' => 'Bearer ' . env('VEND_TOKEN'),
        //     'Accept' => 'application/json',
        // ];
        // $response = $client->request('GET', 'https://stapog.com/api/sub/' . $product . ($apres ? '/' . $apres : null) . ($avant ? '/' . $avant : null));
        // return $data = json_decode((string) $response->getBody(), true);
    });

    // VEND API
    Route::post('/creer-bl', function (Request $request) {
        $client = new Client();
        $headers = [
            'Authorization' => 'Bearer ' . env('VEND_TOKEN'),
            'Accept' => 'application/json',
        ];
        // Créer un nouveau Consignment
        $response = $client->request('POST', 'https://stapog.vendhq.com/api/consignment', [
            'headers' => $headers,
            'body' => json_encode([
                'outlet_id' => '06bf537b-c77f-11e6-ff13-fb602832ccea',
                'name' => $request->nom . ' ' . $request['commande']['name'],
                'type' => 'SUPPLIER',
                'status' => 'OPEN',
            ]),
        ]);
        $data = json_decode((string) $response->getBody(), true);

        // Créer un Produit dans le consignment
        foreach ($request['sectionnables'] as $sect) {
            $response2 = $client->request('POST', 'https://stapog.vendhq.com/api/consignment_product', [
                'headers' => $headers,
                'body' => json_encode([
                    'consignment_id' => $data['id'],
                    'product_id' => $sect['sectionnable_id'],
                    'count' => $sect['pivot']['quantite'],
                    'cost' => $sect['pivot']['prix_achat'],
                ]),
            ]);
        }

        Facture::find($request['id'])->update([
            'bon_livraison_id' => $data['id'],
        ]);
    });
    Route::get('/api/vend/commande/{commande_id}/reorderpoint/{reorderpoint_id}/', function ($commande_id, $reorderpoint_id) {
        $client = new Client();
        $headers = [
            'Authorization' => 'Bearer ' . env('VEND_TOKEN'),
            'Accept' => 'application/json',
        ];
        $response = $client->request('GET', 'https://stapog.vendhq.com/api/2.0/consignments/' . $reorderpoint_id . '/products?page_size=1000', [
            'headers' => $headers,
        ]);
        $data = json_decode((string) $response->getBody(), true);

        $totaux = [];
        $totaux['products'] = sizeof($data['data']);
        if (!($section = Section::where(['nom' => 'Reorder Point', 'commande_id' => $commande_id])->first())) {
            $section = Section::create([
                'nom' => 'Reorder Point',
                'commande_id' => $commande_id,
            ]);
        }
        $totaux['inserted'] = 0;
        $totaux['duplicatas'] = [];
        // Pour chaque produit de la commande
        $sections = Section::where('commande_id', $commande_id)
            ->with('sectionnables')
            ->get();
        // Pour chaque produit du reorder
        foreach ($data['data'] as $product) {
            $found = false;
            // Je vérifie que le produit n'est dans aucune section
            foreach ($sections as $section) {
                foreach ($section->sectionnables as $sectionnable) {
                    if ($sectionnable->sectionnable_id === $product['product_id']) {
                        $found = true;
                    }
                }
            }
            // Vérifier que ca n'existe dans aucune section
            if (!$found) {
                DB::table('sectionnables')->insert([
                    'section_id' => $section->id,
                    'sectionnable_id' => $product['product_id'],
                    'sectionnable_type' => 'App\\Product',
                    'quantite' => $product['count'],
                ]);
                $totaux['inserted'] += 1;
            } else {
                array_push($totaux['duplicatas'], $product);
            }
        }
        return $totaux;
    });
    Route::get('/api/stock/{product}', function (Product $product) {
        return $product->quantity;
        $client = new Client();
        $headers = [
            'Authorization' => 'Bearer ' . env('VEND_TOKEN'),
            'Accept' => 'application/json',
        ];
        $response = $client->request('GET', 'https://stapog.vendhq.com/api/2.0/products/' . $product . '/inventory', [
            'headers' => $headers,
        ]);
        $data = json_decode((string) $response->getBody(), true);
        return $data['data'][0]['inventory_level'];
    });
    Route::get('/vend/update-quantities', function () {
        $client = new Client();
        $headers = [
            'Authorization' => 'Bearer ' . env('VEND_TOKEN'),
            'Accept' => 'application/json',
        ];
        $pages = [];
        for ($j = 1; $j <= 19; $j++) {
            return $response = $client->request('GET', 'https://stapog.vendhq.com/api/products?page_size=200&page=' . $j, ['headers' => $headers]);
            $data = json_decode((string) $response->getBody(), true);
            array_push($pages, $data['products']);
        }
        foreach ($pages as $products) {
            foreach ($products as $product) {
                if (Product::where('id', $product['id'])->first()) {
                    if (isset($product['inventory']) && isset($product['inventory'][0]['count'])) {
                        foreach ($product['inventory'] as $inventory) {
                            if ($inventory['outlet_id'] === '06bf537b-c77f-11e6-ff13-fb602832ccea' && $inventory['count']) {
                                Product::find($product['id'])->update([
                                    'quantity' => ((int) $inventory['count']),
                                ]);
                            }
                        }
                    }
                }
            }
        }
    });
    Route::get('/api/produits', function () {
        $client = new Client();
        $headers = [
            'Authorization' => 'Bearer ' . env('VEND_TOKEN'),
            'Accept' => 'application/json',
        ];
        $pages = [];
        for ($j = 23; $j <= 26; $j++) {
            $response = $client->request('GET', 'https://stapog.vendhq.com/api/products?page_size=200&page=' . $j, ['headers' => $headers]);
            $data = json_decode((string) $response->getBody(), true);
            array_push($pages, $data['products']);
        }
        foreach ($pages as $products) {
            foreach ($products as $product) {
                if (!Product::where('id', $product['id'])->first()) {
                    if (!($handle = Handle::where('name', $product['handle'])->first())) {
                        $handle = Handle::create([
                            'name' => $product['handle'],
                        ]);
                    }
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
                        'variant_option_three_value' => $product['variant_option_three_value'],
                    ]);
                    if (isset($product['inventory']) && isset($product['inventory'][0]['count'])) {
                        Product::find($product['id'])->update([
                            'quantity' => ((int) $product['inventory'][0]['count']),
                        ]);
                    }
                } else {
                    Product::find($product['id'])->update([
                        'variant_option_one_name' => $product['variant_option_one_name'],
                        'variant_option_one_value' => $product['variant_option_one_value'],
                        'variant_option_two_name' => $product['variant_option_two_name'],
                        'variant_option_two_value' => $product['variant_option_two_value'],
                        'variant_option_three_name' => $product['variant_option_three_name'],
                        'variant_option_three_value' => $product['variant_option_three_value'],
                    ]);
                }
            }
        }
    });
    Route::get('/br', function () {
        return 1;
    });
    Route::get('/api/produits/force', function () {
        UpdateProducts::dispatch();
        // $client = new Client();
        // $headers = [
        //     "Authorization" => "Bearer " . env('VEND_TOKEN'),
        //     'Accept'        => 'application/json',
        // ];
        // // return 1;
        // $pages = array();
        // $after = 0;
        // $i = 0;
        // do {
        //     $response = $client->request('GET', 'https://stapog.vendhq.com/api/2.0/products?after=' . $after . '&page_size=10', ['headers' => $headers]);
        //     $data = json_decode((string) $response->getBody(), true);
        //     array_push($pages, $data['data']);
        //     $after = $data['version']['max'];
        //     $i++;
        // } while ( $after && $i < 2) ;

        // $localProducts = Product::all();
        // $productsToInsert = [];

        // foreach ($pages as $products) {
        //     foreach ($products as $product)
        //     {

        //         if (! $localProducts->where('id', $product['id'])->first())
        //         {
        //             array_push($productsToInsert, [
        //                 'id' => $product['id'],
        //                 // 'handle_id' => $handles->where('name', $product['handle'])->first() ? ,
        //                 'name' => $product['name'],
        //                 'sku' => $product['sku'],
        //                 // 'price' => $product['price'],
        //                 'supply_price' => $product['supply_price'],
        //                 'variant_option_one_name' => $product['variant_name'],

        //                 'created_at' => now(),
        //                 'updated_at' => now(),
        //             ]);

        //         }
        //         // else {
        //             // $name = '';
        //             // if($product['variant_option_one_value']){
        //             //     $name .= $product['variant_option_one_value'];
        //             // }
        //             // if($product['variant_option_two_value']){
        //             //     $name .= '/' . $product['variant_option_two_value'];
        //             // }
        //             // if($product['variant_option_three_value']){
        //             //     $name .= '/' . $product['variant_option_three_value'];
        //             // }
        //             // Product::find($product['id'])->update([
        //             //     'name' => $name,
        //             //     'variant_option_one_name' => $product['variant_option_one_name'],
        //             //     'variant_option_one_value' => $product['variant_option_one_value'],
        //             //     'variant_option_two_name' => $product['variant_option_two_name'],
        //             //     'variant_option_two_value' => $product['variant_option_two_value'],
        //             //     'variant_option_three_name' => $product['variant_option_three_name'],
        //             //     'variant_option_three_value' => $product['variant_option_three_value']
        //             // ]) ;
        //         // }
        //     }
        // }

        // DB::table('products')->insert($productsToInsert);
        // return 1;
    });
    Route::get('/api2/produits', function () {
        $client = new Client();
        $headers = [
            'Authorization' => 'Bearer ' . env('VEND_TOKEN'),
            'Accept' => 'application/json',
        ];
        $response = Http::get('https://stapog.vendhq.com/api/products', $headers);
        return $response->body();
        // return $response = $client->request('GET', 'https://stapog.vendhq.com/api/2.0/products', ['headers' => $headers]);
    });
    Route::get('/api/handle', function () {});
    Route::get('/api/sales', function () {
        $client = new Client();
        $headers = [
            'Authorization' => 'Bearer ' . env('VEND_TOKEN'),
            'Accept' => 'application/json',
        ];
        $pages = [];
        for ($j = 1; $j <= 40; $j++) {
            $response = $client->request('GET', 'https://stapog.vendhq.com/api/register_sales?since=2019-04-01T00:00:01&status=CLOSED&status=ONACCOUNT&status=LAYBY&status=ONACCOUNT_CLOSED&status=LAYBY_CLOSED&status=LAYBY&status=AWAITING_DISPATCH&status=AWAITING_PICKUP&status=DISPATCHED_CLOSED&status=PICKED_UP_CLOSED&page=' . $j, ['headers' => $headers]);
            $data = json_decode((string) $response->getBody(), true);
            array_push($pages, $data['register_sales']);
        }
        // return $pages;
        $start_trim1 = new DateTime('2019-04-01');
        $end_trim1 = new DateTime('2019-06-31');
        $start_trim2 = new DateTime('2019-07-01');
        $end_trim2 = new DateTime('2019-09-31');
        $start_trim3 = new DateTime('2019-10-01');
        $end_trim3 = new DateTime('2019-12-31');
        $start_trim4 = new DateTime('2020-01-01');
        $end_trim4 = new DateTime('2020-03-31');
        $start_trim5 = new DateTime('2020-04-01');
        foreach ($pages as $page) {
            for ($i = 0; $i < sizeof($page); $i++) {
                $sale_date = new DateTime($page[$i]['sale_date']);
                if ($sale_date >= $start_trim1 && $page[$i]['status'] !== 'SAVED') {
                    foreach ($page[$i]['register_sale_products'] as $sale_product) {
                        if ($sale = Sales::where('product_id', $sale_product['product_id'])->first()) {
                            $sale->increment('quantite_vendue', $sale_product['quantity']);
                            //
                            if ($sale_date >= $start_trim1 && $sale_date <= $end_trim1) {
                                $sale->increment('Trim1', $sale_product['quantity']);
                            } elseif ($sale_date >= $start_trim2 && $sale_date <= $end_trim2) {
                                $sale->increment('Trim2', $sale_product['quantity']);
                            } elseif ($sale_date >= $start_trim3 && $sale_date <= $end_trim3) {
                                $sale->increment('Trim3', $sale_product['quantity']);
                            } elseif ($sale_date >= $start_trim4 && $sale_date <= $end_trim4) {
                                $sale->increment('Trim4', $sale_product['quantity']);
                            } elseif ($sale_date >= $start_trim5) {
                                $sale->increment('Trim5', $sale_product['quantity']);
                            }
                        } else {
                            $sale = Sales::create([
                                'product_id' => $sale_product['product_id'],
                                'quantite_vendue' => $sale_product['quantity'],
                            ]);

                            if ($sale_date >= $start_trim1 && $sale_date <= $end_trim1) {
                                $sale->increment('Trim1', $sale_product['quantity']);
                            } elseif ($sale_date >= $start_trim2 && $sale_date <= $end_trim2) {
                                $sale->increment('Trim2', $sale_product['quantity']);
                            } elseif ($sale_date >= $start_trim3 && $sale_date <= $end_trim3) {
                                $sale->increment('Trim3', $sale_product['quantity']);
                            } elseif ($sale_date >= $start_trim4 && $sale_date <= $end_trim4) {
                                $sale->increment('Trim4', $sale_product['quantity']);
                            } elseif ($sale_date >= $start_trim5) {
                                $sale->increment('Trim5', $sale_product['quantity']);
                            }
                        }
                    }
                }
            }
        }
    });
    Route::get('/api/stocktake', function () {
        $client = new Client();
        $headers = [
            'Authorization' => 'Bearer ' . env('VEND_TOKEN'),
            'Accept' => 'application/json',
        ];

        $pages = [];

        for ($j = 1; $j <= 1; $j++) {
            $response = $client->request('GET', 'https://stapog.vendhq.com/api/consignment?since=2020-01-01T00:00:01&page_size=200&page=' . $j, ['headers' => $headers]);
            // $response = $client->request('GET', 'https://stapog.vendhq.com/api/consignment_product?product_id=0af7b240-ab71-11e7-eddc-5e6cb15d832a', ['headers' => $headers]);
            $data = json_decode((string) $response->getBody(), true);
        }

        foreach ($data['consignments'] as $cons) {
            if ($cons['type'] == 'SUPPLIER') {
                Consignment::create([
                    'id' => $cons['id'],
                    'name' => $cons['name'],
                    'due_at' => $cons['due_at'],
                    'status' => $cons['status'],
                    'type' => $cons['type'],
                ]);
            }
        }
        return 'Ok';
    });
    Route::get('/api/stocktake/products', function () {
        $client = new Client();
        $headers = [
            'Authorization' => 'Bearer ' . env('VEND_TOKEN'),
            'Accept' => 'application/json',
        ];

        $pages = [];
        $stocktakes = Consignment::where('status', '<>', 'RECEIVED')->get();

        for ($j = 0; $j < sizeof($stocktakes); $j++) {
            $response = $client->request('GET', 'https://stapog.vendhq.com/api/consignment_product?consignment_id=' . $stocktakes[$j]['id'], ['headers' => $headers]);
            // $response = $client->request('GET', 'https://stapog.vendhq.com/api/consignment_product?product_id=0af7b240-ab71-11e7-eddc-5e6cb15d832a', ['headers' => $headers]);
            $data = json_decode((string) $response->getBody(), true);
            array_push($pages, $data['consignment_products']);
        }

        foreach ($pages as $page) {
            foreach ($page as $cons) {
                DB::table('consignment_product')->insert([
                    'consignment_id' => $cons['consignment_id'],
                    'product_id' => $cons['product_id'],
                    'count' => (int) $cons['count'],
                    'cost' => (float) $cons['cost'],
                ]);
            }
        }
        return 'Ok';
    });
    Route::get('/api/products', function () {
        $client = new Client();
        $headers = [
            'Authorization' => 'Bearer ' . env('VEND_TOKEN'),
            'Accept' => 'application/json',
        ];

        $pages = [];

        for ($j = 1; $j <= 5; $j++) {
            $response = $client->request('GET', 'https://stapog.vendhq.com/api/products?page_size=200&page=' . $j, ['headers' => $headers]);
            $data = json_decode((string) $response->getBody(), true);
            array_push($pages, $data['products']);
        }
        $push = [];
        foreach ($pages as $products) {
            foreach ($products as $product) {
                if (!Product::find($product['id'])) {
                    array_push($push, [
                        'id' => $product['id'],
                        'handle' => $product['handle'],
                        'name' => $product['name'],
                        'sku' => $product['sku'],
                        'price' => $product['price'],
                        'supply_price' => $product['supply_price'],
                    ]);
                }
            }
        }
        // return sizeof($push);
        DB::table('products')->insert($push);

        // return $totalProducts;
    });

    Route::post('api/inventory', 'TemplateController@createVend');
    Route::get('/inventory-count', 'TemplateController@inventory');
});

// Products

Auth::routes();
Route::get('/home', 'HomeController@index')->name('home');
