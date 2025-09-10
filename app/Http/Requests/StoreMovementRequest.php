<?php
// app/Http/Requests/StoreMovementRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
class StoreMovementRequest extends FormRequest
{
    // app/Http/Requests/StoreMovementRequest.php
    public function rules(): array {
    return [
        'product_id'   => 'required|exists:products,id',
        'warehouse_id' => 'required|exists:warehouses,id',   // Ubicación
        'type'         => 'required|in:in,out',
        'quantity'     => 'required|integer|min:1',
        'note'         => 'nullable|string|max:255',
    ];
    }

}