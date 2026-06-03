// ── Tab switching ──
function switchTab(tab) {
    if (tab === 'profile') {
        window.location.href = '/wp-admin/profile.php';
        return;
    }
    document.querySelectorAll('.nav-item').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.tab === tab);
    });
    document.querySelectorAll('.tab-panel').forEach(panel => {
        panel.classList.toggle('active', panel.id === 'tab-' + tab);
    });
}

// ── Div-based toggles ──
function initToggles() {
    document.querySelectorAll('.toggle').forEach(toggle => {

        // Click to flip
        toggle.addEventListener('click', () => {
            const isChecked = toggle.getAttribute('aria-checked') === 'true';
            const newState = !isChecked;
            toggle.setAttribute('aria-checked', String(newState));

            // Fire any named callback stored in data-onchange
            const callbackName = toggle.dataset.onchange;
            if (callbackName && typeof window[callbackName] === 'function') {
                window[callbackName](newState);
            }
        });

        // Space or Enter key support (matches native checkbox behaviour)
        toggle.addEventListener('keydown', (e) => {
            if (e.key === ' ' || e.key === 'Enter') {
                e.preventDefault();
                toggle.click();
            }
        });
    });
}

// Helper: read a toggle's current state from the DOM
function isToggleOn(id) {
    const el = document.getElementById(id);
    return el ? el.getAttribute('aria-checked') === 'true' : false;
}

// ── Email Notifications toggle callback ──
function toggleEmailConfig(checked) {
    const config = document.getElementById('emailConfig');
    config.classList.toggle('open', checked);
}

// ── Init on load ──
document.addEventListener('DOMContentLoaded', () => {
    initToggles();

    // Sync email config panel with initial toggle state
    const emailOn = isToggleOn('toggle-email');
    document.getElementById('emailConfig').classList.toggle('open', emailOn);

    // Remove placeholder style on field type select once a real option is chosen
    const fieldType = document.getElementById('newFieldType');
    if (fieldType) {
        fieldType.addEventListener('change', () => {
            fieldType.classList.toggle('placeholder', fieldType.value === '');
        });
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
    const sel = document.getElementById('newFieldType');
    sel.value = '';
    sel.classList.add('placeholder');
}

function confirmAddField() {
    const input = document.getElementById('newFieldInput');
    const value = input.value.trim();
    const type = document.getElementById('newFieldType').value;
    if (!value || !type) return;

    const li = document.createElement('li');
    li.className = 'config-item';
    li.dataset.type = type;
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
    const currentType = li.dataset.type || 'string';

    const input = document.createElement('input');
    input.type = 'text';
    input.value = current;
    input.style.cssText = 'font-family:inherit;font-size:0.875rem;border:1.5px solid #2563eb;border-radius:6px;padding:4px 8px;outline:none;flex:1;';

    const typeSelect = document.createElement('select');
    typeSelect.style.cssText = 'font-family:inherit;font-size:0.875rem;border:1.5px solid #e5e7eb;border-radius:6px;padding:4px 28px 4px 8px;outline:none;cursor:pointer;appearance:none;-webkit-appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'12\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%236b7280\' stroke-width=\'2.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3E%3Cpolyline points=\'6 9 12 15 18 9\'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 8px center;background-color:#fff;color:#111827;';
    typeSelect.innerHTML = `<option value="string"${currentType === 'string' ? ' selected' : ''}>Text</option><option value="integer"${currentType === 'integer' ? ' selected' : ''}>Number</option>`;

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
    btn.replaceWith(typeSelect, saveBtn, cancelBtn);
    input.focus();

    saveBtn.onclick = () => {
        const newVal = input.value.trim() || current;
        const newType = typeSelect.value;
        const newSpan = document.createElement('span');
        newSpan.textContent = newVal;
        const newEdit = document.createElement('button');
        newEdit.textContent = 'Edit';
        newEdit.className = 'btn-edit-link';
        newEdit.onclick = () => editField(newEdit);
        li.dataset.type = newType;
        li.style.gap = '';
        li.replaceChild(newSpan, input);
        typeSelect.replaceWith(newEdit);
        saveBtn.remove();
        cancelBtn.remove();
    };

    cancelBtn.onclick = () => {
        li.style.gap = '';
        li.replaceChild(span, input);
        typeSelect.replaceWith(btn);
        saveBtn.remove();
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