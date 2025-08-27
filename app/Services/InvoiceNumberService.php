<?php

namespace App\Services;

use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceNumberService
{
    private const PREFIX = 'INV';
    private const FORMAT = '{prefix}-{yearmonth}-{sequence}';
    
    /**
     * Generate a unique invoice number for the current month
     */
    public function generateInvoiceNumber(): string
    {
        $yearMonth = Carbon::now()->format('Ym');
        $prefix = self::PREFIX;
        
        // Get the last sequence number for this month
        $lastInvoice = Sale::where('invoice_no', 'like', "{$prefix}-{$yearMonth}-%")
            ->orderBy('invoice_no', 'desc')
            ->first();
        
        if ($lastInvoice) {
            // Extract sequence number from last invoice
            $parts = explode('-', $lastInvoice->invoice_no);
            $lastSequence = (int) end($parts);
            $nextSequence = $lastSequence + 1;
        } else {
            $nextSequence = 1;
        }
        
        // Format sequence with leading zeros (4 digits)
        $formattedSequence = str_pad($nextSequence, 4, '0', STR_PAD_LEFT);
        
        return str_replace(
            ['{prefix}', '{yearmonth}', '{sequence}'],
            [$prefix, $yearMonth, $formattedSequence],
            self::FORMAT
        );
    }
    
    /**
     * Validate if an invoice number is in the correct format
     */
    public function isValidFormat(string $invoiceNo): bool
    {
        $pattern = '/^' . self::PREFIX . '-\d{6}-\d{4}$/';
        return preg_match($pattern, $invoiceNo) === 1;
    }
    
    /**
     * Extract year and month from invoice number
     */
    public function extractYearMonth(string $invoiceNo): ?string
    {
        if (!$this->isValidFormat($invoiceNo)) {
            return null;
        }
        
        $parts = explode('-', $invoiceNo);
        $yearMonth = $parts[1];
        
        return Carbon::createFromFormat('Ym', $yearMonth)->format('Y-m');
    }
    
    /**
     * Extract sequence number from invoice number
     */
    public function extractSequence(string $invoiceNo): ?int
    {
        if (!$this->isValidFormat($invoiceNo)) {
            return null;
        }
        
        $parts = explode('-', $invoiceNo);
        return (int) $parts[2];
    }
    
    /**
     * Check if invoice number already exists
     */
    public function exists(string $invoiceNo): bool
    {
        return Sale::where('invoice_no', $invoiceNo)->exists();
    }
}
