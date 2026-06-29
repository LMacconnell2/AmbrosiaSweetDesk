const STATUS_CLASS_MAP = {
    open: 'sd-status-open',
    in_progress: 'sd-status-progress',
    pending: 'sd-status-pending',
    closed: 'sd-status-closed'
};

const PRIORITY_CLASS_MAP = {
    urgent: 'sd-priority-urgent',
    high: 'sd-priority-high',
    normal: 'sd-priority-normal',
    low: 'sd-priority-low'
};

function getStatusBadgeClass(status) {
    const key = String(status || '').toLowerCase();

    return STATUS_CLASS_MAP[key] || 'sd-badge-default';
}

function getPriorityBadgeClass(priority) {
    const key = String(priority || '').toLowerCase();

    return PRIORITY_CLASS_MAP[key] || 'sd-badge-default';
}

function formatBadgeLabel(value) {
    return String(value || '—')
        .replace(/_/g, ' ')
        .replace(/\b\w/g, char => char.toUpperCase());
}

function renderBadge(type, value) {
    const colorClass =
        type === 'status'
            ? getStatusBadgeClass(value)
            : getPriorityBadgeClass(value);

    const label = formatBadgeLabel(value);

    return `<span class="sd-badge ${colorClass}">${label}</span>`;
}
