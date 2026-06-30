<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        if ($order->user_id != Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403);
        }
        
        $order->load('items');
        return view('orders.show', compact('order'));
    }

    /**
     * Annuler une commande
     */
    public function cancel(Order $order)
    {
        if ($order->user_id != Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403);
        }
        
        if ($order->status !== 'pending') {
            return back()->with('error', 'Cette commande ne peut pas être annulée.');
        }
        
        $order->status = 'cancelled';
        $order->save();
        
        // Restaurer les stocks
        foreach ($order->items as $item) {
            if ($item->product_id) {
                $product = \App\Models\Product::find($item->product_id);
                if ($product) {
                    $product->stock += $item->quantity;
                    $product->save();
                }
            }
        }
        
        return redirect()->route('orders.index')
            ->with('success', 'Commande annulée avec succès.');
    }

    /**
     * Générer la facture PDF
     */
    public function invoice(Order $order)
    {
        if ($order->user_id != Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403);
        }
        
        $order->load('items', 'user');

        // Vérifier si le package est installé
        if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('orders.invoice', compact('order'));
            return $pdf->download('facture_' . $order->order_number . '.pdf');
        }

        // Fallback: Utiliser DomPDF directement
        if (class_exists('\Dompdf\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $html = view('orders.invoice', compact('order'))->render();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            return $dompdf->stream('facture_' . $order->order_number . '.pdf');
        }

        // Si aucun package n'est installé
        return back()->with('error', 'Le module PDF n\'est pas installé. Veuillez contacter l\'administrateur.');
    }
}