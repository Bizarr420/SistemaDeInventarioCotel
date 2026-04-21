<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlertController extends Controller
{
    public function index(Request $request): View
    {
        $query = Alert::with('product');

        if ($request->has('status') && $request->status === 'unread') {
            $query->where('is_read', false);
        }

        $alerts = $query->orderByDesc('triggered_at')->paginate(20);

        return view('alerts.index', compact('alerts'));
    }

    public function markAsRead(Request $request, Alert $alert)
    {
        $alert->update(['is_read' => true]);

        return redirect()->back()->with('success', 'Alerta marcada como leída.');
    }
}