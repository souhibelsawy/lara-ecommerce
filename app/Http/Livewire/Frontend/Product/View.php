<?php

namespace App\Http\Livewire\Frontend\Product;

use App\Models\Cart;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class View extends Component
{
    public $category, $product, $prodColorSelectedQuantity, $quantityCount = 1, $productColorId;

    public function addToWishlist($productId)
    {
        if(Auth::check())
        {
            if(Wishlist::where('user_id',auth()->user()->id)->where('product_id',$productId)->exists())
            {
                session()->flash('message','Already added to wishlist');
                $this->dispatchBrowserEvent('message', [
                    'text' => 'Already added to wishlist',
                    'type'=> 'success',
                    'status' => 409
                ]);
                return false;

            }
            else
            {
                Wishlist::create([
                    'user_id' => auth()->user()->id,
                    'product_id' => $productId
                ]);
            $this->emit('wishlistAddedUpdated');
            session()->flash('message','Wishlist Added successfully ');
            $this->dispatchBrowserEvent('message', [
                'text' => 'Wishlist Added successfully',
                'type'=> 'success',
                'status' => 200
                ]);

            }
        }
        else
        {
            session()->flash('message','please Login to continue');
            $this->dispatchBrowserEvent('message', [
                'text' => 'please Login to continue',
                'type'=> 'info',
                'status' => 401
            ]);
            return false;
        }
    }

    public function colorSelected($productColorId)
    {
        // dd($productColorId);
        $this->productColorId = $productColorId;
        $productColor = $this->product->productColors()->where('id',$productColorId)->first();
        $this->prodColorSelectedQuantity = $productColor->quantity;

        if($this->prodColorSelectedQuantity == 0){
             $this->prodColorSelectedQuantity = 'outOfStock';
        }

    }

    public function incrementQuantity()
    {
        if($this->quantityCount < 10){
            $this->quantityCount++;

        }
    }

    public function decrementQuantity()
    {
        if($this->quantityCount > 1){
        $this->quantityCount--;

        }
    }

    public function addToCart(int $productId)
    {
        if(Auth::check())
        {
            // dd( $productId);
            if($this->product->where('id',$productId)->where('status','0')->exists())
            {

                // Check for Product Color  quantity and add to cart
                if($this->product->productColors()->count() > 1)
                {
                    if($this->prodColorSelectedQuantity != NULL)
                    {
                        if(Cart::where('user_id', auth()->user()->id)
                            ->where('product_id',$productId)
                            ->where('product_color_id',$this->productColorId)
                            ->exists())
                        {
                            $this->dispatchBrowserEvent('message', [
                                'text' => 'Product Already Added',
                                'type'=> 'warning',
                                'status' => 200
                            ]);

                        }
                        else
                        {
                            $productColor = $this->product->productColors()->where('id',$this->productColorId)->first();
                            if($productColor->quantity > 0)
                            {
                                    if($productColor->quantity > $this->quantityCount)
                                    {
                                        // Insert Product to Cart
                                        Cart::create([
                                            'user_id' => auth()->user()->id,
                                            'product_id' => $productId,
                                            'user_ color_id' => $this->productColorId,
                                            'quantity' => $this->quantityCount
                                        ]);
                                        $this->emit('CartAddedUpdated');
                                        $this->dispatchBrowserEvent('message', [
                                            'text' => 'Product Added to Cart',
                                            'type'=> 'success',
                                            'status' => 200
                                        ]);
                                    }
                                    else
                                    {
                                        $this->dispatchBrowserEvent('message', [
                                            'text' => 'Only'.$productColor->quantity.'Quantity Available',
                                            'type'=> 'warning',
                                            'status' => 404
                                        ]);

                                    }
                            }else
                            {
                                $this->dispatchBrowserEvent('message', [
                                    'text' => 'Out of Stock',
                                    'type'=> 'warning',
                                    'status' => 404
                                ]);
                            }
                        }

                    }
                    else
                    {
                        $this->dispatchBrowserEvent('message', [
                            'text' => ' Select Your  Product Color',
                            'type'=> 'info',
                            'status' => 404
                        ]);
                    }
                }
                else
                {
                    if(Cart::where('user_id', auth()->id)->where('product_id',$productId)->exists())
                    {
                        $this->dispatchBrowserEvent('message', [
                            'text' => 'Product Already Added',
                            'type'=> 'warning',
                            'status' => 200
                        ]);

                    }
                    else
                    {


                        if($this->product->quantity > 0)
                        {
                            if($this->product->quantity > $this->quantityCount)
                            {
                                // Insert Product to Cart
                                Cart::create([
                                    'user_id' => auth()->user()->id,
                                    'product_id' => $productId,
                                    'quantity' => $this->quantityCount
                                ]);
                                $this->emit('CartAddedUpdated');
                                $this->dispatchBrowserEvent('message', [
                                    'text' => 'Product Added to Cart',
                                    'type'=> 'success',
                                    'status' => 200
                                ]);
                            }
                            else
                            {
                                $this->dispatchBrowserEvent('message', [
                                    'text' => 'Only'.$this->product->quantity.'Quantity Available',
                                    'type'=> 'warning',
                                    'status' => 404
                                ]);
                            }

                        }
                        else
                        {
                            $this->dispatchBrowserEvent('message', [
                                'text' => 'Out of Stock',
                                'type'=> 'warning',
                                'status' => 404
                            ]);
                        }
                    }

                }

            }
            else
            {
                $this->dispatchBrowserEvent('message', [
                    'text' => 'Product Does not exists',
                    'type'=> 'success',
                    'status' => 404
                ]);
            }

        }
        else
        {
                $this->dispatchBrowserEvent('message', [
                    'text' => 'Already added to add to cart',
                    'type'=> 'success',
                    'status' => 409
                ]);
        }
    }

    public function mount($category, $product)
    {
        $this->category = $category;
        $this->product = $product;
    }
    public function render()
    {
        return view('livewire.frontend.product.view', [
            'category' => $this->category,
            'product' => $this->product,
        ]);
    }
}
