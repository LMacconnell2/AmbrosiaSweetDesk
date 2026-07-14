<?php

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="sd-analytics-page">

    <div class="sd-analytics-header">
        <h1 class="sd-page-title">Analytics</h1>

        <button
            type="button"
            class="sd-export-btn"
            id="sdExportButton"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8-4-4m0 0L8 8m4-4v12"
                />
            </svg>

            Export Report
        </button>
    </div>

    <div
        class="sd-analytics-notice"
        id="sdAnalyticsNotice"
        role="status"
        aria-live="polite"
        hidden
    ></div>

    <!-- Admin-only controls. Hidden by JavaScript for editors. -->
    <div class="sd-view-toggle" id="sdViewToggle">
        <button
            type="button"
            class="sd-toggle-btn active"
            id="btnCompanyWide"
            data-view-mode="company"
        >
            Company-wide
        </button>

        <button
            type="button"
            class="sd-toggle-btn"
            id="btnSpecificUser"
            data-view-mode="user"
        >
            Specific User
        </button>
    </div>

    <div
        class="sd-user-selector"
        id="sdUserSelector"
        hidden
    >
        <label
            class="sd-user-selector-label"
            for="sdUserSelect"
        >
            Displaying analytics for
        </label>

        <select
            id="sdUserSelect"
            class="sd-user-select"
        >
            <!-- Populated by analytics.js -->
        </select>
    </div>

    <h2 class="sd-subheader">Statistics</h2>

    <div class="sd-date-range">
        <span class="sd-date-range-label">
            Display data from between:
        </span>

        <input
            type="date"
            id="sdDateStart"
            class="sd-date-input"
        />

        <span class="sd-date-sep">&#8212;</span>

        <input
            type="date"
            id="sdDateEnd"
            class="sd-date-input"
        />

        <button
            type="button"
            class="button"
            id="sdApplyDateRange"
        >
            Apply
        </button>
    </div>

    <!-- Tickets -->
    <div
        class="sd-section sd-section--full"
        id="sdSectionTickets"
    >
        <div class="sd-section-header">
            <h2 class="sd-section-title">Tickets</h2>
        </div>

        <!-- User/editor view -->
        <div
            class="sd-view sd-view--editor"
            id="viewTicketsEditor"
        >
            <div class="sd-stat-grid">
                <a
                    href="#"
                    class="sd-stat-block sd-stat-link"
                    id="sdUserAssignedLink"
                    title="View assigned tickets"
                >
                    <span class="sd-stat-label">Assigned</span>
                    <span
                        class="sd-stat-value"
                        id="sdUserAssigned"
                    >
                        &mdash;
                    </span>
                </a>

                <a
                    href="#"
                    class="sd-stat-block sd-stat-link"
                    id="sdUserClearedLink"
                    title="View cleared tickets"
                >
                    <span class="sd-stat-label">Cleared</span>
                    <span
                        class="sd-stat-value"
                        id="sdUserCleared"
                    >
                        &mdash;
                    </span>
                </a>
            </div>
        </div>

        <!-- Company view -->
        <div
            class="sd-view sd-view--company"
            id="viewTicketsCompany"
            hidden
        >
            <div class="sd-stat-grid sd-stat-grid--2x2">
                <a
                    href="#"
                    class="sd-stat-block sd-stat-link"
                    id="sdCompanyReceivedLink"
                    title="View received tickets"
                >
                    <span class="sd-stat-label">Received</span>
                    <span
                        class="sd-stat-value"
                        id="sdCompanyReceived"
                    >
                        &mdash;
                    </span>
                </a>

                <a
                    href="#"
                    class="sd-stat-block sd-stat-link"
                    id="sdCompanyClearedLink"
                    title="View cleared tickets"
                >
                    <span class="sd-stat-label">Cleared</span>
                    <span
                        class="sd-stat-value"
                        id="sdCompanyCleared"
                    >
                        &mdash;
                    </span>
                </a>

                <div class="sd-stat-block sd-stat-block--sub">
                    <span class="sd-stat-label">
                        Avg Received<br />
                        <span class="sd-stat-sublabel">
                            per team member
                        </span>
                    </span>

                    <span
                        class="sd-stat-value sd-stat-value--sub"
                        id="sdCompanyAverageReceived"
                    >
                        &mdash;
                    </span>
                </div>

                <div class="sd-stat-block sd-stat-block--sub">
                    <span class="sd-stat-label">
                        Avg Cleared<br />
                        <span class="sd-stat-sublabel">
                            per team member
                        </span>
                    </span>

                    <span
                        class="sd-stat-value sd-stat-value--sub"
                        id="sdCompanyAverageCleared"
                    >
                        &mdash;
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="sd-two-col">

        <!-- Resolution time -->
        <div class="sd-section">
            <div class="sd-section-header">
                <h2 class="sd-section-title">Resolution Time</h2>
            </div>

            <!-- User/editor view -->
            <div
                class="sd-view sd-view--editor"
                id="viewResolutionEditor"
            >
                <div class="sd-report-rows">

                    <!-- Median row removed -->

                    <div class="sd-report-row">
                        <span class="sd-row-label">Average</span>
                        <span
                            class="sd-row-value"
                            id="sdUserResolutionAverage"
                        >
                            &mdash;
                        </span>
                    </div>

                    <div class="sd-report-row">
                        <span class="sd-row-label">Minimum</span>

                        <a
                            href="#"
                            class="sd-row-value sd-row-link sd-row-value--minmax"
                            id="sdUserResolutionMinimumLink"
                        >
                            <span
                                class="sd-row-ticket-name"
                                id="sdUserResolutionMinimumTicket"
                            >
                                &mdash;
                            </span>

                            <span
                                class="sd-row-score"
                                id="sdUserResolutionMinimumValue"
                            >
                                &mdash;
                            </span>
                        </a>
                    </div>

                    <div class="sd-report-row">
                        <span class="sd-row-label">Maximum</span>

                        <a
                            href="#"
                            class="sd-row-value sd-row-link sd-row-value--minmax"
                            id="sdUserResolutionMaximumLink"
                        >
                            <span
                                class="sd-row-ticket-name"
                                id="sdUserResolutionMaximumTicket"
                            >
                                &mdash;
                            </span>

                            <span
                                class="sd-row-score"
                                id="sdUserResolutionMaximumValue"
                            >
                                &mdash;
                            </span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Company view -->
            <div
                class="sd-view sd-view--company"
                id="viewResolutionCompany"
                hidden
            >
                <div class="sd-report-rows">

                    <!-- Median row removed -->

                    <div class="sd-report-row">
                        <span class="sd-row-label">Average</span>
                        <span
                            class="sd-row-value"
                            id="sdCompanyResolutionAverage"
                        >
                            &mdash;
                        </span>
                    </div>

                    <div class="sd-report-row sd-row-indented">
                        <span class="sd-row-label">
                            Average
                            <span class="sd-row-sublabel">
                                (per team member)
                            </span>
                        </span>

                        <span
                            class="sd-row-value"
                            id="sdCompanyResolutionAveragePerMember"
                        >
                            &mdash;
                        </span>
                    </div>

                    <div class="sd-report-row">
                        <span class="sd-row-label">Minimum</span>

                        <a
                            href="#"
                            class="sd-row-value sd-row-link sd-row-value--minmax"
                            id="sdCompanyResolutionMinimumLink"
                        >
                            <span
                                class="sd-row-ticket-name"
                                id="sdCompanyResolutionMinimumTicket"
                            >
                                &mdash;
                            </span>

                            <span
                                class="sd-row-score"
                                id="sdCompanyResolutionMinimumValue"
                            >
                                &mdash;
                            </span>
                        </a>
                    </div>

                    <div class="sd-report-row">
                        <span class="sd-row-label">Maximum</span>

                        <a
                            href="#"
                            class="sd-row-value sd-row-link sd-row-value--minmax"
                            id="sdCompanyResolutionMaximumLink"
                        >
                            <span
                                class="sd-row-ticket-name"
                                id="sdCompanyResolutionMaximumTicket"
                            >
                                &mdash;
                            </span>

                            <span
                                class="sd-row-score"
                                id="sdCompanyResolutionMaximumValue"
                            >
                                &mdash;
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feedback -->
        <div class="sd-section">
            <div class="sd-section-header">
                <h2 class="sd-section-title">Feedback</h2>
            </div>

            <!-- User/editor view -->
            <div
                class="sd-view sd-view--editor"
                id="viewFeedbackEditor"
            >
                <div class="sd-report-rows">

                    <!-- Median row removed -->

                    <div class="sd-report-row">
                        <span class="sd-row-label">Average</span>
                        <span
                            class="sd-row-value"
                            id="sdUserFeedbackAverage"
                        >
                            &mdash;
                        </span>
                    </div>

                    <div class="sd-report-row">
                        <span class="sd-row-label">Minimum</span>

                        <a
                            href="#"
                            class="sd-row-value sd-row-link sd-row-value--minmax"
                            id="sdUserFeedbackMinimumLink"
                        >
                            <span
                                class="sd-row-ticket-name"
                                id="sdUserFeedbackMinimumTicket"
                            >
                                &mdash;
                            </span>

                            <span
                                class="sd-row-score"
                                id="sdUserFeedbackMinimumValue"
                            >
                                &mdash;
                            </span>
                        </a>
                    </div>

                    <div class="sd-report-row">
                        <span class="sd-row-label">Maximum</span>

                        <a
                            href="#"
                            class="sd-row-value sd-row-link sd-row-value--minmax"
                            id="sdUserFeedbackMaximumLink"
                        >
                            <span
                                class="sd-row-ticket-name"
                                id="sdUserFeedbackMaximumTicket"
                            >
                                &mdash;
                            </span>

                            <span
                                class="sd-row-score"
                                id="sdUserFeedbackMaximumValue"
                            >
                                &mdash;
                            </span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Company view -->
            <div
                class="sd-view sd-view--company"
                id="viewFeedbackCompany"
                hidden
            >
                <div class="sd-report-rows">

                    <!-- Median row removed -->

                    <div class="sd-report-row">
                        <span class="sd-row-label">Average</span>
                        <span
                            class="sd-row-value"
                            id="sdCompanyFeedbackAverage"
                        >
                            &mdash;
                        </span>
                    </div>

                    <div class="sd-report-row sd-row-indented">
                        <span class="sd-row-label">
                            Average
                            <span class="sd-row-sublabel">
                                (per team member)
                            </span>
                        </span>

                        <span
                            class="sd-row-value"
                            id="sdCompanyFeedbackAveragePerMember"
                        >
                            &mdash;
                        </span>
                    </div>

                    <div class="sd-report-row">
                        <span class="sd-row-label">Minimum</span>

                        <a
                            href="#"
                            class="sd-row-value sd-row-link sd-row-value--minmax"
                            id="sdCompanyFeedbackMinimumLink"
                        >
                            <span
                                class="sd-row-ticket-name"
                                id="sdCompanyFeedbackMinimumTicket"
                            >
                                &mdash;
                            </span>

                            <span
                                class="sd-row-score"
                                id="sdCompanyFeedbackMinimumValue"
                            >
                                &mdash;
                            </span>
                        </a>
                    </div>

                    <div class="sd-report-row">
                        <span class="sd-row-label">Maximum</span>

                        <a
                            href="#"
                            class="sd-row-value sd-row-link sd-row-value--minmax"
                            id="sdCompanyFeedbackMaximumLink"
                        >
                            <span
                                class="sd-row-ticket-name"
                                id="sdCompanyFeedbackMaximumTicket"
                            >
                                &mdash;
                            </span>

                            <span
                                class="sd-row-score"
                                id="sdCompanyFeedbackMaximumValue"
                            >
                                &mdash;
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h2 class="sd-subheader">Action Items</h2>

    <!-- Oldest unresolved -->
    <div
        class="sd-section sd-section--full"
        id="sdSectionOldest"
    >
        <div class="sd-section-header">
            <h2 class="sd-section-title">
                Oldest Unresolved Tickets
            </h2>
        </div>

        <div
            class="sd-ticket-list"
            id="sdOldestTicketList"
        >
            <p class="sd-empty-state">Loading tickets...</p>
        </div>
    </div>

    <!-- Recent messages -->
    <div
        class="sd-section sd-section--full sd-section--recent-messages"
        id="sdRecentMessages"
    >
        <div class="sd-section-header sd-section-header--messages">
            <div>
                <h2 class="sd-section-title">Recent Messages</h2>

                <p class="sd-section-note">
                    Not filtered by date range
                </p>
            </div>

            <div class="sd-messages-controls">
                <select
                    id="sdMessageCount"
                    class="sd-select"
                >
                    <option value="3">Show 3</option>
                    <option value="5" selected>Show 5</option>
                    <option value="10">Show 10</option>
                </select>

                <select
                    id="sdMessageFilter"
                    class="sd-select"
                >
                    <option value="all" selected>
                        All sources
                    </option>

                    <option value="staff">
                        Employees only
                    </option>

                    <option value="customer">
                        Customers only
                    </option>
                </select>
            </div>
        </div>

        <div
            class="sd-message-list"
            id="sdMessageList"
        >
            <p class="sd-empty-state">Loading messages...</p>
        </div>
    </div>
</div>