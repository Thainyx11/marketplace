<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #1f2937; }
        h1 { font-size: 20px; margin-bottom: 0; }
        .muted { color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #e5e7eb; }
        th { background-color: #f3f4f6; }
        .text-right { text-align: right; }
        .total-row td { font-weight: bold; border-top: 2px solid #1f2937; border-bottom: none; }
        .header { overflow: hidden; margin-bottom: 20px; }
        .header .right { float: right; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Marketplace Pop Culture</h1>
        <p class="muted">Facture — Commande #{{ $order->id }}</p>

        <div class="right">
            <p><strong>Date</strong> : {{ $order->created_at->format('d/m/Y') }}</p>
            <p><strong>Client</strong> : {{ $order->buyer->name }}<br>{{ $order->buyer->email }}</p>
        </div>
    </div>

    <p><strong>Adresse de livraison</strong><br>{{ $order->shipping_address }}</p>

    <table>
        <thead>
            <tr>
                <th>Article</th>
                <th class="text-right">Quantité</th>
                <th class="text-right">Prix unitaire</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product->title }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2, ',', ' ') }} €</td>
                    <td class="text-right">{{ number_format($item->unit_price * $item->quantity, 2, ',', ' ') }} €</td>
                </tr>
            @endforeach

            @if ($order->discount_amount > 0)
                <tr>
                    <td colspan="3" class="text-right">Remise</td>
                    <td class="text-right">-{{ number_format($order->discount_amount, 2, ',', ' ') }} €</td>
                </tr>
            @endif

            <tr class="total-row">
                <td colspan="3" class="text-right">Total payé</td>
                <td class="text-right">{{ number_format($order->total, 2, ',', ' ') }} €</td>
            </tr>
        </tbody>
    </table>

    <p class="muted" style="margin-top: 30px;">Paiement traité via Stripe — référence {{ $order->payment->stripe_id }}.</p>
</body>
</html>
