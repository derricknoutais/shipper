<?php

namespace App\Imports;
use DB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class DemandeImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        foreach( $rows as $row){
            if($row[0]){
                if($row[0] !== 'ID'){
                    if($row[3]){
                        if($row[3]!== 0){
                            if($row[4]){
                                DB::table('demande_sectionnable')->where('id', $row[0])->update([
                                    'offre' => $row[3],
                                    'quantite_offerte' => $row[4]
                                ]);
                            } else {
                                DB::table('demande_sectionnable')->where('id', $row[0])->update([
                                    'offre' => $row[3],
                                    'quantite_offerte' => $row[2]
                                ]);
                            }
                        } else {
                            // DB::table('demande_sectionnable')->where('id', $row[0])->delete();
                        }
                    } else {
                        // DB::table('demande_sectionnable')->where('id', $row[0])->delete();
                    }
                }
            }
        }
    }
}
