<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Services\OdooService;
use App\Http\Requests\SalesInvoiceHeaderRequestValidationIndex;
use App\Http\Resources\SalesInvoiceRegisterECIHeaderResources;
use App\Http\Resources\SalesInvoiceRegisterECIHeaderResourcesCollection;
use App\Http\Requests\SalesInvoiceDetailRequestValidationIndex;
use App\Http\Resources\SalesInvoiceRegisterECIDetailResources;
use App\Http\Resources\SalesInvoiceRegisterECIDetailResourcesCollection;
use App\Http\Requests\InvoiceSalesRequestValidationIndex;
use App\Http\Resources\salesInvoiceRegisterEciResources;
use App\Http\Resources\salesInvoiceRegisterEciResourcesCollection;

class SalesInvoiceRegisterEci extends Controller
{
 public function __construct(protected OdooService $odoo) {}

    public function AccountMove(SalesInvoiceHeaderRequestValidationIndex $request)
    {
        $validated = $request->validated();

        $limit  = is_numeric($validated['limit'] ?? null) ? (int) $validated['limit'] : 10;
        $offset = is_numeric($validated['offset'] ?? null) ? (int) $validated['offset'] : 0;

        $domain = [
            ['move_type', '=', 'out_invoice']
        ];

        $total = $this->odoo->searchCount('account.move', $domain);
        // $records = $this->odoo->searchRead(
        //     'account.move',
        //     $domain,
        //     [

        //         'name',
        //         'partner_id',
        //         'invoice_date',
        //         'invoice_origin',
        //         'ref',
        //         'invoice_payment_term_id',
        //         'currency_id',
        //         'company_id',
        //         'state',
        //         'payment_state',
        //         'amount_tax',
        //         'amount_total',
        //         'invoice_line_ids',
        //     ],
        //     $limit,
        //     $offset
        // );
        $records = $this->odoo->searchRead(
                'account.move',
                $domain,
                [
                    'name',
                    'partner_id',
                    'invoice_date',
                    'invoice_origin',
                    'ref',
                    'invoice_payment_term_id',
                    'currency_id',
                    'company_id',
                    'state',
                    'payment_state',
                    'amount_untaxed', // TAMBAHKAN INI
                    'amount_tax',     // SUDAH ADA
                    'amount_total',   // SUDAH ADA
                    'invoice_line_ids',
                ],
                $limit,
                $offset
            );
        $message = empty($records)
            ? 'The data you are looking for was not found'
            : 'Success Get Invoice Header Register ECI';

        return ApiResponse::paginate(
            new SalesInvoiceRegisterECIHeaderResourcesCollection(
                $records,
                $total,
                $limit,
                $offset
            ),
            $message
        );
    }


    // public function AccountMoveLine(SalesInvoiceDetailRequestValidationIndex $request)
    // {
    //     $validated = $request->validated();

    //     $limit  = is_numeric($validated['limit'] ?? null) ? (int) $validated['limit'] : 10;
    //     $offset = is_numeric($validated['offset'] ?? null) ? (int) $validated['offset'] : 0;
    //     $moveId = (int) $validated['move_id'];

    //     $domain = [
    //         ['move_id', '=', $moveId],
    //         ['display_type', '=', 'product']
    //     ];

    //     $total = $this->odoo->searchCount('account.move.line', $domain);

    //     $records = $this->odoo->searchRead(
    //         'account.move.line',
    //         $domain,
    //         [
    //             'move_id', 'name', 'product_id', 'quantity', 'price_unit', 
    //             'discount', 'tax_ids', 'price_subtotal', 'price_total'
    //         ],
    //         $limit,
    //         $offset
    //     );

    //     if (!empty($records)) {
    //         // --- PROSES EAGER LOAD UNTUK DETAIL ---

    //         // 1. Ambil Kategori Produk
    //         $productIds = collect($records)->pluck('product_id.0')->unique()->filter()->toArray();
    //         $products = $this->odoo->searchRead('product.product', [['id', 'in', $productIds]], ['id', 'categ_id']);
    //         $productMap = collect($products)->keyBy('id');

    //         // 2. Ambil Nama Pajak (Ubah ID [234] jadi [[234, "Name"]])
    //         $taxIds = collect($records)->pluck('tax_ids')->flatten()->unique()->filter()->toArray();
    //         $taxes = !empty($taxIds) ? $this->odoo->searchRead('account.tax', [['id', 'in', $taxIds]], ['id', 'name']) : [];
    //         $taxMap = collect($taxes)->keyBy('id');

    //         // 3. Mapping data ke dalam records
    //         $records = collect($records)->map(function ($item) use ($productMap, $taxMap) {
    //             $prodId = $item['product_id'][0] ?? null;
                
    //             // Tempel Kategori
    //             $item['product_category'] = $productMap->get($prodId)['categ_id'] ?? null;
                
    //             // Format Pajak
    //             $item['tax_formatted'] = collect($item['tax_ids'] ?? [])->map(function($tId) use ($taxMap) {
    //                 return [
    //                     $tId, 
    //                     $taxMap->get($tId)['name'] ?? 'Unknown Tax'
    //                 ];
    //             })->toArray();
                
    //             return $item;
    //         });
    //     }

    //     $message = empty($records) 
    //         ? 'Invoice details not found' 
    //         : 'Success Get Invoice Register ECI Detail';

    //     return ApiResponse::paginate(
    //         new SalesInvoiceRegisterECIDetailResourcesCollection(
    //             $records, 
    //             $total,
    //             $limit,
    //             $offset
    //         ),
    //         $message
    //     );
    // }


    public function AccountMoveLine(SalesInvoiceDetailRequestValidationIndex $request)
{
    $validated = $request->validated();

    $limit  = is_numeric($validated['limit'] ?? null) ? (int) $validated['limit'] : 10;
    $offset = is_numeric($validated['offset'] ?? null) ? (int) $validated['offset'] : 0;
    $moveId = (int) $validated['move_id'];

    if ($moveId <= 0) {
        return ApiResponse::error('move_id tidak valid', 422);
    }

    $domain = [
        ['move_id', '=', $moveId],
        ['display_type', '=', 'product']
    ];

    $total = $this->odoo->searchCount('account.move.line', $domain);

    $records = $this->odoo->searchRead(
        'account.move.line',
        $domain,
        [
            'move_id',
            'name',
            'product_id',
            'quantity',
            'price_unit',
            'discount',
            'tax_ids',
            'price_subtotal',
            'price_total'
        ],
        $limit,
        $offset
    );

    if (!empty($records)) {

        // Product IDs
        $productIds = collect($records)
            ->pluck('product_id.0')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $products = !empty($productIds)
            ? $this->odoo->searchRead(
                'product.product',
                [['id', 'in', $productIds]],
                ['id', 'categ_id'],
                0,
                0
            )
            : [];

        $productMap = collect($products)->keyBy('id');

        // Tax IDs
        $taxIds = collect($records)
            ->pluck('tax_ids')
            ->flatten()
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $taxes = !empty($taxIds)
            ? $this->odoo->searchRead(
                'account.tax',
                [['id', 'in', $taxIds]],
                ['id', 'name'],
                0,
                0
            )
            : [];

        $taxMap = collect($taxes)->keyBy('id');

        // Mapping
        $records = collect($records)->map(function ($item) use ($productMap, $taxMap) {

            $productId = $item['product_id'][0] ?? null;

            $item['product_category'] = $productId
                ? ($productMap->get($productId)['categ_id'] ?? null)
                : null;

            $item['tax_formatted'] = collect($item['tax_ids'] ?? [])
                ->map(function ($taxId) use ($taxMap) {
                    return [
                        (int) $taxId,
                        $taxMap->get($taxId)['name'] ?? 'Unknown Tax'
                    ];
                })
                ->values()
                ->toArray();

            return $item;
        })->values()->toArray();
    }

    $message = empty($records)
        ? 'Invoice details not found'
        : 'Success Get Invoice Register ECI Detail';

    return ApiResponse::paginate(
        new SalesInvoiceRegisterECIDetailResourcesCollection(
            $records,
            $total,
            $limit,
            $offset
        ),
        $message
    );
}


//     public function CombinedInvoiceSales(InvoiceSalesRequestValidationIndex $request)
// {

//     $validated = $request->validated();
    
//     // 1. Parameter Filter & Pagination
//     $startDate = $request->query('start_date');
//     $endDate   = $request->query('end_date');

//     // LOGIKA PENGUNCI: Jika ada filter tanggal, abaikan semua limit/offset manual.
//     if ($startDate && $endDate) {
//         $limit = 0;      // Ambil semua data dari Odoo
//         $offset = 0;     // Mulai dari awal
//         $isFullData = true;
//     } else {
//         // Jika tidak ada filter tanggal, baru gunakan pagination normal
//         $limit = (int) ($validated['limit'] ?? 10);
//         $offset = (int) ($validated['offset'] ?? 0);
//         $isFullData = false;
//     }

//     // --- SISA KODE KE BAWAH TETAP SAMA ---
//     // 2. Domain Header
//     $domain = [['move_type', '=', 'out_invoice']];
//     if ($startDate && $endDate) {
//         $domain[] = ['invoice_date', '>=', $startDate];
//         $domain[] = ['invoice_date', '<=', $endDate];
//     }

//     // 3. Ambil Total & Header
//     $total = (int) $this->odoo->searchCount('account.move', $domain);
//     $headers = $this->odoo->searchRead(
//         'account.move',
//         $domain,
//         [
//             'id', 'name', 'invoice_partner_display_name', 'partner_id', 'invoice_date',
//             'invoice_origin', 'ref', 'company_currency_id', 'currency_id','invoice_payment_term_id',
//             'amount_untaxed', 'amount_untaxed_signed', 'amount_tax', 'amount_total',
//             'amount_total_in_currency_signed', 'payment_state', 'state', 'move_type', 'invoice_line_ids'
//         ],
//         $limit,
//         $offset
//     );

//     if (empty($headers)) {
//         return ApiResponse::paginate(new salesInvoiceRegisterEciResourcesCollection([], 0, $limit ?: 10, $offset), "Data not found");
//     }

//     // 4. Ambil Detail Lines (CRITICAL: Gunakan Limit 0)
//     $moveIds = collect($headers)->pluck('id')->map(fn($id) => (int)$id)->toArray();
    
//     $allLines = $this->odoo->searchRead(
//         'account.move.line',
//         [
//             ['move_id', 'in', $moveIds],
//             ['display_type', '=', 'product']
//         ],
//         ['move_id', 'name', 'product_id', 'quantity', 'price_unit', 'discount', 'tax_ids', 'price_subtotal', 'price_total'],
//         0, // Limit 0 = Ambil semua baris tanpa kecuali
//         0
//     );

//     // 5. Eager Load Kategori Produk (Limit 0)
//     $productIds = collect($allLines)->pluck('product_id.0')->filter()->unique()->map(fn($id) => (int)$id)->values()->toArray();
//     $productMap = collect();
//     if (!empty($productIds)) {
//         $products = $this->odoo->searchRead('product.product', [['id', 'in', $productIds]], ['id', 'categ_id'], 0, 0);
//         $productMap = collect($products)->keyBy('id');
//     }

//     // 6. Eager Load Nama Pajak (Limit 0)
//     $taxIds = collect($allLines)->pluck('tax_ids')->flatten()->filter()->unique()->map(fn($id) => (int)$id)->values()->toArray();
//     $taxMap = collect();
//     if (!empty($taxIds)) {
//         $taxes = $this->odoo->searchRead('account.tax', [['id', 'in', $taxIds]], ['id', 'name'], 0, 0);
//         $taxMap = collect($taxes)->keyBy('id');
//     }

//     // 7. Mapping Detail Lines
//     $linesMapped = collect($allLines)->map(function ($line) use ($productMap, $taxMap) {
//         $pId = $line['product_id'][0] ?? null;
//         $line['product_category'] = $productMap->get($pId)['categ_id'] ?? null;
        
//         $line['tax_formatted'] = collect($line['tax_ids'] ?? [])->map(fn($tId) => [
//             (int) $tId, 
//             $taxMap->get($tId)['name'] ?? 'Pajak Tidak Diketahui'
//         ])->toArray();
        
//         return $line;
//     });

//     // 8. Grouping & Merging
//     $groupedLines = $linesMapped->groupBy(fn($item) => is_array($item['move_id']) ? $item['move_id'][0] : $item['move_id']);

//     $records = collect($headers)->map(function ($header) use ($groupedLines) {
//         // Kita pastikan id dicocokkan dengan benar
//         $header['lines'] = $groupedLines->get((int)$header['id']) ?? [];
//         return $header;
//     });

//     // 9. Response
//     $collectionLimit = ($limit === 0) ? $total : $limit;
//     return ApiResponse::paginate(
//         new salesInvoiceRegisterEciResourcesCollection($records, $total, $collectionLimit, $offset),
//         "Success Get Sales Invoice Register ECI"
//     );
// }



public function CombinedInvoiceSales(InvoiceSalesRequestValidationIndex $request)
{
    $validated = $request->validated();

    // 1. Parameter Filter & Pagination
    $startDate = $validated['start_date'] ?? null;
    $endDate   = $validated['end_date'] ?? null;

    // Validasi agar start & end harus berpasangan
    if (($startDate && !$endDate) || (!$startDate && $endDate)) {
        return ApiResponse::error(
            "start_date dan end_date harus dikirim bersamaan",
            422
        );
    }

    // Jika ada filter tanggal -> ambil full data
    if ($startDate && $endDate) {
        $limit = 0;
        $offset = 0;
        $isFullData = true;
    } else {
        // Pagination normal
        $limit = (int) ($validated['limit'] ?? 10);
        $offset = (int) ($validated['offset'] ?? 0);
        $isFullData = false;
    }

    // 2. Domain Header
    $domain = [
        ['move_type', '=', 'out_invoice'],
        ['state', '=', 'posted'] // hanya invoice valid/final
    ];

    if ($startDate && $endDate) {
        $domain[] = ['invoice_date', '>=', $startDate];
        $domain[] = ['invoice_date', '<=', $endDate];
    }

    // 3. Ambil Total Header
    $total = (int) $this->odoo->searchCount('account.move', $domain);

    // Ambil Header Invoice
    $headers = $this->odoo->searchRead(
        'account.move',
        $domain,
        [
            'id',
            'name',
            'invoice_partner_display_name',
            'partner_id',
            'invoice_date',
            'invoice_origin',
            'ref',
            'company_currency_id',
            'currency_id',
            'invoice_payment_term_id',
            'amount_untaxed',
            'amount_untaxed_signed',
            'amount_tax',
            'amount_total',
            'amount_total_in_currency_signed',
            'payment_state',
            'state',
            'move_type',
            'invoice_line_ids'
        ],
        $limit,
        $offset
    );

    // Jika kosong
    if (empty($headers)) {
        return ApiResponse::paginate(
            new salesInvoiceRegisterEciResourcesCollection(
                collect([]),
                0,
                $limit ?: 10,
                $offset
            ),
            "Data not found"
        );
    }

    // 4. Ambil Semua Move ID
    $moveIds = collect($headers)
        ->pluck('id')
        ->filter()
        ->map(fn($id) => (int) $id)
        ->values()
        ->toArray();

    // 5. Ambil Semua Detail Line
    $allLines = $this->odoo->searchRead(
        'account.move.line',
        [
            ['move_id', 'in', $moveIds],
            ['display_type', '=', 'product']
        ],
        [
            'id',
            'move_id',
            'name',
            'product_id',
            'quantity',
            'price_unit',
            'discount',
            'tax_ids',
            'price_subtotal',
            'price_total'
        ],
        0,
        0
    );

    // 6. Ambil Product Category
    $productIds = collect($allLines)
        ->pluck('product_id.0')
        ->filter()
        ->unique()
        ->map(fn($id) => (int) $id)
        ->values()
        ->toArray();

    $productMap = collect();

    if (!empty($productIds)) {
        $products = $this->odoo->searchRead(
            'product.product',
            [['id', 'in', $productIds]],
            ['id', 'categ_id'],
            0,
            0
        );

        $productMap = collect($products)->keyBy('id');
    }

    // 7. Ambil Tax Name
    $taxIds = collect($allLines)
        ->pluck('tax_ids')
        ->flatten()
        ->filter()
        ->unique()
        ->map(fn($id) => (int) $id)
        ->values()
        ->toArray();

    $taxMap = collect();

    if (!empty($taxIds)) {
        $taxes = $this->odoo->searchRead(
            'account.tax',
            [['id', 'in', $taxIds]],
            ['id', 'name'],
            0,
            0
        );

        $taxMap = collect($taxes)->keyBy('id');
    }

    // 8. Mapping Lines
    $linesMapped = collect($allLines)->map(function ($line) use ($productMap, $taxMap) {
        // Product Category
        $productId = $line['product_id'][0] ?? null;

        $line['product_category'] = $productId
            ? ($productMap->get($productId)['categ_id'] ?? null)
            : null;

        // Format Tax
        $line['tax_formatted'] = collect($line['tax_ids'] ?? [])
            ->map(function ($taxId) use ($taxMap) {
                return [
                    (int) $taxId,
                    $taxMap->get($taxId)['name'] ?? 'Pajak Tidak Diketahui'
                ];
            })
            ->values()
            ->toArray();

        return $line;
    });

    // 9. Group Lines by Move ID
    $groupedLines = $linesMapped->groupBy(function ($line) {
        return is_array($line['move_id'])
            ? (int) ($line['move_id'][0] ?? 0)
            : (int) $line['move_id'];
    });

    // 10. Merge Header + Lines
    $records = collect($headers)->map(function ($header) use ($groupedLines) {
        $header['lines'] = $groupedLines->get((int) $header['id'], collect([]))->values();
        return $header;
    });

    // 11. Collection Limit
    $collectionLimit = ($limit === 0) ? $total : $limit;

    // 12. Response
    return ApiResponse::paginate(
        new salesInvoiceRegisterEciResourcesCollection(
            $records,
            $total,
            $collectionLimit,
            $offset
        ),
        $isFullData
            ? "Success Get Full Sales Invoice Register ECI By Date Range"
            : "Success Get Sales Invoice Register ECI"
    );
}

}
