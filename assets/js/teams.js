function openEditTeamModal(name, desc) {
    document.getElementById('edit-team-name').value = name;
    document.getElementById('edit-team-desc').value = desc;
    document.getElementById('editTeamModal').classList.add('active');
}
function closeEditTeamModal() {
    document.getElementById('editTeamModal').classList.remove('active');
}
function openNewTeamModal() {
    document.getElementById('new-team-name').value = '';
    document.getElementById('new-team-desc').value = '';
    document.getElementById('new-team-member-list').innerHTML = '';
    document.getElementById('new-team-member-select').value = '';
    document.getElementById('newTeamModal').classList.add('active');
}
function closeNewTeamModal() {
    document.getElementById('newTeamModal').classList.remove('active');
}

let teamToDelete = null;

function openDeleteTeamModal(teamName, button) {
    teamToDelete = {
        name: teamName,
        card: button.closest('.team-card')
    };
    document.getElementById('delete-team-name').textContent = teamName;
    document.getElementById('deleteTeamModal').classList.add('active');
}

function closeDeleteTeamModal() {
    document.getElementById('deleteTeamModal').classList.remove('active');
    teamToDelete = null;
}

function confirmDeleteTeam() {
    if (!teamToDelete) {
        return;
    }

    teamToDelete.card?.remove();
    closeDeleteTeamModal();
}

function addTeamMember() {
    const select = document.getElementById('new-team-member-select');
    const list = document.getElementById('new-team-member-list');
    const name = select.value;

    if (!name) {
        return;
    }

    const existing = [...list.querySelectorAll('.member-item')].some(item => item.dataset.name === name);
    if (existing) {
        return;
    }

    const listItem = document.createElement('li');
    listItem.className = 'member-item';
    listItem.dataset.name = name;
    listItem.innerHTML = `
        <span>${name}</span>
        <button type="button" class="member-delete">Delete</button>
    `;

    list.appendChild(listItem);
    select.value = '';
}
function removeTeamMember(event) {
    if (!event.target.classList.contains('member-delete')) {
        return;
    }

    const listItem = event.target.closest('.member-item');
    if (listItem) {
        listItem.remove();
    }
}
function submitNewTeam(event) {
    event.preventDefault();
    closeNewTeamModal();
}

// Close on overlay click
document.getElementById('editTeamModal').addEventListener('click', function (e) {
    if (e.target === this) closeEditTeamModal();
});
document.getElementById('newTeamModal').addEventListener('click', function (e) {
    if (e.target === this) closeNewTeamModal();
});
document.getElementById('deleteTeamModal').addEventListener('click', function (e) {
    if (e.target === this) closeDeleteTeamModal();
});

document.getElementById('sd-new-team').addEventListener('click', openNewTeamModal);
document.getElementById('new-team-close').addEventListener('click', closeNewTeamModal);
document.getElementById('new-team-cancel').addEventListener('click', closeNewTeamModal);
document.getElementById('add-team-member').addEventListener('click', addTeamMember);
document.getElementById('new-team-member-list').addEventListener('click', removeTeamMember);
document.getElementById('new-team-form').addEventListener('submit', submitNewTeam);