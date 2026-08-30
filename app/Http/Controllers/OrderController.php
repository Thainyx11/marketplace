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

        // FIX: OrderPolicy::view() only checks that the viewer owns at least
        // one line of the order — it doesn't scope *which* lines they then
        // see. A vendor who sold a single item in a multi-seller order used
        // to see every other vendor's product, price and quantity here too
        // (the view rendered $order->items in full, unfiltered).
        $isBuyerOrAdmin = auth()->id() === $order->buyer_id || auth()->user()->isAdmin();
        $visibleItems = $isBuyerOrAdmin
            ? $order->items
            : $order->items->where('seller_id', auth()->id());

        return view('orders.show', ['order' => $order, 'visibleItems' => $visibleItems, 'isBuyerOrAdmin' => $isBuyerOrAdmin]);
    }

    public function invoice(Order $order)
    {
        Gate::authorize('view', $order);

        // FIX: same leak as show() but worse — the PDF is the buyer's full
        // purchase invoice across every vendor in the order. Restricted to
        // the buyer/admin; a seller has their own payout view instead
        // (vendor.orders), not the buyer's consolidated invoice.
        abort_unless(auth()->id() === $order->buyer_id || auth()->user()->isAdmin(), 403);
        abort_unless($order->payment && $order->payment->status === 'paid', 404);

        $order->load(['items.product', 'buyer', 'payment']);

        $pdf = Pdf::loadView('orders.invoice', ['order' => $order]);

        return $pdf->stream("facture-commande-{$order->id}.pdf");
    }
}
