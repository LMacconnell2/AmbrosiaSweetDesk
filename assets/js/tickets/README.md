# SweetDesk ticket JavaScript refactor

## Files

- `tickets-state.js`: shared page state.
- `tickets-api.js`: all REST requests and response validation.
- `tickets-renderer.js`: table output, escaping, and pagination output.
- `tickets-filters.js`: query-builder, search, sorting, and request parameters.
- `tickets-modal.js`: create/edit/delete modal behavior and ticket payloads.
- `tickets-transfer.js`: JSON import and export.
- `tickets-list.js`: page bootstrap and list loading.
- `enqueue-example.php`: required WordPress script order.

No changes to `tickets-list.php` are required.

The files use `window.SweetDeskTickets` as a shared namespace. This avoids global function collisions while remaining compatible with ordinary WordPress `wp_enqueue_script()` usage.
