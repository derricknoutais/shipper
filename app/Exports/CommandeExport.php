<?php

namespace App\Exports;

use App\Commande;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class CommandeExport implements FromView
{
    protected $commande_id;

    public function __construct($commande_id){
        $this->commande_id = $commande_id;
    }

    public function view(): View
    {
        return view('exports.commandes', [
            'commande' => Commande::where('id', $this->commande_id)->with( 'sections', 'sections.sectionnables', 'sections.sectionnables.product', 'sections.sectionnables.article')->first()
        ]);
    }
}
