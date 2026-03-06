<div>
    @if ($showMore)
        <span>{{ $description }}</span>
        <button wire:click="toggleShowMore" class="text-indigo-600 hover:text-indigo-900">Show Less</button>
    @else
        <span>{{ \Illuminate\Support\Str::limit($description, $limit) }}</span>
        @if (strlen($description) > $limit)
            <button wire:click="toggleShowMore" class="text-indigo-600 hover:text-indigo-900">Show More</button>
        @endif
    @endif
</div>