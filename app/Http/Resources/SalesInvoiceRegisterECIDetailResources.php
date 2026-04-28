<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesInvoiceRegisterECIDetailResources extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->resource;

        return [
            'id' => $data['id'] ?? null,
            'move_id' => $data['move_id'][0] ?? null,
            'move_name' => $data['move_id'][1] ?? null,
            'product_name' => $data['product_id'][1] ?? null,
            'line_label' => $data['name'] ?? null,
            'quantity' => $data['quantity'] ?? 0,
            'unit_price' => $data['price_unit'] ?? 0,
            'discount' => $data['discount'] ?? 0,
            // 'tax_ids' => $data['tax_ids'] ?? [],
            'tax_ids'        => $data['tax_formatted'] ?? [],
            'line_total_excl_tax' => $data['price_subtotal'] ?? 0,
            'line_total_incl_tax' => $data['price_total'] ?? 0,
            //  Penambahan Kategori Produk
            // 'category_id'    => $data['product_category'][0] ?? null,
            // 'category_name'  => $data['product_category'][1] ?? null,
            // 'product_name' => $data['product_id'][1] ?? null,
        ];
    }
}
