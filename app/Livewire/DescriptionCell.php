<?php

namespace App\Livewire;

use Livewire\Component;

class DescriptionCell extends Component
{
    public $description;
    public $limit = 50;
    public $showMore = false;

    public function mount($description)
    {
        $this->description = $description;
    }

    public function toggleShowMore()
    {
        $this->showMore = !$this->showMore;
    }

    public function render()
    {
        return view('livewire.description-cell');
    }
}