function togglePanel() {
    const panel = document.getElementById('personPanel');
    const main  = document.getElementById('mainContent');
    panel.classList.toggle('collapsed');
    main.classList.toggle('panel-open');
}

function setType(type) {
    document.getElementById('typeInternal').classList.toggle('active', type === 'internal');
    document.getElementById('typeClient').classList.toggle('active', type === 'client');
}

function openEditPersonModal(name, role, email, phone, company, notes) {
    document.getElementById('edit-person-name').value    = name;
    document.getElementById('edit-person-role').value    = role;
    document.getElementById('edit-person-email').value   = email;
    document.getElementById('edit-person-phone').value   = phone;
    document.getElementById('edit-person-company').value = company;
    document.getElementById('edit-person-notes').value   = notes;
    document.getElementById('editPersonModal').style.display = 'flex';
}
function closeEditPersonModal() {
    document.getElementById('editPersonModal').style.display = 'none';
}
document.getElementById('editPersonModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditPersonModal();
});