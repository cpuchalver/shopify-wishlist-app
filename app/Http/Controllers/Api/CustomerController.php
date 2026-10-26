<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerController extends Controller
{
    /**
     * Display a paginated listing of customers.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $customers = Customer::query()
            ->when(
                $request->query('shop_id'),
                fn (Builder $query, string $shopId) => $query->where('shop_id', $shopId),
            )
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return CustomerResource::collection($customers);
    }

    /**
     * Store a newly created customer.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shop_id' => ['required', 'uuid', 'exists:shops,id'],
            'shopify_customer_id' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'accepts_marketing' => ['boolean'],
        ]);

        $customer = Customer::create($validated);

        return CustomerResource::make($customer)
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer): CustomerResource
    {
        return CustomerResource::make($customer);
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, Customer $customer): CustomerResource
    {
        $validated = $request->validate([
            'email' => ['sometimes', 'required', 'email', 'max:255'],
            'first_name' => ['sometimes', 'required', 'string', 'max:255'],
            'last_name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:255'],
            'accepts_marketing' => ['sometimes', 'boolean'],
        ]);

        $customer->update($validated);

        return CustomerResource::make($customer->refresh());
    }

    /**
     * Remove the specified customer.
     */
    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();

        return response()->json(status: 204);
    }
}
