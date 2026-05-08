function togglePanel() {
    const panel = document.getElementById('clientPanel');
    const main = document.getElementById('mainContent');
    panel.classList.toggle('collapsed');
    main.classList.toggle('panel-open');
}

function openEditClientModal(name, contact, notes) {
    document.getElementById('edit-client-name').value = name;
    document.getElementById('edit-client-notes').value = notes;
    const sel = document.getElementById('edit-client-contact');
    for (let opt of sel.options) { if (opt.value === contact) { sel.value = contact; break; } }
    document.getElementById('editClientModal').style.display = 'flex';
}
function closeEditClientModal() {
    document.getElementById('editClientModal').style.display = 'none';
}
document.getElementById('editClientModal').addEventListener('click', function (e) {
    if (e.target === this) closeEditClientModal();
});