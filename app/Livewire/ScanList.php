<?php

namespace App\Livewire;

use App\Http\Controllers\ScanController;
use Gloudemans\Shoppingcart\Facades\Cart;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Scan box + list on the Scan to Ship / Return / Cancel pages.
 *
 * Each scan (the scanner sends Enter) is added straight to the list without reloading the page;
 * the list is processed once with Confirm (ScanController@confirm_scans), as before.
 */
class ScanList extends Component
{
    #[Locked]
    public string $type;

    /** Last scan result shown under the box */
    public ?string $message = null;

    public bool $ok = true;

    public function mount(string $type): void
    {
        abort_unless(in_array($type, ['ship', 'return', 'cancelled'], true), 404);

        $this->type = $type;
    }

    public function scan(string $code): void
    {
        [$this->ok, $this->message] = ScanController::addToScanList($this->type, $code);

        // the page beeps (good / bad) and keeps the cursor in the scan box
        $this->dispatch('scan-result', ok: $this->ok);
    }

    public function remove(string $rowId): void
    {
        $item = Cart::instance('order_' . $this->type)->content()->get($rowId);

        if ($item) {
            Cart::instance('order_' . $this->type)->remove($rowId);
            [$this->ok, $this->message] = [true, "Order {$item->name} removed from the list."];
        }
    }

    public function render()
    {
        return view('livewire.scan-list', [
            'cartItems' => Cart::instance('order_' . $this->type)->content(),
        ]);
    }
}
