<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesInvoiceRegisterECIHeaderResources extends JsonResource
{
    // public function toArray(Request $request): array
    // {
    //     $data = $this->resource;

    //     return [
    //         'id' => $data['id'] ?? null,
    //         'name' => $data['name'] ?? null,
    //         'partner_id' => $data['partner_id'][0] ?? null,
    //         'invoice_date' => $data['create_date'] ?? null,
    //         'invoice_origin' => $data['invoice_origin'] ?? null,
    //         'reference' => $data['ref'] ?? null,
    //         'invoice_payment_term_id' => $data['invoice_payment_term_id'] ?? null,
    //         'currency_id' => $data['company_currency_id'][0] ?? null,
    //         'company_id' => $data['company_id'][1] ?? null,
    //         'move_type' => $data['move_type'][1] ?? null,
    //         'state' => $data['state'][1] ?? null,
    //         'payment_state' => $data['payment_state'][1] ?? null,
    //         'amount_untaxed' => $data['amount_untaxed'][1] ?? null,
    //         'amount_tax' => $data['amount_tax'][1] ?? null,
    //         'amount_total' => $data['amount_total'][1] ?? null,
    //         'invoice_line_ids' => $data['invoice_line_ids'][1] ?? null,
    //     ];
    // }

    public function toArray(Request $request): array
{
    $data = $this->resource;

    return [
        'id' => $data['id'] ?? null,
        'name' => $data['name'] ?? null,
        // partner_id biasanya array [id, name], jadi [0] sudah benar
        'partner_id' => $data['partner_id'][0] ?? null, 
        'customer_name' => $data['partner_id'][1] ?? null, 
        
        'invoice_date' => $data['invoice_date'] ?? null, // Gunakan invoice_date sesuai yang ditarik di Controller
        'invoice_origin' => $data['invoice_origin'] ?? null,
        'reference' => $data['ref'] ?? null,
        
        // Field ID & Name biasanya [id, name]
        'currency_id' => $data['currency_id'][0] ?? null,
        'currency_name' => $data['currency_id'][1] ?? null,

        // Field Angka (Float) JANGAN pakai [1]
        'amount_untaxed' => $data['amount_untaxed'] ?? 0, 
        'amount_tax' => $data['amount_tax'] ?? 0,         
        'amount_total' => $data['amount_total'] ?? 0,     

        // State dan Payment State di Odoo biasanya String murni, bukan array
        'state' => $data['state'] ?? null,
        'payment_state' => $data['payment_state'] ?? null,

        // Invoice lines adalah array ID [1, 2, 3...]
        'invoice_line_ids' => $data['invoice_line_ids'] ?? [],
    ];
}
}