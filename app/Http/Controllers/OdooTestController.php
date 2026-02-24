<?php

namespace App\Http\Controllers;

use App\Http\Services\OdooService;
use Illuminate\Http\JsonResponse;

class OdooTestController extends Controller
{
    public function __construct(
        protected OdooService $odooService
    ) {}

    public function testConnection(): JsonResponse
    {
        try {
            // We call 'res.partner' which is the Odoo model for Customers/Contacts
            $contacts = $this->odooService->call(
                'res.partner',
                'search_read',
                [[['is_company', '=', true]]], // Filter: Only companies
                ['fields' => ['name', 'email'], 'limit' => 5] // Only these fields
            );

            return response()->json([
                'status' => 'success',
                'data' => $contacts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
