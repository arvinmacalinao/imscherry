<?php

namespace App\Livewire;

use App\Models\Product;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ProductTransactionForm extends Component
{
    public $cart_instance;

    private $product;

    #[Validate('Required')]

    public array $transactionProducts = [];

    #[Validate('required', message: 'Please select products')]
    public Collection $allProducts;

    public function mount($cartInstance): void
    {
        $this->cart_instance = $cartInstance;

        $this->allProducts = Product::all();
    }

    public function render(): View
    {
        $total = 0;

        $cart_items = Cart::instance($this->cart_instance)->content();

        return view('livewire.product-transaction-form', [
            'cart_items' => $cart_items,
        ]);
    }

    public function addProduct(): void
    {
        foreach ($this->transactionProducts as $key => $transactionProduct) {
            if (! $transactionProduct['is_saved']) {
                $this->addError('transactionProducts.'.$key, 'This line must be saved before creating a new one.');
                return;
            }
        }

        $this->transactionProducts[] = [
            'product_id' => '',
            'quantity' => 1,
            'is_saved' => false,
            'product_name' => ''
        ];
    }

    public function editProduct($index): void
    {
        foreach ($this->transactionProducts as $key => $transactionProduct) {
            if (! $transactionProduct['is_saved']) {
                $this->addError('transactionProducts.'.$key, 'This line must be saved before editing another.');

                return;
            }
        }

        $this->transactionProducts[$index]['is_saved'] = false;
    }

    public function saveProduct($index): void
    {
        $this->resetErrorBag();

        $product = $this->allProducts
            ->find($this->transactionProducts[$index]['product_id']);

        $this->transactionProducts[$index]['product_name'] = $product->name;
        $this->transactionProducts[$index]['is_saved'] = true;
        $this->transactionProducts[$index]['stock'] = $product->quantity;


        //
        $cart = Cart::instance($this->cart_instance);

        $exists = $cart->search(function ($cartItem) use ($product) {
            return $cartItem->id === $product['id'];
        });

        // if ($exists->isNotEmpty()) {
        //     session()->flash('message', 'Product exists in the cart!');

        //     return;
        // }

        $cart->add([
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => 0,
            'qty' => $this->transactionProducts[$index]['quantity'], //form field
            'weight' => 1,
            'options' => [
            'code' => $product['code'],
            ],
        ]);
    }

    public function removeProduct($index): void
    {
        unset($this->transactionProducts[$index]);

        $this->transactionProducts = array_values($this->transactionProducts);

        //
        //Cart::instance($this->cart_instance)->remove($index);
    }
}
