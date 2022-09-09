<?php

namespace App\Exports;

use App\Demande;
use App\Product;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class DemandeExport implements FromView
{
    protected $demande_id;

    public function __construct($demande_id){
        $this->demande_id = $demande_id;
    }

    public function view(): View
    {
        $handles = [];
        $demande = Demande::find($this->demande_id);
        $demande->loadMissing(['sectionnables', 'sectionnables.product', 'sectionnables.product.handle', 'sectionnables.article']);
        // $demande = Demande::where('id', $this->demande_id)->with('sectionnables', 'sectionnables.product', 'sectionnables.product.handle', 'sectionnables.article')->first();
        foreach($demande->sectionnables as $sectionnable){
            if(! isset($sectionnable->product) ){
                $sectionnable->product = Product::where('id', $sectionnable->sectionnable_id)->first();
            }
        }

        return view('exports.demandes',  compact('demande'));
    }
}
