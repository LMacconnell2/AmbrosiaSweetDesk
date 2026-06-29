function getClientPanelShell() {
    return document.getElementById('clientPanelShell');
}

function toggleClientPanel() {
    getClientPanelShell()?.classList.toggle('collapsed');
}

function closeClientSidebar() {
    getClientPanelShell()?.classList.add('collapsed');
}

function openClientSidebar(mode = 'create', data = {}) {
    const shell = getClientPanelShell();

    shell?.classList.remove('collapsed');

    document.getElementById('client-panel-title').textContent =
        mode === 'create' ? 'Add New Client' : 'Edit Client';

    document.getElementById('client-panel-submit').textContent =
        mode === 'create' ? 'Add Client' : 'Save Changes';

    if (mode === 'create') {
        resetClientForm();
        return;
    }

    document.getElementById('client-panel-name').value = data.name || '';

    document.getElementById('client-panel-notes').value = data.notes || '';

    const contactSelect = document.getElementById('client-panel-contact');

    for (const option of contactSelect.options) {
        if (option.value === data.contact || option.textContent === data.contact) {
            contactSelect.value = option.value;
            break;
        }
    }
}

function resetClientForm() {
    document.getElementById('client-panel-name').value = '';
    document.getElementById('client-panel-notes').value = '';
    document.getElementById('client-panel-contact').selectedIndex = 0;
}

function openNewClientSidebar() {
    openClientSidebar('create');
}

function openEditClientSidebar(name, contact, notes) {
    openClientSidebar('edit', { name, contact, notes });
}

document.getElementById('sd-new-client')?.addEventListener('click', openNewClientSidebar);
