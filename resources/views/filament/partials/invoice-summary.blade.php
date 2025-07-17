<div class="bg-white rounded-lg overflow-hidden flex flex-row justify-end">
    @if ($invoice)
    <table class="">
        <tbody class="text-right">
          <tr class="text-right">
            <td>Subtotal:</td>
            <th>{{ \Illuminate\Support\Number::currency(number: $invoice->subtotal_amount, precision: 4)}}</th>
          </tr>
          <tr class="text-right">
            <td>Tax:</td>
            <th>{{ \Illuminate\Support\Number::currency(number: $invoice->tax_amount, precision: 4) }}</th>
          </tr>
          <tr class="text-right">
            <td>Total:</td>
            <th>{{ \Illuminate\Support\Number::currency(number: $invoice->total_amount, precision: 4) }}</th>
          </tr>
        </tbody>
      </table>
    @else
      <span>Subtotal: </span><strong>$ {{ number_format($subtotal, 4) }}</strong>
    @endif
</div>

