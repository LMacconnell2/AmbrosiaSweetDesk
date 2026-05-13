// ── Tab switching ──
function switchTab(tab) {
    document.querySelectorAll('.nav-item').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.tab === tab);
    });
    document.querySelectorAll('.tab-panel').forEach(panel => {
        panel.classList.toggle('active', panel.id === 'tab-' + tab);
    });
}

// ── Email Notifications toggle ──
function toggleEmailConfig(checked) {
    const config = document.getElementById('emailConfig');
    config.classList.toggle('open', checked);
}

// Init email config state on load
document.addEventListener('DOMContentLoaded', () => {
    const emailToggle = document.getElementById('toggle-email');
    if (emailToggle && emailToggle.checked) {
        document.getElementById('emailConfig').classList.add('open');
    }
});

// ── Custom Statuses ──
function showAddStatus() {
    document.getElementById('addStatusRow').style.display = 'flex';
    document.getElementById('addStatusBtn').style.display = 'none';
    document.getElementById('newStatusInput').focus();
}

function cancelAddStatus() {
    document.getElementById('addStatusRow').style.display = 'none';
    document.getElementById('addStatusBtn').style.display = '';
    document.getElementById('newStatusInput').value = '';
}

function confirmAddStatus() {
    const input = document.getElementById('newStatusInput');
    const value = input.value.trim();
    if (!value) return;

    const li = document.createElement('li');
    li.className = 'config-item';
    li.innerHTML = `<span>${value}</span><button class="btn-remove" onclick="removeItem(this)">Remove</button>`;
    document.getElementById('statusList').appendChild(li);

    cancelAddStatus();
}

// ── Custom Fields ──
function showAddField() {
    document.getElementById('addFieldRow').style.display = 'flex';
    document.getElementById('addFieldBtn').style.display = 'none';
    document.getElementById('newFieldInput').focus();
}

function cancelAddField() {
    document.getElementById('addFieldRow').style.display = 'none';
    document.getElementById('addFieldBtn').style.display = '';
    document.getElementById('newFieldInput').value = '';
}

function confirmAddField() {
    const input = document.getElementById('newFieldInput');
    const value = input.value.trim();
    if (!value) return;

    const li = document.createElement('li');
    li.className = 'config-item';
    li.innerHTML = `<span>${value}</span><button class="btn-edit-link" onclick="editField(this)">Edit</button>`;
    document.getElementById('fieldList').appendChild(li);

    cancelAddField();
}

// ── Shared helpers ──
function removeItem(btn) {
    btn.closest('li').remove();
}

function editField(btn) {
    const li = btn.closest('li');
    const span = li.querySelector('span');
    const current = span.textContent;

    const input = document.createElement('input');
    input.type = 'text';
    input.value = current;
    input.style.cssText = 'font-family:inherit;font-size:0.875rem;border:1.5px solid #2563eb;border-radius:6px;padding:4px 8px;outline:none;flex:1;';

    const saveBtn = document.createElement('button');
    saveBtn.textContent = 'Save';
    saveBtn.className = 'btn-add-confirm';
    saveBtn.style.cssText = 'padding:5px 12px;font-size:0.82rem;';

    const cancelBtn = document.createElement('button');
    cancelBtn.textContent = 'Cancel';
    cancelBtn.className = 'btn-add-cancel';
    cancelBtn.style.cssText = 'padding:4px 10px;font-size:0.82rem;';

    li.style.gap = '8px';
    li.replaceChild(input, span);
    btn.replaceWith(saveBtn, cancelBtn);
    input.focus();

    saveBtn.onclick = () => {
        const newVal = input.value.trim() || current;
        const newSpan = document.createElement('span');
        newSpan.textContent = newVal;
        const newEdit = document.createElement('button');
        newEdit.textContent = 'Edit';
        newEdit.className = 'btn-edit-link';
        newEdit.onclick = () => editField(newEdit);
        li.style.gap = '';
        li.replaceChild(newSpan, input);
        saveBtn.replaceWith(newEdit);
        cancelBtn.remove();
    };

    cancelBtn.onclick = () => {
        li.style.gap = '';
        li.replaceChild(span, input);
        saveBtn.replaceWith(btn);
        cancelBtn.remove();
    };
}

// Allow Enter key on add inputs
document.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
        if (document.activeElement.id === 'newStatusInput') confirmAddStatus();
        if (document.activeElement.id === 'newFieldInput') confirmAddField();
    }
});
