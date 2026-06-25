{{-- "Select all" header cell — the first <th> of a selectable admin table. --}}
<th class="admin-col-select w-px px-3 text-center align-middle font-semibold">
    <input type="checkbox" @change="toggleAll($event)" class="size-3.5 cursor-pointer accent-[var(--color-accent)]" aria-label="{{ __('Select all') }}" />
</th>
