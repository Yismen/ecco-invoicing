<div class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden flex flex-col items-end justify-end">
    <table>
        <tbody class="text-right mb-4">
            <tr class="text-right">
                {{-- <td>Subtotal:</td> --}}
                <th>${{ number_format($subtotal, 4) }}</th>
            </tr>
        </tbody>
    </table>
    {{-- @if ($invoice)
    <table class="border-t">
        <tbody class="text-right">
        <tr class="text-right">
            <td>Invoice Subtotal:</td>
            <th>{{ \Illuminate\Support\Number::currency(number: $invoice->subtotal_amount, precision: 4)}}</th>
        </tr>
        <tr class="text-right">
            <td>Invoice Tax:</td>
            <th>{{ \Illuminate\Support\Number::currency(number: $invoice->tax_amount, precision: 4) }}</th>
        </tr>
        <tr class="text-right">
            <td>Invoice Total:</td>
            <th>{{ \Illuminate\Support\Number::currency(number: $invoice->total_amount, precision: 4) }}</th>
        </tr>
        </tbody>
    </table>
    @endif --}}
</div>
