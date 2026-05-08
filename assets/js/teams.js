function openEditTeamModal(name, desc) {
    document.getElementById('edit-team-name').value = name;
    document.getElementById('edit-team-desc').value = desc;
    document.getElementById('editTeamModal').style.display = 'flex';
}
function closeEditTeamModal() {
    document.getElementById('editTeamModal').style.display = 'none';
}
// Close on overlay click
document.getElementById('editTeamModal').addEventListener('click', function (e) {
    if (e.target === this) closeEditTeamModal();
});