<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Response;

/**
 * RGPD Article 20 (droit à la portabilité) : permet à l'utilisateur
 * connecté de télécharger ses propres données dans un format structuré.
 */
class ProfileDataExportController extends Controller
{
    public function __invoke(): Response
    {
        $user = auth()->user();

        $data = [
            'profil' => [
                'nom' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'adresse_livraison' => $user->shipping_address,
                'bio' => $user->bio,
                'boutique' => $user->shop_name,
                'membre_depuis' => $user->created_at?->toDateString(),
            ],
            'commandes' => $user->ordersAsBuyer()->with(['items.product', 'payment'])->get()
                ->map(fn ($order) => [
                    'id' => $order->id,
                    'date' => $order->created_at?->toDateString(),
                    'statut' => $order->status,
                    'mode_expedition' => $order->shipping_method,
                    'total' => (float) $order->total,
                    'paiement_statut' => $order->payment?->status,
                    'articles' => $order->items->map(fn ($item) => [
                        'produit' => $item->product?->title,
                        'quantite' => $item->quantity,
                        'prix_unitaire' => (float) $item->unit_price,
                        'statut' => $item->status,
                    ]),
                ]),
            'produits_vendus' => $user->products()->get()
                ->map(fn ($product) => [
                    'titre' => $product->title,
                    'prix' => (float) $product->price,
                    'stock' => $product->stock,
                    'statut' => $product->status,
                    'cree_le' => $product->created_at?->toDateString(),
                ]),
            'avis_laisses' => Review::whereHas('order', fn ($q) => $q->where('buyer_id', $user->id))
                ->with('product')->get()
                ->map(fn ($review) => [
                    'produit' => $review->product?->title,
                    'note' => $review->rating,
                    'commentaire' => $review->comment,
                    'date' => $review->created_at?->toDateString(),
                ]),
            'messages' => $user->sentMessages()->with(['product', 'receiver'])->get()
                ->concat($user->receivedMessages()->with(['product', 'sender'])->get())
                ->sortBy('created_at')
                ->values()
                ->map(fn ($message) => [
                    'produit' => $message->product?->title,
                    'expediteur' => $message->sender?->name,
                    'destinataire' => $message->receiver?->name,
                    'contenu' => $message->content,
                    'date' => $message->created_at?->toDateTimeString(),
                ]),
        ];

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="mes-donnees-marketplace-pop-culture.json"',
        ]);
    }
}
