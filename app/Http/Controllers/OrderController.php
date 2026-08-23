<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function index()
    {
        $orders = auth()->user()->ordersAsBuyer()
            ->with(['items.product', 'payment'])
            ->latest()
            ->paginate(10);

        return view('orders.index', ['orders' => $orders]);
    }

    public function show(Order $order)
    {
        Gate::authorize('view', $order);

        $order->load(['items.product', 'items.seller', 'payment', 'buyer']);

        return view('orders.show', ['order' => $order]);
    }

    public function invoice(Order $order)
    {
        Gate::authorize('view', $order);

        abort_unless($order->payment && $order->payment->status === 'paid', 404);

        $order->load(['items.product', 'buyer', 'payment']);

        $pdf = Pdf::loadView('orders.invoice', ['order' => $order]);

        return $pdf->stream("facture-commande-{$order->id}.pdf");
    }
}
