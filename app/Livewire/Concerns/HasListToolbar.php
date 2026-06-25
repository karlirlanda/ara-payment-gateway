<?php

namespace App\Livewire\Concerns;

use Flux\Flux;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Shared "smart list toolbar" behaviour for admin list pages: row selection,
 * refresh, and CSV export. Pair it with the <x-admin.list-toolbar> component and
 * an `x-data="listTools('<key>')"` page root (which handles Columns + Density
 * client-side). The host component supplies the data-specific pieces through the
 * abstract hooks below.
 */
trait HasListToolbar
{
    /** Selected row ids (drives the bulk-action toolbar). @var array<int, string> */
    public array $selected = [];

    public bool $selectPage = false;

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectPage = false;
    }

    public function updatedSelectPage(bool $value): void
    {
        $this->selected = $value ? $this->toolbarPageIds() : [];
    }

    public function refreshList(): void
    {
        $this->reloadListData();
        $this->clearSelection();
        Flux::toast(text: __('List refreshed'), variant: 'success');
    }

    /** Stream the filtered (or selected) rows as a CSV — a real download of the in-memory demo data. */
    public function export(bool $selectedOnly = false): StreamedResponse
    {
        $rows = $this->toolbarExportRows($selectedOnly);
        $columns = $this->toolbarExportColumns();
        $filename = $this->toolbarExportName().'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rows, $columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);
            foreach ($rows as $row) {
                fputcsv($out, array_map(fn ($key) => data_get($row, $key, ''), $columns));
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Ids on the current page (header select-all). Pages with no row selection
     * can leave the default; pages with selection override it.
     *
     * @return array<int, string>
     */
    protected function toolbarPageIds(): array
    {
        return [];
    }

    /** Re-pull the page's source data (used by Refresh). */
    abstract protected function reloadListData(): void;

    /**
     * The filtered rows to export. When $selectedOnly is true, scope to $this->selected.
     *
     * @return Collection<int, array<string, mixed>>
     */
    abstract protected function toolbarExportRows(bool $selectedOnly): Collection;

    /**
     * Ordered column keys to write to the CSV (also the header row).
     *
     * @return array<int, string>
     */
    abstract protected function toolbarExportColumns(): array;

    /** Base filename for the CSV (no extension / timestamp). */
    abstract protected function toolbarExportName(): string;
}
