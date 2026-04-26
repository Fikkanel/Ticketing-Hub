<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $customers = Customer::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('unix_id', 'like', "%{$search}%");
            })
            ->withCount('orders')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.customers.index', compact('customers', 'search'));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $customer = Customer::with(['orders' => function($query) {
            $query->orderBy('created_at', 'desc');
        }, 'orders.orderItems.product.event'])->findOrFail($id);

        return view('admin.customers.show', compact('customer'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Optional: Implement customer deletion/ban logic here
        $customer = Customer::findOrFail($id);
        // $customer->delete();
        // return back()->with('success', 'Customer deleted successfully');
        return back()->with('error', 'Fitur hapus belum diaktifkan.');
    }
}
