<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Ecco Outsourcing Group</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 40px;
      /* background: #f9f9f9; */
      color: #333;
    }
    .invoice-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-direction: row;
      margin-bottom: 20px;
    }
    h1 {
      /* text-align: center;
      margin-bottom: 20px; */
    }
    .status {
      font-size: 1.2em;
      color: #555;
      text-transform: uppercase;
      margin-top: 0;
    }
    .info, .billing, .dates {
      margin-bottom: 20px;
    }
    .box-header {
      font-weight: bold;
      text-transform: uppercase;
      background-color: rgb(191,115,0);
      width: 70%;
      padding: 8px;
      /* border-radius: 5px; */
      display: inline-block;
      margin: 0px;
      color: white;

    }


    .info p, .billing p, .dates p {
      margin: 4px 0;
    }
    span.adderss {
      display: block;
      margin-top: 5px;
    }

    span.address p {
      margin: 0;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 30px;
    }
    table.products th, table.products td {
      border: 1px solid #ddd;
      padding: 8px;
    }
    table.products td {
      text-align: right;
      font-size: 0.9rem;
    }

    table.products tr.table-header > th {
      font-weight: bold;
      text-transform: uppercase;
      background-color: rgb(191,115,0);
      /* width: 70%; */
      /* padding: 8px; */
      /* display: inline-block; */
      margin: 0px;
      color: white;
    }
    table td.description {
      text-align: left;
    }
    table th {
      background-color: #f2f2f2;
    }
    .total {
      text-align: right;
      font-weight: bold;
    }
    .footer {
      font-size: 0.9em;
      color: #777;
      margin-top: 30px;
    }
    .text-right {
      text-align: right;
    }
    .text-center {
      text-align: center;
    }
    .text-blue {
      color: #007bff;
    }
    .text-red {
      color: #dc3545;
    }
    .cool-gray {
      color: #6c757d;
    }
  </style>
</head>
<body>
    @php
        $laravelDailyInvoice = $invoice;
        $invoiceModel = $laravelDailyInvoice->getCustomData()['model'];
    @endphp
  <div class="invoice-box">
    <table>
        <tr>
            <td style="width: 15%; vertical-align: top;">
                <img src="{{ $laravelDailyInvoice->getLogo() }}" alt="Ecco" height="50" style="max-height: 50px; max-width: 200px; margin-right: 10px">
            </td>
            <td>
                <div class="info" style="">
                    <span style="margin-bottom:90px;"><strong >{{ config('app.company.name') }}</strong></span><br>
                    {!! config('app.company.address') !!}<br>
                    {{-- {{ config('app.company.city') }}, {{ config('app.company.state') }} {{ config('app.company.zip') }}<br> --}}
                </div>
            </td>
            <td style="vertical-align: top; text-align: right; width: 30%; margin: 0; padding: 0;">
                <h1 style="vertical-align: top; margin: 0; padding: 0; text-transform: uppercase;">
                    {{ $laravelDailyInvoice->name }}
                </h1>
                <h2 class="status" style="color: {{ $invoiceModel->status->getTextColor() }}; margin: 0; padding: 0;">
                    {{-- {{ $laravelDailyInvoice->status->name }} --}}
                    {{ $invoiceModel->status->getLabel() }}
                </h2>
            </td>
        </tr>
    </table>

    <table>
        <tr>
            <td style="vertical-align: top; width: 50%; margin-right: 20px;">
                <span class="box-header"><strong>Bill To:</strong></span><br>
                {{ $laravelDailyInvoice->buyer->name }}<br>
                <strong>{{ $laravelDailyInvoice->buyer->company }}</strong><br>
                <span class="address">{!! ($laravelDailyInvoice->buyer->address) !!}</span>
            </td>
            <td style="vertical-align: top;" >
                <strong>Invoice #:</strong> {{ $laravelDailyInvoice->series }}</br>
                <strong>Invoice Date:</strong> {{ $invoiceModel->created_at->format('M d, Y') }}</br>
                <strong>{{ $invoiceModel->project->client->template_date_field_name ?? 'File Sent At' }}:</strong> {{ $laravelDailyInvoice->date->format('M d, Y') }}</br>
                <strong>{{  $invoiceModel->project->client->template_project_field_name ?? 'Publication' }}:</strong> {{ $invoiceModel->campaign->name }}</br>
            </td>
        </tr>
    </table>

    <table class="products">
      <thead>
        <tr class="table-header">
          <th>Description</th>
          <th>Qty</th>
          <th>Unit Price</th>
          <th>Amount</th>
        </tr>
      </thead>
      <tbody>
        @foreach($laravelDailyInvoice->items as $item)
        <tr @class([
            'text-red' => $item->sub_total_price < 0,
        ])>
            <td class="pl-0 description">
                {{ $item->title }}

                @if($item->description)
                    <p class="cool-gray">{{ $item->description }}</p>
                @endif
            </td>
            @if($laravelDailyInvoice->hasItemUnits)
                <td class="text-center">{{ $item->units }}</td>
            @endif
            <td class="text-center">{{ $item->quantity }}</td>
            <td class="text-right">
                {{ $laravelDailyInvoice->formatCurrency($item->price_per_unit) }}
            </td>
            @if($laravelDailyInvoice->hasItemDiscount)
                <td class="text-right">
                    {{ $laravelDailyInvoice->formatCurrency($item->discount) }}
                </td>
            @endif
            @if($laravelDailyInvoice->hasItemTax)
                <td class="text-right">
                    {{ $laravelDailyInvoice->formatCurrency($item->tax) }}
                </td>
            @endif

            <td class="text-right pr-0">
                {{ $laravelDailyInvoice->formatCurrency($item->sub_total_price) }}
            </td>
        </tr>
        @endforeach

        {{-- Subtotal --}}
        <tr class="table-footer">
            <th colspan="{{ $laravelDailyInvoice->table_columns - 1 }}" class="text-right pl-0">{{ __('invoices::invoice.total_amount') }}</th>
            <td class="text-right pr-0 total">
                <strong>{{ $laravelDailyInvoice->formatCurrency($laravelDailyInvoice->total_amount) }}</strong>
            </td>
        </tr>
        @if ($invoiceModel->payments->count() > 0)
            <tr>
                <th colspan="{{ $laravelDailyInvoice->table_columns - 1 }}" class="text-right pl-0">{{ __('Amount paid') }}</th>
                <td class="text-right pr-0 text-blue" @class(['text-blue' => $invoiceModel->total_paid > 0])>
                    <strong>{{ $laravelDailyInvoice->formatCurrency($invoiceModel->total_paid) }}</strong>
                </td>
            </tr>
            <tr class="table-footer">">
                <th colspan="{{ $laravelDailyInvoice->table_columns - 1 }}" class="text-right pl-0">{{ __('Amount Pending') }}</th>
                <td class="text-right pr-0" @class(['p-4', 'text-red' => $invoiceModel->balance_pending > 0])>
                    <strong>{{ $laravelDailyInvoice->formatCurrency($invoiceModel->balance_pending) }}</strong>
                </td>
            </tr>

        @endif
      </tbody>
    </table>

    {{-- {{ $invoiceModel->payments }} --}}

    @if ($invoiceModel->payments->count() > 0)
        <h3>Payments</h3>
        <table class="payments">
            <thead>
                <tr>
                    <th>Payment Date</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoiceModel->payments as $payment)
                <tr>
                    <td>{{ $payment->created_at->format('M d, Y') }}</td>
                    <td class="text-right">
                        {{ $laravelDailyInvoice->formatCurrency($payment->amount) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
    <p>Thank you for your business!</p>
    <p>
        <strong>Payment Terms:</strong> Net {{ $invoiceModel->project->invoice_net_days }} Days (By {{ $invoiceModel->due_date->format('M d, Y') }})
    </p>
      <p><strong>Wire Payment Info:</strong><br>
      {{ config('app.company.account.bank') }}<br>
      Routing: {{ config('app.company.account.routing') }} – Account: {{ config('app.company.account.number') }} – Wire: {{ config('app.company.account.wire') }}</p>
    </div>
  </div>

</body>
</html>
