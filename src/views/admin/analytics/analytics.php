<div class="sd-analytics-page">

    <!-- Header -->
    <div class="sd-analytics-header">
        <h1 class="sd-page-title">Analytics</h1>
        <button class="sd-export-btn">
            <span class="dashicons dashicons-download"></span>
            Export Report
        </button>
    </div>

    <!-- Admin-only: View mode toggle -->
    <div class="sd-view-toggle" id="sdViewToggle">
        <button class="sd-toggle-btn active" id="btnCompanyWide" onclick="setViewMode('company')">Company-wide</button>
        <button class="sd-toggle-btn" id="btnSpecificUser" onclick="setViewMode('user')">Specific User</button>
    </div>

    <!-- Admin-only: Specific user selector (hidden by default) -->
    <div class="sd-user-selector" id="sdUserSelector" style="display:none;">
        <span class="sd-user-selector-label">Displaying analytics for</span>
        <select id="sdUserSelect" class="sd-user-select">
            <option value="self" selected>Yourself (John Doe)</option>
            <option value="2">Jane Smith</option>
            <option value="3">Alice Williams</option>
        </select>
    </div>

    <!-- Statistics subheader -->
    <h2 class="sd-subheader">Statistics</h2>

    <!-- Date range picker -->
    <div class="sd-date-range">
        <span class="sd-date-range-label">Display data from between:</span>
        <input type="date" id="sdDateStart" class="sd-date-input" />
        <span class="sd-date-sep">&#8212;</span>
        <input type="date" id="sdDateEnd" class="sd-date-input" />
    </div>

    <!-- Row 1: Tickets — full width -->
    <div class="sd-section sd-section--full" id="sdSectionTickets">
        <div class="sd-section-header">
            <h2 class="sd-section-title">Tickets</h2>
        </div>

        <!-- Editor / specific-user view -->
        <div class="sd-view sd-view--editor" id="viewTicketsEditor">
            <div class="sd-stat-grid">
                <a href="#" class="sd-stat-block sd-stat-link" title="View assigned tickets">
                    <span class="sd-stat-label">Assigned</span>
                    <span class="sd-stat-value">24</span>
                </a>
                <a href="#" class="sd-stat-block sd-stat-link" title="View cleared tickets">
                    <span class="sd-stat-label">Cleared</span>
                    <span class="sd-stat-value">19</span>
                </a>
            </div>
        </div>

        <!-- Admin company-wide view: 2x2 grid -->
        <div class="sd-view sd-view--company" id="viewTicketsCompany" style="display:none;">
            <div class="sd-stat-grid sd-stat-grid--2x2">
                <a href="#" class="sd-stat-block sd-stat-link" title="View received tickets">
                    <span class="sd-stat-label">Received</span>
                    <span class="sd-stat-value">87</span>
                </a>
                <a href="#" class="sd-stat-block sd-stat-link" title="View cleared tickets">
                    <span class="sd-stat-label">Cleared</span>
                    <span class="sd-stat-value">63</span>
                </a>
                <div class="sd-stat-block sd-stat-block--sub">
                    <span class="sd-stat-label">Avg Received<br /><span class="sd-stat-sublabel">per team member</span></span>
                    <span class="sd-stat-value sd-stat-value--sub">29</span>
                </div>
                <div class="sd-stat-block sd-stat-block--sub">
                    <span class="sd-stat-label">Avg Cleared<br /><span class="sd-stat-sublabel">per team member</span></span>
                    <span class="sd-stat-value sd-stat-value--sub">21</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Resolution Time + Feedback — two columns -->
    <div class="sd-two-col">

        <!-- Resolution Time -->
        <div class="sd-section">
            <div class="sd-section-header">
                <h2 class="sd-section-title">Resolution Time</h2>
            </div>

            <!-- Editor / specific-user view -->
            <div class="sd-view sd-view--editor" id="viewResolutionEditor">
                <div class="sd-report-rows">
                    <div class="sd-report-row">
                        <span class="sd-row-label">Median</span>
                        <a href="#" class="sd-row-value sd-row-link">2.3 days</a>
                    </div>
                    <div class="sd-report-row">
                        <span class="sd-row-label">Average</span>
                        <a href="#" class="sd-row-value sd-row-link">2.4 days</a>
                    </div>
                    <div class="sd-report-row">
                        <span class="sd-row-label">Minimum</span>
                        <a href="#" class="sd-row-value sd-row-link sd-row-value--minmax">
                            <span class=\"sd-row-ticket-name\">('Login page throws 500 er...')</span>
                            <span class="sd-row-score">1.1 days</span>
                        </a>
                    </div>
                    <div class="sd-report-row">
                        <span class="sd-row-label">Maximum</span>
                        <a href="#" class="sd-row-value sd-row-link sd-row-value--minmax">
                            <span class=\"sd-row-ticket-name\">('Newly-created users can't acces...')</span>
                            <span class="sd-row-score">6.8 days</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Admin company-wide view -->
            <div class="sd-view sd-view--company" id="viewResolutionCompany" style="display:none;">
                <div class="sd-report-rows">
                    <div class="sd-report-row">
                        <span class="sd-row-label">Median</span>
                        <span class="sd-row-value">2.3 days</span>
                    </div>
                    <div class="sd-report-row">
                        <span class="sd-row-label">Average</span>
                        <span class="sd-row-value">2.4 days</span>
                    </div>
                    <div class="sd-report-row sd-row-indented">
                        <span class="sd-row-label">Average <span class="sd-row-sublabel">(per team member)</span></span>
                        <span class="sd-row-value">2.6 days</span>
                    </div>
                    <div class="sd-report-row">
                        <span class="sd-row-label">Minimum</span>
                        <a href="#" class="sd-row-value sd-row-link sd-row-value--minmax">
                            <span class=\"sd-row-ticket-name\">('Login page throws 500 er...')</span>
                            <span class="sd-row-score">1.1 days</span>
                        </a>
                    </div>
                    <div class="sd-report-row">
                        <span class="sd-row-label">Maximum</span>
                        <a href="#" class="sd-row-value sd-row-link sd-row-value--minmax">
                            <span class=\"sd-row-ticket-name\">('Newly-created users can't acces...')</span>
                            <span class="sd-row-score">6.8 days</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feedback scores -->
        <div class="sd-section">
            <div class="sd-section-header">
                <h2 class="sd-section-title">Feedback</h2>
            </div>

            <!-- Editor / specific-user view -->
            <div class="sd-view sd-view--editor" id="viewFeedbackEditor">
                <div class="sd-report-rows">
                    <div class="sd-report-row">
                        <span class="sd-row-label">Median</span>
                        <a href="#" class="sd-row-value sd-row-link"><span class="sd-score-main">4.2</span><span class="sd-score-denom">&thinsp;/ 5</span></a>
                    </div>
                    <div class="sd-report-row">
                        <span class="sd-row-label">Average</span>
                        <a href="#" class="sd-row-value sd-row-link"><span class="sd-score-main">4.0</span><span class="sd-score-denom">&thinsp;/ 5</span></a>
                    </div>
                    <div class="sd-report-row">
                        <span class="sd-row-label">Minimum</span>
                        <a href="#" class="sd-row-value sd-row-link sd-row-value--minmax">
                            <span class=\"sd-row-ticket-name\">('PDF export produces blan...')</span>
                            <span class="sd-row-score"><span class="sd-score-main">2</span><span class="sd-score-denom">&thinsp;/ 5</span></span>
                        </a>
                    </div>
                    <div class="sd-report-row">
                        <span class="sd-row-label">Maximum</span>
                        <a href="#" class="sd-row-value sd-row-link sd-row-value--minmax">
                            <span class=\"sd-row-ticket-name\">('Login page throws 500 er...')</span>
                            <span class="sd-row-score"><span class="sd-score-main">5</span><span class="sd-score-denom">&thinsp;/ 5</span></span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Admin company-wide view -->
            <div class="sd-view sd-view--company" id="viewFeedbackCompany" style="display:none;">
                <div class="sd-report-rows">
                    <div class="sd-report-row">
                        <span class="sd-row-label">Median</span>
                        <a href="#" class="sd-row-value sd-row-link"><span class="sd-score-main">4.1</span><span class="sd-score-denom">&thinsp;/ 5</span></a>
                    </div>
                    <div class="sd-report-row">
                        <span class="sd-row-label">Average</span>
                        <a href="#" class="sd-row-value sd-row-link"><span class="sd-score-main">3.9</span><span class="sd-score-denom">&thinsp;/ 5</span></a>
                    </div>
                    <div class="sd-report-row sd-row-indented">
                        <span class="sd-row-label">Average <span class="sd-row-sublabel">(per team member)</span></span>
                        <span class="sd-row-value"><span class="sd-score-main">3.7</span><span class="sd-score-denom">&thinsp;/ 5</span></span>
                    </div>
                    <div class="sd-report-row">
                        <span class="sd-row-label">Minimum</span>
                        <a href="#" class="sd-row-value sd-row-link sd-row-value--minmax">
                            <span class=\"sd-row-ticket-name\">('Billing page crash on che...')</span>
                            <span class="sd-row-score"><span class="sd-score-main">1</span><span class="sd-score-denom">&thinsp;/ 5</span></span>
                        </a>
                    </div>
                    <div class="sd-report-row">
                        <span class="sd-row-label">Maximum</span>
                        <a href="#" class="sd-row-value sd-row-link sd-row-value--minmax">
                            <span class=\"sd-row-ticket-name\">('Login page throws 500 er...')</span>
                            <span class="sd-row-score"><span class="sd-score-main">5</span><span class="sd-score-denom">&thinsp;/ 5</span></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /.sd-two-col -->

    <!-- Action Items subheader -->
    <h2 class="sd-subheader">Action Items</h2>

    <!-- Row 3: Oldest Unresolved — full width -->
    <div class="sd-section sd-section--full" id="sdSectionOldest">
        <div class="sd-section-header">
            <h2 class="sd-section-title">Oldest Unresolved Tickets</h2>
        </div>

        <!-- Editor / specific-user view -->
        <div class="sd-view sd-view--editor" id="viewOldestEditor">
            <div class="sd-ticket-list">
                <a href="#" class="sd-ticket-item">
                    <span class="sd-ticket-name">Login page throws 500 error on submit</span>
                    <span class="sd-ticket-age">18 days</span>
                </a>
                <a href="#" class="sd-ticket-item">
                    <span class="sd-ticket-name">PDF export produces blank file</span>
                    <span class="sd-ticket-age">11 days</span>
                </a>
                <a href="#" class="sd-ticket-item">
                    <span class="sd-ticket-name">Dashboard widgets not loading for Safari users</span>
                    <span class="sd-ticket-age">6 days</span>
                </a>
            </div>
        </div>

        <!-- Admin company-wide view -->
        <div class="sd-view sd-view--company" id="viewOldestCompany" style="display:none;">
            <div class="sd-ticket-list">
                <a href="#" class="sd-ticket-item">
                    <span class="sd-ticket-name">Newly-created users can't access settings</span>
                    <span class="sd-ticket-assignee">Alice Williams</span>
                    <span class="sd-ticket-age">24 days</span>
                </a>
                <a href="#" class="sd-ticket-item">
                    <span class="sd-ticket-name">Login page throws 500 error on submit</span>
                    <span class="sd-ticket-assignee">John Doe</span>
                    <span class="sd-ticket-age">18 days</span>
                </a>
                <a href="#" class="sd-ticket-item">
                    <span class="sd-ticket-name">PDF export produces blank file</span>
                    <span class="sd-ticket-assignee">John Doe</span>
                    <span class="sd-ticket-age">11 days</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Row 4: Recent Messages — full width, not date-range filtered -->
    <div class="sd-section sd-section--full sd-section--recent-messages" id="sdRecentMessages">
        <div class="sd-section-header sd-section-header--messages">
            <div>
                <h2 class="sd-section-title">Recent Messages</h2>
                <p class="sd-section-note">Not filtered by date range</p>
            </div>
            <div class="sd-messages-controls">
                <select id="sdMessageCount" class="sd-select" onchange="renderMessages()">
                    <option value="3">Show 3</option>
                    <option value="5" selected>Show 5</option>
                    <option value="10">Show 10</option>
                </select>
                <select id="sdMessageFilter" class="sd-select" onchange="renderMessages()">
                    <option value="all" selected>All sources</option>
                    <option value="employee">Employees only</option>
                    <option value="customer">Customers only</option>
                </select>
            </div>
        </div>
        <div class="sd-message-list" id="sdMessageList">
            <!-- Populated by analytics.js -->
        </div>
    </div>

</div>