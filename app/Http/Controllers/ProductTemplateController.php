<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;

class ProductTemplateController extends Controller
{
    public function addProduct(Request $request){
        DB::table('product_template')->insert($request->all());
        return 'OK';
    }
    public function removeProduct(Request $request){
        DB::table('product_template')->where(['product_id' => $request->product_id, 'template_id' => $request->template_id])->delete();
        return 'OK';
    }

}
