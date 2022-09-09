<?php

namespace App\Exports;

use App\BonCommande;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class BCommandeExport implements FromView
{
    protected $bcommande_id;

    public function __construct($bcommande_id){
        $this->bcommande_id = $bcommande_id ;
    }

    public function view(): View
    {
        return view('exports.bon_commandes', [
            'bonCommande' => BonCommande::where('id', $this->bcommande_id)->with('sectionnables', 'sectionnables.product', 'sectionnables.article')->first()
        ]);
    }
}
