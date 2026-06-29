function getPersonPanelShell() {
    return document.getElementById('personPanelShell');
}

function togglePersonPanel() {
    getPersonPanelShell()?.classList.toggle('collapsed');
}

function closePersonSidebar() {
    getPersonPanelShell()?.classList.add('collapsed');
}

function setType(type) {
    document.getElementById('typeInternal').classList.toggle('active', type === 'internal');
    document.getElementById('typeClient').classList.toggle('active', type === 'client');

    const companyField = document.getElementById('companyField');

    if (companyField) {
        companyField.hidden = type === 'internal';
    }
}

function openPersonSidebar(mode = 'create', data = {}) {
    const shell = getPersonPanelShell();

    shell?.classList.remove('collapsed');

    document.getElementById('person-panel-title').textContent =
        mode === 'create' ? 'Add New Person' : 'Edit Person';

    document.getElementById('person-panel-submit').textContent =
        mode === 'create' ? 'Add Person' : 'Save Changes';

    if (mode === 'create') {
        resetPersonForm();
        return;
    }

    document.getElementById('person-panel-name').value = data.name || '';
    document.getElementById('person-panel-role').value = data.role || '';
    document.getElementById('person-panel-email').value = data.email || '';
    document.getElementById('person-panel-phone').value = data.phone || '';
    document.getElementById('person-panel-company').value = data.company || '';
    document.getElementById('person-panel-notes').value = data.notes || '';

    setType(data.company ? 'client' : 'internal');
}

function resetPersonForm() {
    document.getElementById('person-panel-name').value = '';
    document.getElementById('person-panel-role').value = '';
    document.getElementById('person-panel-email').value = '';
    document.getElementById('person-panel-phone').value = '';
    document.getElementById('person-panel-company').value = '';
    document.getElementById('person-panel-notes').value = '';
    setType('internal');
}

function openNewPersonSidebar() {
    openPersonSidebar('create');
}

function openEditPersonSidebar(name, role, email, phone, company, notes) {
    openPersonSidebar('edit', { name, role, email, phone, company, notes });
}

document.getElementById('sd-new-person')?.addEventListener('click', openNewPersonSidebar);

let personToDelete = null;

function openDeletePersonModal(name, button) {
    personToDelete = {
        name,
        row: button.closest('tr')
    };
    document.getElementById('sd-delete-person-name').textContent = name;
    document.getElementById('sd-delete-person-modal').classList.add('active');
}

function closeDeletePersonModal() {
    document.getElementById('sd-delete-person-modal').classList.remove('active');
    personToDelete = null;
}

function confirmDeletePerson() {
    if (!personToDelete) {
        return;
    }

    personToDelete.row?.remove();
    closeDeletePersonModal();
}

document.getElementById('sd-delete-person-modal')?.addEventListener('click', function (e) {
    if (e.target === this) {
        closeDeletePersonModal();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    setType('internal');
});
