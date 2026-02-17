<?php

namespace App\Http\Livewire;

use Livewire\Component;

class DeleteConfirmation extends Component
{
    public $actionUrl;
    public $modalVisible = false;

    protected $listeners = ['showDeleteModal' => 'showModal'];

    public function showModal($url)
    {
        $this->actionUrl = $url;
        $this->modalVisible = true;
    }

    public function delete()
    {
        $this->dispatchBrowserEvent('submit-delete-form', ['url' => $this->actionUrl]);
        $this->modalVisible = false;
    }

    public function render()
    {
        return view('livewire.confirm-delete');
    }
}
