<?php

use App\Models\MessageReport;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public function resolve(MessageReport $report): void
    {
        $report->update(['status' => 'resolved']);
    }

    public function with(): array
    {
        return ['reports' => MessageReport::with(['message.sender', 'message.receiver', 'message.product', 'reporter'])->latest()->paginate(15)];
    }
}; ?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-gray-100 mb-6">{{ __('Administration') }}</h1>

    @include('admin._nav')

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm divide-y divide-gray-100 dark:divide-gray-700">
        @forelse ($reports as $report)
            <div class="p-4" wire:key="report-{{ $report->id }}">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('Signalé par') }} {{ $report->reporter->name }} · {{ $report->message->product->title }}
                        </p>
                        <p class="text-gray-900 dark:text-gray-100 mt-1">« {{ $report->message->content }} »</p>
                        <p class="text-xs text-gray-400 mt-1">{{ __('De') }} {{ $report->message->sender->name }} {{ __('à') }} {{ $report->message->receiver->name }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <x-badge :color="$report->status === 'pending' ? 'amber' : 'emerald'">
                            {{ $report->status === 'pending' ? __('En attente') : __('Résolu') }}
                        </x-badge>
                        @if ($report->status === 'pending')
                            <button type="button" wire:click="resolve({{ $report->id }})" class="block mt-2 text-sm font-semibold text-violet-600 dark:text-violet-400 hover:underline">
                                {{ __('Marquer résolu') }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <p class="text-gray-500 dark:text-gray-400 p-8 text-center">{{ __('Aucun signalement.') }}</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $reports->links() }}</div>
</div>
