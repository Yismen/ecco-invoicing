<div class="w-full bg-white rounded-lg overflow-hidden flex flex-col sm:flex-row content-between justify-between">
    <!-- Company Info -->
    <div class="px-6 py-4">
      <h2 class="text-xl font-semibold text-gray-800 mb-2">
        {{ $invoice?->project->name }}
      </h2>
      <p class="text-gray-600">
        {!! $invoice->project->address !!}
      </p>
    </div>

    <!-- Representative -->
    <div class="px-6 py-4">
      <h3 class="text-gray-00 font-medium">
        Representative
      </h3>
      <p class="text-gray-900 font-bold">
        {{ $invoice->agent->name }}
      </p>
      <p class="text-sm text-gray-500">
        {{-- {{ $invoice->agent->title }} --}}
      </p>
    </div>

    <!-- Details -->
    <div class="px-6 py-4">
      <div>
        <h3 class="text-gray-00 font-medium">
            Invoice #
        </h3>
        <p class="text-gray-900 font-bold">
            {{ $invoice->number }}
        </p>
        <p class="text-sm text-gray-500">
            {{-- {{ $invoice->agent->title }} --}}
        </p>
      </div>
      <div>
        <h3 class="text-gray-00 font-medium">
            Invoice Status
        </h3>
        <p class="text-gray-900 font-bold bg-{{ $invoice->status->getColor() }} rounded p-1">
            {{ str($invoice->status->name)->headline() }}
        </p>
        <p class="text-sm text-gray-500">
            {{-- {{ $invoice->agent->title }} --}}
        </p>
      </div>
    </div>
  </div>

