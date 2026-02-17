<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\ScanLog;

class ScanLogsTable extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $userId = Auth::id();

        // Fetch logs for the user
        $logs = ScanLog::with(['order', 'toStatus'])
            ->where('user_id', $userId)
            ->when($this->search, function ($query) {
                $query->whereHas('order', function ($q) {
                    $q->where('order_number', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.scan-logs-table', [
            'logs' => $logs,
        ]);
    }
}
