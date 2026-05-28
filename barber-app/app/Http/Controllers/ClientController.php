<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Client;
use App\Models\Membership;
use App\Models\ClientMembership;
use App\Models\Payment;
use Carbon\Carbon;

class ClientController extends Controller
{
    private function checkAuth()
    {
        if (!Auth::check()) abort(403);
    }

    public function index(Request $request)
    {
        $this->checkAuth();

        $query = Client::withCount('appointments')
            ->with('activeMembership.membership');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('phone', 'like', "%$s%")
                  ->orWhere('email', 'like', "%$s%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $clients = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.clients.index', compact('clients'));
    }

    public function store(Request $request)
    {
        $this->checkAuth();

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'phone'     => 'nullable|string|max:30',
            'email'     => 'nullable|email|max:255',
            'birthdate' => 'nullable|date',
            'notes'     => 'nullable|string',
        ]);

        $validated['is_active'] = true;
        Client::create($validated);

        return back()->with('success', 'Cliente creado correctamente.');
    }

    public function show(Client $client)
    {
        $this->checkAuth();

        $client->load([
            'appointments.barber',
            'appointments.service',
            'clientMemberships.membership',
            'payments',
        ]);

        $memberships = Membership::where('is_active', true)->get();

        return view('admin.clients.show', compact('client', 'memberships'));
    }

    public function update(Request $request, Client $client)
    {
        $this->checkAuth();

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'phone'     => 'nullable|string|max:30',
            'email'     => 'nullable|email|max:255',
            'birthdate' => 'nullable|date',
            'notes'     => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $client->update($validated);

        return back()->with('success', 'Cliente actualizado.');
    }

    public function destroy(Client $client)
    {
        $this->checkAuth();
        $client->delete();
        return redirect()->route('admin.clients.index')->with('success', 'Cliente eliminado.');
    }

    public function addMembership(Request $request, Client $client)
    {
        $this->checkAuth();

        $validated = $request->validate([
            'membership_id'  => 'required|exists:memberships,id',
            'start_date'     => 'required|date',
            'payment_method' => 'required|in:efectivo,transferencia,tarjeta,otro',
            'payment_status' => 'required|in:pendiente,pagado',
        ]);

        $membership = Membership::findOrFail($validated['membership_id']);
        $startDate  = Carbon::parse($validated['start_date']);
        $endDate    = $startDate->copy()->addMonth();

        $cm = ClientMembership::create([
            'client_id'         => $client->id,
            'membership_id'     => $membership->id,
            'start_date'        => $startDate->toDateString(),
            'end_date'          => $endDate->toDateString(),
            'payment_date'      => $validated['payment_status'] === 'pagado' ? now()->toDateString() : null,
            'payment_status'    => $validated['payment_status'],
            'payment_method'    => $validated['payment_method'],
            'services_remaining' => is_numeric($membership->visits) ? (int) $membership->visits : 0,
        ]);

        // Registrar pago si está pagado
        if ($validated['payment_status'] === 'pagado') {
            Payment::create([
                'client_id'            => $client->id,
                'client_membership_id' => $cm->id,
                'amount'               => $membership->price,
                'method'               => $validated['payment_method'],
                'type'                 => 'membresia',
                'description'          => "Membresía {$membership->name} - {$client->name}",
                'paid_at'              => now(),
            ]);
        }

        return back()->with('success', 'Membresía asignada correctamente.');
    }
}
