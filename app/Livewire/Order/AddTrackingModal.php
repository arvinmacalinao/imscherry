<?php

namespace App\Livewire\Order;

use Livewire\Component;
use App\Models\Order;

class AddTrackingModal extends Component
{
    public $showModal = false;
    public $orderId;
    public $tracking_number;

    protected $rules = [
        'tracking_number' => 'required|string|max:255',
    ];

    protected $listeners = [
        'openTrackingModal'
    ];

    public function openTrackingModal($orderId)
    {
        $this->resetValidation();
        $this->reset(['tracking_number']);

        $this->orderId = $orderId;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $order = Order::findOrFail($this->orderId);

        $order->update([
            'tracking_number' => $this->tracking_number
        ]);

        $this->dispatch('trackingAdded'); // optional event
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.order.add-tracking-modal');
    }
}