function resetFilters() {
    // Remove 'tanggal' parameter from URL and redirect
    const url = new URL(window.location);
    url.searchParams.delete('tanggal');
    window.location.href = url.pathname + url.search;
}