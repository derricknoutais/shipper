<?php

namespace App\Exports;

use App\Product;
use App\BonCommande;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class BCommandeExport implements FromView
{
    protected $bcommande_id;

    public function __construct($bcommande_id)
    {
        $this->bcommande_id = $bcommande_id;
    }

    public function view(): View
    {
        $bc = BonCommande::where('id', $this->bcommande_id)
            ->with('sectionnables', 'sectionnables.product', 'sectionnables.product.handle', 'sectionnables.article')
            ->first();
        // $bc->sectionnables = $bc->sectionnables
        //     ->filter(function ($sectionnable) {
        //         return $sectionnable->product === null && $sectionnable->sectionnable_type === 'App\\Product';
        //     })
        //     ->map(function ($sectionnable) {
        //         $sectionnable->product = Product::find($sectionnable->sectionnable_id);
        //         return $sectionnable;
        //     })
        //     ->collect()
        //     ->values();
        return view('exports.bon_commandes', [
            'bonCommande' => $bc,
        ]);
    }
}
