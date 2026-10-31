<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Agent;
use App\Models\Call;

class AdminSearchController extends Controller
{
    /**
     * Global Administrative Search Engine
     * Powers the 'Cmd+K' Command Palette.
     */
    public function search(Request $request)
    {
        $query = $request->query('q');

        if (strlen($query) < 2 || strlen($query) > 100) {
            return response()->json([]);
        }

        // Escape LIKE wildcards to prevent unexpected result sets
        $safe = str_replace(['%', '_', '\\'], ['\%', '\_', '\\\\'], $query);

        $results = [];

        // 1. Search Users
        $users = User::where('name', 'like', "%{$safe}%")
            ->orWhere('email', 'like', "%{$safe}%")
            ->take(5)
            ->get();

        foreach ($users as $user) {
            $results[] = [
                'type' => 'User',
                'title' => $user->name,
                'subtitle' => $user->email,
                'url' => route('admin.users.details', $user->id),
                'icon' => 'user'
            ];
        }

        // 2. Search Agents
        $agents = Agent::where('name', 'like', "%{$safe}%")
            ->take(5)
            ->get();

        foreach ($agents as $agent) {
            $results[] = [
                'type' => 'Agent',
                'title' => $agent->name,
                'subtitle' => "Owner: " . ($agent->user->name ?? 'System'),
                'url' => route('admin.agents', ['search' => $agent->name]),
                'icon' => 'bot'
            ];
        }

        // 3. Search Call IDs (Exactish match)
        $calls = Call::where('call_id', 'like', "%{$safe}%")
            ->take(3)
            ->get();

        foreach ($calls as $call) {
            $results[] = [
                'type' => 'Call',
                'title' => "Call " . substr($call->call_id, 0, 8),
                'subtitle' => "Status: " . ($call->status ?? 'completed'),
                'url' => route('admin.calls', ['search' => $call->call_id]),
                'icon' => 'phone'
            ];
        }

        return response()->json($results);
    }
}
