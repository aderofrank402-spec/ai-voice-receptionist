<?php

namespace App\Observers;

use App\Models\Call;
use App\Models\DailyAgentMetric;
use Carbon\Carbon;

class CallObserver
{
    /**
     * Handle the Call "created" event.
     * We initialize the count on creation.
     */
    public function created(Call $call): void
    {
        $this->updateMetrics($call);
    }

    /**
     * Handle the Call "updated" event.
     * If duration or outcome changes, we update the totals.
     */
    public function updated(Call $call): void
    {
        // Only update if critical metrics changed
        if ($call->wasChanged(['duration', 'outcome', 'status'])) {
            $this->updateMetrics($call, true);
        }
    }

    /**
     * Internal logic to sync metrics.
     */
    private function updateMetrics(Call $call, $isUpdate = false)
    {
        if (!$call->agent_id) return;

        $date = $call->created_at->format('Y-m-d');
        
        // Success Logic
        $isSuccess = false;
        $outcome = $call->outcome;
        if ($outcome) {
            $successKeywords = ['Handled', 'Booked', 'Confirmed', 'Resolved'];
            foreach ($successKeywords as $keyword) {
                if (stripos($outcome, $keyword) !== false) {
                    $isSuccess = true;
                    break;
                }
            }
        }

        // After Hours Logic (Nairobi Time)
        $ca = $call->created_at->timezone('Africa/Nairobi');
        $isAfterHours = !($ca->dayOfWeek >= 1 && $ca->dayOfWeek <= 6 && $ca->hour >= 7 && $ca->hour < 20);

        // ── Post-Call Lifecycle Automation (Integrations) ──────────
        // Triggered only on first creation or if outcome is newly finalized
        if (!$isUpdate || $call->wasChanged('outcome')) {
            $this->dispatchIntegrations($call, $isSuccess);
        }

        DailyAgentMetric::incrementMetric($call->agent_id, $date, [
            'total_calls' => $isUpdate ? 0 : 1,
            'total_seconds' => $isUpdate ? ($call->duration - ($call->getOriginal('duration') ?? 0)) : $call->duration,
            'success_count' => ($isSuccess && (!$isUpdate || stripos($call->getOriginal('outcome'), 'Handled') === false)) ? 1 : 0,
            'after_hours_count' => $isUpdate ? 0 : ($isAfterHours ? 1 : 0),
        ]);
    }

    /**
     * Dispatch post-call logic across different integration bridges (Asynchronous)
     */
    private function dispatchIntegrations(Call $call, bool $isSuccess)
    {
        // Offload ALL external work to the background queue
        \App\Jobs\DispatchIntegrationJob::dispatch($call->id, $isSuccess);
    }
}
