<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    //
    protected $table = 'supplier';

    public function get_category_product(){
        $sql = $this->select("supplier.*");
        return $sql;
    }
}
