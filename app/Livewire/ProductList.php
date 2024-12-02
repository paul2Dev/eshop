<?php

namespace App\Livewire;

use Livewire\Component;

class ProductList extends Component
{

    public $table = 'products';

    public function render()
    {
        return view('livewire.product-list');
    }
}
