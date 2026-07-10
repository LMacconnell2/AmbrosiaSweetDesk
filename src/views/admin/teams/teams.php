  <!-- Main -->
  <main class="main">
    <div class="page-header">
      <h1 class="page-title">Teams</h1>
      <button class="btn-primary" id="sd-new-team">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        New Team
      </button>
    </div>

    <div class="teams-grid" id="sd-teams-grid" aria-live="polite">
      <p class="sd-loading-message">Loading teams...</p>
    </div>

    <div class="sd-pagination" id="sd-teams-pagination" hidden>
      <button type="button" class="btn-secondary" id="sd-teams-prev">
          Previous
      </button>

      <span id="sd-teams-page-info"></span>

      <button type="button" class="btn-secondary" id="sd-teams-next">
          Next
      </button>
    </div>
  </main>

  <!-- Edit Team Modal -->
  <div class="modal-overlay" id="editTeamModal">
      <div class="modal">
          <div class="modal-header">
              <h2>Edit Team</h2>

              <button
                  type="button"
                  class="modal-close"
                  id="edit-team-close"
                  aria-label="Close edit team modal"
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
                          d="M6 18L18 6M6 6l12 12"
                      />
                  </svg>
              </button>
          </div>

          <form id="edit-team-form">
              <input type="hidden" id="edit-team-id">

              <div class="form-group">
                  <label for="edit-team-name">Team Name</label>
                  <input
                      type="text"
                      id="edit-team-name"
                      required
                  >
              </div>

              <div class="form-group">
                  <label for="edit-team-desc">Description</label>
                  <input
                      type="text"
                      id="edit-team-desc"
                  >
              </div>

              <div class="form-group">
                  <label for="edit-team-color">Team Color</label>
                  <input
                      type="color"
                      id="edit-team-color"
                      value="#2563eb"
                  >
              </div>

              <div class="form-group">
                  <label for="edit-team-member-search">
                      Search for members
                  </label>

                  <input
                      type="search"
                      id="edit-team-member-search"
                      placeholder="Start typing a name or email"
                      autocomplete="off"
                  >

                  <div
                      class="member-search-results"
                      id="edit-team-member-results"
                  ></div>
              </div>

              <div class="form-group">
                  <label>Selected Members</label>

                  <ul
                      class="member-list"
                      id="edit-team-member-list"
                  ></ul>
              </div>

              <div class="modal-actions">
                  <button
                      type="button"
                      class="btn-secondary"
                      id="edit-team-cancel"
                  >
                      Cancel
                  </button>

                  <button
                      type="submit"
                      class="btn-primary"
                      id="edit-team-submit"
                  >
                      Save Changes
                  </button>
              </div>
          </form>
      </div>
  </div>

  <!-- New Team Modal -->
  <div class="modal-overlay" id="newTeamModal">
      <div class="modal">
          <div class="modal-header">
              <h2>New Team</h2>

              <button
                  type="button"
                  class="modal-close"
                  id="new-team-close"
                  aria-label="Close new team modal"
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
                          d="M6 18L18 6M6 6l12 12"
                      />
                  </svg>
              </button>
          </div>

          <form id="new-team-form">
              <div class="form-group">
                  <label for="new-team-name">Team Name</label>
                  <input
                      type="text"
                      id="new-team-name"
                      placeholder="Enter team name"
                      required
                  >
              </div>

              <div class="form-group">
                  <label for="new-team-desc">Team Description</label>
                  <input
                      type="text"
                      id="new-team-desc"
                      placeholder="Enter team description"
                  >
              </div>

              <div class="form-group">
                  <label for="new-team-color">Team Color</label>
                  <input
                      type="color"
                      id="new-team-color"
                      value="#2563eb"
                  >
              </div>

              <div class="form-group">
                  <label for="new-team-member-search">
                      Search for members
                  </label>

                  <input
                      type="search"
                      id="new-team-member-search"
                      placeholder="Start typing a name or email"
                      autocomplete="off"
                  >

                  <div
                      class="member-search-results"
                      id="new-team-member-results"
                  ></div>
              </div>

              <div class="form-group">
                  <label>Selected Members</label>

                  <ul
                      class="member-list"
                      id="new-team-member-list"
                  ></ul>
              </div>

              <div class="modal-actions">
                  <button
                      type="button"
                      class="btn-secondary"
                      id="new-team-cancel"
                  >
                      Cancel
                  </button>

                  <button
                      type="submit"
                      class="btn-primary"
                      id="new-team-submit"
                  >
                      Save Team
                  </button>
              </div>
          </form>
      </div>
  </div>

  <!-- Delete Team Confirmation Modal -->
  <div class="modal-overlay" id="deleteTeamModal">
    <div class="modal delete-modal">
      <div class="modal-header">
        <h2>Delete Team</h2>
        <button class="modal-close" onclick="closeDeleteTeamModal()">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete <strong id="delete-team-name">Team Name</strong>? This action cannot be undone.</p>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn-secondary" onclick="closeDeleteTeamModal()">Cancel</button>
        <button
          type="button"
          class="btn-danger"
          id="confirm-delete-team"
      >
          Delete Team
      </button>
      </div>
    </div>
  </div>
