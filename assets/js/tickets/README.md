# SweetDesk ticket-list JavaScript

The ticket-list page is divided into focused browser modules using the shared
`window.SweetDeskTickets` namespace.

## Load order

1. `tickets-state.js`
2. `tickets-api.js`
3. `tickets-lookups.js`
4. `tickets-renderer.js`
5. `tickets-filters.js`
6. `tickets-modal.js`
7. `tickets-transfer.js`
8. `tickets-list.js`

## Lookup caching

`tickets-lookups.js` retrieves active ticket statuses and active ticket custom
fields once per admin-page load. It caches both resolved arrays and in-flight
Promises in `SweetDeskTickets.state.lookups`.

Calling `loadStatuses()`, `loadCustomFields()`, or `loadAll()` again reuses the
cached data. Pass `true` to any loader to explicitly refresh it.

## HTML

No changes to `tickets-list.php` are required. The existing hardcoded status
options are replaced by JavaScript after the lookup request completes, and the
existing `#sd-custom-fields-container` is populated from field definitions.
