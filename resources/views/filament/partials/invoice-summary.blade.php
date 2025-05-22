<div class="bg-white rounded-lg overflow-hidden flex flex-row justify-end">
    @if ($invoice)
    <table class="">
        <tbody class="text-right">
          <tr class="text-right">
            <td>Subtotal:</td>
            <th>{{ \Illuminate\Support\Number::currency($invoice->subtotal_amount)}}</th>
          </tr>
          <tr class="text-right">
            <td>Tax:</td>
            <th>{{ \Illuminate\Support\Number::currency($invoice->tax_amount) }}</th>
          </tr>
          <tr class="text-right">
            <td>Total:</td>
            <th>{{ \Illuminate\Support\Number::currency($invoice->total_amount) }}</th>
          </tr>
        </tbody>
      </table>
    @else
      <span>Subtotal: </span><strong>{{ \Illuminate\Support\Number::currency($subtotal) }}</strong>
    @endif
</div>

