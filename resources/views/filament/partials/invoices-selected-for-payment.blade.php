<div class="flex w-full">
    <table class="table-auto md:table-fixed border-collapse ">
        <thead>
            <th class="py-1 px-3 border border-gray-300">Invoice Number</th>
            <th class="py-1 px-3 border border-gray-300">Amount to pay</th>
        </thead>
        <tbody>
            @foreach ($selectedInvoices as $invoiceNumber => $amount)
                @if ($amount > 0)
                    <tr>
                        <td class="py-1 px-3 border border-gray-300">{{ $invoiceNumber }}</td>
                        <td class="py-1 px-3 border border-gray-300">${{ $amount }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="py-1 px-3 border border-gray-300"><strong>Total:</strong></td>
                <td class="py-1 px-3 border border-gray-300">
                    <strong>
                        ${{
                            collect($selectedInvoices)
                                ->filter(fn ($amount) => $amount > 0)
                                ->sum()
                        }}
                    </strong>
                </td>
            </tr>
        </tfoot>
    </table>
</div>
