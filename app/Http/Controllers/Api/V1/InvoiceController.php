<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\InvoiceStatuses;
use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Get all outstanding invoices (any status except 'Paid').
     *
     * Query Parameters:
     * - client_id: Filter by client ID
     * - campaign_id: Filter by campaign ID
     * - date: Single date or comma-separated date range (YYYY-MM-DD or YYYY-MM-DD,YYYY-MM-DD)
     */
    public function outstanding(Request $request): JsonResponse
    {
        // Check token ability
        if (! $request->user()->tokenCan('read:invoices')) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Insufficient permissions. Token requires read:invoices scope.',
                'count' => 0,
            ], 403);
        }

        try {
            $query = Invoice::with(['project.client', 'agent', 'campaign'])
                ->where('status', '!=', InvoiceStatuses::Paid->value);

            // Filter by client_id
            if ($request->has('client_id')) {
                $clientId = $request->query('client_id');
                $query->whereHas('project', function ($q) use ($clientId) {
                    $q->where('client_id', $clientId);
                });
            }

            // Filter by campaign_id
            if ($request->has('campaign_id')) {
                $campaignId = $request->query('campaign_id');
                $query->where('campaign_id', $campaignId);
            }

            // Filter by date (single date or range)
            if ($request->has('date')) {
                $dateInput = $request->query('date');
                $dates = explode(',', $dateInput);

                if (count($dates) === 1) {
                    // Single date
                    $date = trim($dates[0]);
                    $query->whereDate('date', $date);
                } elseif (count($dates) === 2) {
                    // Date range
                    $startDate = trim($dates[0]);
                    $endDate = trim($dates[1]);
                    $query->whereBetween('date', [$startDate, $endDate]);
                }
            }

            $invoices = $query->get();

            return response()->json([
                'success' => true,
                'data' => InvoiceResource::collection($invoices),
                'message' => 'Outstanding invoices retrieved successfully',
                'count' => $invoices->count(),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'An error occurred while retrieving outstanding invoices: '.$e->getMessage(),
                'count' => 0,
            ], 500);
        }
    }
}
