(() => {
    'use strict';

    const config = window.sweetdeskAnalytics ?? {};

    const state = {
        scope: 'company',
        personId: null,
        requestId: 0,
        isLoading: false,
    };

    document.addEventListener('DOMContentLoaded', initAnalytics);

    async function initAnalytics() {
        if (!validateConfiguration()) {
            return;
        }

        initializeDateRange();
        bindEvents();
        initializeRoleView();

        try {
            await refreshDashboard();
        } catch (error) {
            handleError(error);
        }
    }

    function validateConfiguration() {
        const required = [
            'restRoot',
            'nonce',
        ];

        const missing = required.filter(
            (property) =>
                config[property] === undefined ||
                config[property] === null ||
                config[property] === ''
        );

        if (missing.length > 0) {
            console.error(
                'SweetDesk analytics configuration is incomplete:',
                missing
            );

            showNotice(
                'Analytics could not be initialized because its configuration is incomplete.',
                'error'
            );

            return false;
        }

        return true;
    }

    function bindEvents() {
        document
            .querySelectorAll('[data-view-mode]')
            .forEach((button) => {
                button.addEventListener('click', () => {
                    setViewMode(button.dataset.viewMode);
                });
            });

        getElement('sdUserSelect')?.addEventListener(
            'change',
            async (event) => {
                state.personId =
                    Number.parseInt(event.target.value, 10) || null;

                await refreshDashboard();
            }
        );

        getElement('sdApplyDateRange')?.addEventListener(
            'click',
            refreshDashboard
        );

        getElement('sdDateStart')?.addEventListener(
            'change',
            validateDateRange
        );

        getElement('sdDateEnd')?.addEventListener(
            'change',
            validateDateRange
        );

        getElement('sdMessageCount')?.addEventListener(
            'change',
            loadRecentMessages
        );

        getElement('sdMessageFilter')?.addEventListener(
            'change',
            loadRecentMessages
        );

        getElement('sdExportButton')?.addEventListener(
            'click',
            exportReport
        );
    }

    function initializeRoleView() {
        state.scope = 'company';
        state.personId = null;

        getElement('sdViewToggle')?.setAttribute('hidden', '');
        getElement('sdUserSelector')?.setAttribute('hidden', '');

        toggleViewElements('company');

        const recentMessages = getElement('sdRecentMessages');

        if (recentMessages) {
            recentMessages.hidden = true;
        }
    }

    async function setViewMode(mode, shouldRefresh = true) {
        if (!['company', 'user'].includes(mode)) {
            return;
        }

        state.scope = mode;

        getElement('btnCompanyWide')?.classList.toggle(
            'active',
            mode === 'company'
        );

        getElement('btnSpecificUser')?.classList.toggle(
            'active',
            mode === 'user'
        );

        const userSelector = getElement('sdUserSelector');

        if (mode === 'user') {
            userSelector?.removeAttribute('hidden');

            const selectedPersonId = Number.parseInt(
                getElement('sdUserSelect')?.value,
                10
            );

            state.personId =
                selectedPersonId ||
                Number(config.currentPersonId) ||
                null;
        } else {
            userSelector?.setAttribute('hidden', '');
        }

        toggleViewElements(mode);

        const recentMessages = getElement('sdRecentMessages');

        if (recentMessages) {
            recentMessages.hidden = mode === 'company';
        }

        if (shouldRefresh) {
            await refreshDashboard();
        }
    }

    function toggleViewElements(mode) {
        document
            .querySelectorAll('.sd-view--editor')
            .forEach((element) => {
                element.hidden = mode !== 'user';
            });

        document
            .querySelectorAll('.sd-view--company')
            .forEach((element) => {
                element.hidden = mode !== 'company';
            });
    }

    function initializeDateRange() {
        const today = new Date();
        const oneWeekAgo = new Date(today);

        oneWeekAgo.setDate(today.getDate() - 7);

        setTextInputValue('sdDateStart', formatDateInput(oneWeekAgo));
        setTextInputValue('sdDateEnd', formatDateInput(today));
    }

    function formatDateInput(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    }

    function validateDateRange() {
        const start = getElement('sdDateStart')?.value;
        const end = getElement('sdDateEnd')?.value;

        if (!start || !end) {
            return false;
        }

        if (start > end) {
            showNotice(
                'The beginning date cannot be later than the ending date.',
                'error'
            );

            return false;
        }

        hideNotice();

        return true;
    }

    async function populateUserSelector() {
        const selector = getElement('sdUserSelect');

        if (!selector) {
            return;
        }

        selector.disabled = true;
        selector.innerHTML =
            '<option value="">Loading users...</option>';

        try {
            const response = await apiFetch(
                'people?internal=true&is_active=1&per_page=100'
            );

            const people = Array.isArray(response)
                ? response
                : response.data ?? [];

            selector.innerHTML = '';

            if (people.length === 0) {
                selector.innerHTML =
                    '<option value="">No active users found</option>';

                return;
            }

            people.forEach((person) => {
                const option = document.createElement('option');

                option.value = String(person.id);
                option.textContent =
                    getPersonDisplayName(person);

                if (
                    Number(person.id) ===
                    Number(config.currentPersonId)
                ) {
                    option.textContent += ' (You)';
                }

                selector.append(option);
            });

            const preferredPersonId =
                state.personId || Number(config.currentPersonId);

            const preferredOption = Array.from(
                selector.options
            ).find(
                (option) =>
                    Number(option.value) === preferredPersonId
            );

            if (preferredOption) {
                selector.value = String(preferredPersonId);
                state.personId = preferredPersonId;
            } else if (selector.options.length > 0) {
                selector.selectedIndex = 0;
                state.personId =
                    Number.parseInt(selector.value, 10) || null;
            }
        } finally {
            selector.disabled = false;
        }
    }

    function getPersonDisplayName(person) {
        const fullName = [
            person.first_name,
            person.last_name,
        ]
            .filter(Boolean)
            .join(' ')
            .trim();

        return fullName || person.email || `Person #${person.id}`;
    }

    async function refreshDashboard() {
        if (!validateDateRange()) {
            return;
        }

        const requestId = ++state.requestId;

        setLoading(true);
        hideNotice();

        try {
            const summaryParams = getReportingParams();

            const oldestParams = new URLSearchParams({
                scope: 'company',
                limit: String(config.oldestTicketLimit || 3),
            });

            const [
                summaryResponse,
                oldestResponse,
            ] = await Promise.all([
                apiFetch(
                    `analytics/summary?${summaryParams.toString()}`
                ),
                apiFetch(
                    `analytics/oldest-unresolved?${oldestParams.toString()}`
                ),
            ]);

            if (requestId !== state.requestId) {
                return;
            }

            renderSummary(summaryResponse);
            renderOldestTickets(oldestResponse.data ?? []);
        } catch (error) {
            if (requestId === state.requestId) {
                handleError(error);
            }
        } finally {
            if (requestId === state.requestId) {
                setLoading(false);
            }
        }
    }

    function getReportingParams() {
        return new URLSearchParams({
            scope: 'company',
            date_start: getElement('sdDateStart').value,
            date_end: getElement('sdDateEnd').value,
        });
    }

    async function loadRecentMessages() {
        if (state.scope !== 'user' || !state.personId) {
            return;
        }

        const list = getElement('sdMessageList');

        if (list) {
            list.innerHTML =
                '<p class="sd-empty-state">Loading messages...</p>';
        }

        try {
            const response = await fetchRecentMessages();
            renderMessages(response.data ?? []);
        } catch (error) {
            handleError(error);
        }
    }

    function fetchRecentMessages() {
        const source =
            getElement('sdMessageFilter')?.value || 'all';

        const limit =
            Number.parseInt(
                getElement('sdMessageCount')?.value,
                10
            ) || 5;

        const params = new URLSearchParams({
            person_id: String(state.personId),
            source,
            limit: String(limit),
        });

        return apiFetch(
            `analytics/recent-messages?${params.toString()}`
        );
    }

    function renderSummary(summary) {
        const tickets = summary.tickets ?? {};
        const resolution = summary.resolution_time ?? {};
        const feedback = summary.feedback ?? {};

        setText(
            'sdCompanyReceived',
            formatNumber(tickets.received)
        );

        setText(
            'sdCompanyCleared',
            formatNumber(tickets.cleared)
        );

        setText(
            'sdCompanyAverageReceived',
            formatNumber(
                tickets.average_received_per_member
            )
        );

        setText(
            'sdCompanyAverageCleared',
            formatNumber(
                tickets.average_cleared_per_member
            )
        );

        setText(
            'sdCompanyResolutionAverage',
            formatDuration(resolution.average_seconds)
        );

        setText(
            'sdCompanyResolutionAveragePerMember',
            formatDuration(
                resolution.average_per_member_seconds
            )
        );

        renderResolutionExtreme(
            'sdCompanyResolutionMinimum',
            resolution.minimum
        );

        renderResolutionExtreme(
            'sdCompanyResolutionMaximum',
            resolution.maximum
        );

        setText(
            'sdCompanyFeedbackAverage',
            formatScore(feedback.average)
        );

        setText(
            'sdCompanyFeedbackAveragePerMember',
            formatScore(feedback.average_per_member)
        );

        renderFeedbackExtreme(
            'sdCompanyFeedbackMinimum',
            feedback.minimum
        );

        renderFeedbackExtreme(
            'sdCompanyFeedbackMaximum',
            feedback.maximum
        );
    }

    function renderResolutionExtreme(prefix, extreme) {
        const link = getElement(`${prefix}Link`);
        const ticketElement = getElement(`${prefix}Ticket`);
        const valueElement = getElement(`${prefix}Value`);

        if (!link || !ticketElement || !valueElement) {
            return;
        }

        if (!extreme?.ticket) {
            link.href = '#';
            link.classList.add('is-disabled');
            ticketElement.textContent = 'No resolved tickets';
            valueElement.textContent = '—';
            return;
        }

        link.classList.remove('is-disabled');
        link.href = getTicketUrl(extreme.ticket.id);
        ticketElement.textContent =
            truncateText(extreme.ticket.title, 36);

        valueElement.textContent =
            formatDuration(extreme.seconds);
    }

    function renderFeedbackExtreme(prefix, extreme) {
        const link = getElement(`${prefix}Link`);
        const ticketElement = getElement(`${prefix}Ticket`);
        const valueElement = getElement(`${prefix}Value`);

        if (!link || !ticketElement || !valueElement) {
            return;
        }

        if (!extreme?.ticket) {
            link.href = '#';
            link.classList.add('is-disabled');
            ticketElement.textContent = 'No feedback received';
            valueElement.textContent = '—';
            return;
        }

        link.classList.remove('is-disabled');
        link.href = getTicketUrl(extreme.ticket.id);
        ticketElement.textContent =
            truncateText(extreme.ticket.title, 36);

        valueElement.textContent =
            formatScore(extreme.score);
    }

    function renderOldestTickets(tickets) {
        const list = getElement('sdOldestTicketList');

        if (!list) {
            return;
        }

        if (!Array.isArray(tickets) || tickets.length === 0) {
            list.innerHTML =
                '<p class="sd-empty-state">No unresolved tickets found.</p>';

            return;
        }

        list.innerHTML = tickets
            .map((ticket) => {
                const assignee =
                    state.scope === 'company'
                        ? renderAssignee(ticket.assignee)
                        : '';

                return `
                    <a
                        href="${escapeAttribute(
                            getTicketUrl(ticket.id)
                        )}"
                        class="sd-ticket-item"
                    >
                        <span class="sd-ticket-name">
                            ${escapeHtml(ticket.title)}
                        </span>

                        ${assignee}

                        <span class="sd-ticket-age">
                            ${escapeHtml(
                                formatAge(ticket.age_seconds)
                            )}
                        </span>
                    </a>
                `;
            })
            .join('');
    }

    function renderAssignee(assignee) {
        if (!assignee) {
            return `
                <span class="sd-ticket-assignee">
                    Unassigned
                </span>
            `;
        }

        const name = [
            assignee.first_name,
            assignee.last_name,
        ]
            .filter(Boolean)
            .join(' ')
            .trim();

        return `
            <span class="sd-ticket-assignee">
                ${escapeHtml(name || 'Unknown')}
            </span>
        `;
    }

    function renderMessages(messages) {
        const list = getElement('sdMessageList');

        if (!list) {
            return;
        }

        if (!Array.isArray(messages) || messages.length === 0) {
            list.innerHTML =
                '<p class="sd-empty-state">No messages to display.</p>';

            return;
        }

        list.innerHTML = messages
            .map((message) => {
                const sourceLabel =
                    message.source === 'staff'
                        ? 'Employee'
                        : 'Customer';

                return `
                    <a
                        href="${escapeAttribute(
                            getTicketUrl(message.ticket?.id)
                        )}"
                        class="sd-message-item"
                    >
                        <div class="sd-message-meta">
                            <span>
                                <span class="sd-message-author">
                                    ${escapeHtml(
                                        message.author
                                            ?.display_name ||
                                            'Unknown author'
                                    )}
                                </span>

                                <span
                                    class="sd-message-source-badge
                                        sd-message-source-badge--${escapeAttribute(
                                            message.source
                                        )}"
                                >
                                    ${sourceLabel}
                                </span>
                            </span>

                            <span>
                                ${escapeHtml(
                                    formatRelativeTime(
                                        message.created_at
                                    )
                                )}
                            </span>
                        </div>

                        <span class="sd-message-ticket">
                            ${escapeHtml(
                                message.ticket?.title ||
                                    'Untitled ticket'
                            )}
                        </span>

                        <span class="sd-message-body">
                            ${escapeHtml(message.body || '')}
                        </span>
                    </a>
                `;
            })
            .join('');
    }

    async function exportReport() {
        if (!validateDateRange()) {
            return;
        }

        const format =
            window.confirm(
                'Select OK to export CSV, or Cancel to export JSON.'
            )
                ? 'csv'
                : 'json';

        const params = getReportingParams();
        params.set('format', format);

        const exportButton = getElement('sdExportButton');

        if (exportButton) {
            exportButton.disabled = true;
        }

        try {
            const response = await fetch(
                buildApiUrl(
                    `analytics/export?${params.toString()}`
                ),
                {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'X-WP-Nonce': config.nonce,
                    },
                }
            );

            if (!response.ok) {
                throw await createResponseError(response);
            }

            const blob = await response.blob();
            const downloadUrl = URL.createObjectURL(blob);
            const link = document.createElement('a');

            link.href = downloadUrl;
            link.download =
                getDownloadFilename(response, format);

            document.body.append(link);
            link.click();
            link.remove();

            URL.revokeObjectURL(downloadUrl);
        } catch (error) {
            handleError(error);
        } finally {
            if (exportButton) {
                exportButton.disabled = false;
            }
        }
    }

    function getDownloadFilename(response, format) {
        const disposition =
            response.headers.get('Content-Disposition');

        const match = disposition?.match(
            /filename="?([^"]+)"?/i
        );

        if (match?.[1]) {
            return match[1];
        }

        return `sweetdesk-analytics.${format}`;
    }

    async function apiFetch(path, options = {}) {
        const response = await fetch(buildApiUrl(path), {
            method: options.method || 'GET',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-WP-Nonce': config.nonce,
                ...options.headers,
            },
            body: options.body
                ? JSON.stringify(options.body)
                : undefined,
        });

        if (!response.ok) {
            throw await createResponseError(response);
        }

        return response.json();
    }

    function buildApiUrl(path) {
        const root = String(config.restRoot).replace(/\/+$/, '');
        const endpoint = String(path).replace(/^\/+/, '');

        return `${root}/sweetdesk/v1/${endpoint}`;
    }

    async function createResponseError(response) {
        let message = `Request failed with status ${response.status}.`;

        try {
            const body = await response.json();

            if (body?.message) {
                message = body.message;
            }
        } catch {
            // Response was not JSON.
        }

        const error = new Error(message);
        error.status = response.status;

        return error;
    }

    function getTicketUrl(ticketId) {
        if (!ticketId) {
            return '#';
        }

        const baseUrl =
            config.ticketPageUrl ||
            'admin.php?page=sweetdesk-tickets';

        const separator = baseUrl.includes('?') ? '&' : '?';

        return `${baseUrl}${separator}ticket_id=${encodeURIComponent(
            ticketId
        )}`;
    }

    function formatNumber(value) {
        const number = Number(value);

        if (!Number.isFinite(number)) {
            return '—';
        }

        return new Intl.NumberFormat().format(number);
    }

    function formatScore(value) {
        const number = Number(value);

        if (!Number.isFinite(number)) {
            return '—';
        }

        return `${number.toFixed(2).replace(/\.?0+$/, '')} / 5`;
    }

    function formatDuration(seconds) {
        const totalSeconds = Number(seconds);

        if (!Number.isFinite(totalSeconds)) {
            return '—';
        }

        if (totalSeconds < 60) {
            return `${Math.round(totalSeconds)} sec`;
        }

        const minutes = totalSeconds / 60;

        if (minutes < 60) {
            return `${formatDecimal(minutes)} min`;
        }

        const hours = minutes / 60;

        if (hours < 24) {
            return `${formatDecimal(hours)} hr`;
        }

        return `${formatDecimal(hours / 24)} days`;
    }

    function formatAge(seconds) {
        const totalSeconds = Number(seconds);

        if (!Number.isFinite(totalSeconds)) {
            return '—';
        }

        const days = Math.floor(totalSeconds / 86400);

        if (days >= 1) {
            return `${days} ${days === 1 ? 'day' : 'days'}`;
        }

        const hours = Math.floor(totalSeconds / 3600);

        if (hours >= 1) {
            return `${hours} ${hours === 1 ? 'hour' : 'hours'}`;
        }

        const minutes = Math.max(
            0,
            Math.floor(totalSeconds / 60)
        );

        return `${minutes} ${
            minutes === 1 ? 'minute' : 'minutes'
        }`;
    }

    function formatRelativeTime(mysqlDateTime) {
        if (!mysqlDateTime) {
            return '';
        }

        /*
         * WordPress/MySQL DATETIME values do not contain a timezone.
         * The localized site timezone can be appended by the API later.
         * For now this treats the supplied value as local time.
         */
        const parsedDate = new Date(
            String(mysqlDateTime).replace(' ', 'T')
        );

        if (Number.isNaN(parsedDate.getTime())) {
            return mysqlDateTime;
        }

        const difference = Math.max(
            0,
            Date.now() - parsedDate.getTime()
        );

        const minutes = Math.floor(difference / 60000);
        const hours = Math.floor(difference / 3600000);
        const days = Math.floor(difference / 86400000);

        if (minutes < 1) {
            return 'Just now';
        }

        if (minutes < 60) {
            return `${minutes}m ago`;
        }

        if (hours < 24) {
            return `${hours}h ago`;
        }

        return `${days}d ago`;
    }

    function formatDecimal(value) {
        return Number(value)
            .toFixed(1)
            .replace(/\.0$/, '');
    }

    function truncateText(value, maximumLength) {
        const text = String(value || '');

        if (text.length <= maximumLength) {
            return text;
        }

        return `${text.slice(0, maximumLength - 1)}…`;
    }

    function setLoading(isLoading) {
        state.isLoading = isLoading;

        const sections = [
            'sdSectionTickets',
            'sdSectionOldest',
            'sdRecentMessages',
        ];

        sections.forEach((id) => {
            getElement(id)?.classList.toggle(
                'is-loading',
                isLoading
            );
        });

        getElement('sdApplyDateRange')?.toggleAttribute(
            'disabled',
            isLoading
        );

        getElement('sdExportButton')?.toggleAttribute(
            'disabled',
            isLoading
        );
    }

    function showNotice(message, type = 'info') {
        const notice = getElement('sdAnalyticsNotice');

        if (!notice) {
            return;
        }

        notice.hidden = false;
        notice.textContent = message;
        notice.className =
            `sd-analytics-notice sd-analytics-notice--${type}`;
    }

    function hideNotice() {
        const notice = getElement('sdAnalyticsNotice');

        if (!notice) {
            return;
        }

        notice.hidden = true;
        notice.textContent = '';
    }

    function handleError(error) {
        console.error('SweetDesk analytics error:', error);

        showNotice(
            error?.message ||
                'An unexpected analytics error occurred.',
            'error'
        );
    }

    function getElement(id) {
        return document.getElementById(id);
    }

    function setText(id, value) {
        const element = getElement(id);

        if (element) {
            element.textContent = value;
        }
    }

    function setTextInputValue(id, value) {
        const input = getElement(id);

        if (input) {
            input.value = value;
        }
    }

    function escapeHtml(value) {
        const element = document.createElement('div');
        element.textContent = String(value ?? '');

        return element.innerHTML;
    }

    function escapeAttribute(value) {
        return escapeHtml(value).replace(/"/g, '&quot;');
    }
})();