<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TradeIn;
use Illuminate\Http\Request;

class TradeInController extends Controller
{
    public function index(Request $request)
    {
        $orgId = auth()->user()->organization_id;

        $tradeIns = TradeIn::where('organization_id', $orgId)
            ->with(['sale', 'client'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('model', 'like', "%{$search}%")
                        ->orWhere('imei', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('trade-ins.index', compact('tradeIns'));
    }
}
