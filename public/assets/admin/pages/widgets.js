(function () {
    const areaListContainer = document.getElementById('widget-area-list');
    const workspace = document.getElementById('widget-workspace');
    const activeAreaTitle = document.getElementById('active-area-title');
    const instanceList = document.getElementById('widget-instance-list');
    
    const btnShowAdd = document.getElementById('btn-show-add-widget');
    const addPanel = document.getElementById('widget-add-panel');
    const selectionStep = document.getElementById('widget-selection-step');
    const formStep = document.getElementById('widget-form-step');
    const selectionGrid = document.getElementById('widget-selection-grid');
    const typeInput = document.getElementById('widget-type-input');
    const btnCancelAdd = document.getElementById('btn-cancel-add');
    const btnBackToSelection = document.getElementById('btn-back-to-selection');
    const addForm = document.getElementById('widget-add-form');

    let currentArea = null;
    let draggingId = null;
    let widgetTypes = [];

    async function loadAreas() {
        try {
            const res = await Admin.request('GET', '/admin/widget-areas');
            const areas = res.data;

            const themeBadge = document.getElementById('active-theme-badge');
            if (themeBadge && res.theme) {
                themeBadge.textContent = res.theme.name || res.theme.key;
                themeBadge.style.display = 'inline-block';
            }
            
            if (!areas || areas.length === 0) {
                areaListContainer.innerHTML = '<div style="padding: 1rem; text-align: center; color: var(--admin-muted);">Belum ada area widget pada tema aktif.</div>';
                return;
            }

            areaListContainer.innerHTML = areas.map((a) => `
                <button class="area-btn ${currentArea === a.key_name ? 'active' : ''}" data-key="${a.key_name}" data-label="${Admin.escapeHtml(a.label)}">
                    <span>${Admin.escapeHtml(a.label)}</span>
                    <span class="badge">${a.scope}</span>
                </button>
            `).join('');
            
            workspace.style.display = 'flex';

            if (!currentArea && areas.length > 0) {
                activateArea(areas[0].key_name, areas[0].label);
            }
        } catch (e) {
            areaListContainer.innerHTML = `<div style="color: red;">Gagal memuat area.</div>`;
        }
    }

    function activateArea(key, label) {
        currentArea = key;
        activeAreaTitle.textContent = label;
        addPanel.style.display = 'none'; // hide add panel on switch
        
        // Update active class
        document.querySelectorAll('.area-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.key === key);
        });

        loadInstances();
    }

    async function loadTypes() {
        const res = await Admin.request('GET', '/admin/widgets');
        widgetTypes = res.data;
        
        selectionGrid.innerHTML = widgetTypes.map((w) => `
            <div class="widget-type-card" data-key="${w.key_name}" data-label="${Admin.escapeHtml(w.label)}">
                <div class="widget-type-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="3" x2="9" y2="21"></line></svg>
                </div>
                <div>
                    <div class="widget-type-title">${Admin.escapeHtml(w.label)}</div>
                    <div class="widget-type-desc">Widget standar CMS</div>
                </div>
            </div>
        `).join('');

        selectionGrid.querySelectorAll('.widget-type-card').forEach(card => {
            card.addEventListener('click', () => {
                const key = card.dataset.key;
                const label = card.dataset.label;
                typeInput.value = key;
                document.getElementById('form-step-title').textContent = 'Pengaturan: ' + label;
                renderDynamicFields();
                
                selectionStep.style.display = 'none';
                formStep.style.display = 'block';
            });
        });
    }

    function renderDynamicFields() {
        const container = document.getElementById('dynamic-widget-fields');
        if (!container) return;
        
        const selectedKey = typeInput.value;
        const typeDef = widgetTypes.find(w => w.key_name === selectedKey);
        
        if (!typeDef || !typeDef.form_fields || typeDef.form_fields.length === 0) {
            container.innerHTML = '<div style="color: var(--admin-muted); font-size: 0.9rem; padding: 0.5rem 0;">Widget ini tidak memiliki pengaturan tambahan.</div>';
            return;
        }
        
        let html = '';
        typeDef.form_fields.forEach(field => {
            html += `<label style="display: block; margin-bottom: 0.75rem;">
                <span style="display: block; margin-bottom: 0.35rem; font-weight: 500; font-size: 0.9rem; color: #334155;">${Admin.escapeHtml(field.label)}</span>`;
                
            if (field.type === 'textarea') {
                html += `<textarea name="dynamic_${field.name}" style="width: 100%; padding: 0.6rem; border: 1px solid #cbd5e1; border-radius: 6px; min-height: 80px; font-family: inherit;">${Admin.escapeHtml(String(field.default || ''))}</textarea>`;
            } else if (field.type === 'select') {
                html += `<select name="dynamic_${field.name}" style="width: 100%; padding: 0.6rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff; font-size: 0.9rem; color: #1e293b;">`;
                for (const [val, optLabel] of Object.entries(field.options || {})) {
                    const isSelected = String(field.default || '') === String(val) ? 'selected' : '';
                    html += `<option value="${Admin.escapeHtml(String(val))}" ${isSelected}>${Admin.escapeHtml(String(optLabel))}</option>`;
                }
                html += `</select>`;
            } else {
                html += `<input type="${field.type}" name="dynamic_${field.name}" value="${Admin.escapeHtml(String(field.default || ''))}" style="width: 100%; padding: 0.6rem; border: 1px solid #cbd5e1; border-radius: 6px;">`;
            }
            html += `</label>`;
        });
        
        container.innerHTML = html;
    }

    async function loadInstances() {
        instanceList.innerHTML = '<div style="text-align: center; padding: 2rem; color: var(--admin-muted);">Memuat widget...</div>';

        try {
            const res = await Admin.request('GET', `/admin/widget-areas/${currentArea}/instances`);
            const items = res.data;

            if (items.length === 0) {
                instanceList.innerHTML = `
                    <div style="text-align: center; padding: 3rem; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; color: var(--admin-muted);">
                        Belum ada widget di area ini.<br>Klik tombol <strong>+ Tambah Widget</strong> untuk memulai.
                    </div>
                `;
                return;
            }

            instanceList.innerHTML = items.map((i) => `
                <div class="widget-item-card" draggable="true" data-id="${i.id}" style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; ${Number(i.is_active) ? 'border-left: 3px solid var(--admin-primary);' : 'opacity: 0.7;'}">
                    <div style="display: flex; align-items: center; min-width: 200px; flex: 1;">
                        <span class="widget-handle" title="Tahan dan seret untuk memindahkan" style="cursor: grab; color: #94a3b8; margin-right: 1rem; padding: 0.5rem; background: #f1f5f9; border-radius: 4px; display: inline-flex;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="12" r="1"></circle><circle cx="9" cy="5" r="1"></circle><circle cx="9" cy="19" r="1"></circle><circle cx="15" cy="12" r="1"></circle><circle cx="15" cy="5" r="1"></circle><circle cx="15" cy="19" r="1"></circle></svg>
                        </span>
                        <div>
                            <div style="font-weight: 700; color: #1e293b; font-size: 1.05rem; margin-bottom: 0.15rem; display: flex; align-items: center; gap: 0.5rem;">
                                ${Admin.escapeHtml(i.title_override || i.widget_label)}
                                ${!Number(i.is_active) ? '<span style="font-size:0.65rem; padding: 0.15rem 0.4rem; background: #fee2e2; color: #ef4444; border-radius: 4px; font-weight: 600;">NONAKTIF</span>' : ''}
                            </div>
                            <div style="font-size: 0.8rem; color: #64748b; display: inline-flex; align-items: center; gap: 0.35rem; background: #f8fafc; padding: 0.15rem 0.5rem; border-radius: 12px; border: 1px solid #e2e8f0;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                                ${Admin.escapeHtml(i.widget_key)}
                            </div>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        <button class="btn btn-sm ${Number(i.is_active) ? 'btn-ghost' : 'btn-primary'}" data-toggle="${i.id}" data-active="${i.is_active}" style="flex: 1; justify-content: center; min-width: 100px; ${Number(i.is_active) ? 'border: 1px solid #cbd5e1; color: #475569;' : ''}">
                            ${Number(i.is_active) ? 'Nonaktifkan' : 'Aktifkan'}
                        </button>
                        <button class="btn btn-sm btn-ghost text-danger" data-delete="${i.id}" style="border: 1px solid #fca5a5; color: var(--admin-danger); width: 34px; padding: 0; justify-content: center;" title="Hapus Widget">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="pointer-events: none;"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </div>
                </div>
            `).join('');

            attachDragHandlers();
        } catch (e) {
            instanceList.innerHTML = `<div style="color: red;">Gagal memuat: ${Admin.escapeHtml(e.message)}</div>`;
        }
    }

    function attachDragHandlers() {
        const items = instanceList.querySelectorAll('.widget-item-card');

        items.forEach((el) => {
            el.addEventListener('dragstart', () => {
                draggingId = el.dataset.id;
                el.classList.add('dragging');
            });

            el.addEventListener('dragend', () => {
                el.classList.remove('dragging');
                persistOrder();
            });

            el.addEventListener('dragover', (e) => {
                e.preventDefault();
                const dragging = instanceList.querySelector('.dragging');
                if (!dragging || dragging === el) return;

                const rect = el.getBoundingClientRect();
                const before = (e.clientY - rect.top) < rect.height / 2;
                instanceList.insertBefore(dragging, before ? el : el.nextSibling);
            });
        });
    }

    async function persistOrder() {
        const order = Array.from(instanceList.querySelectorAll('.widget-item-card')).map((el) => parseInt(el.dataset.id, 10));
        try {
            await Admin.request('POST', `/admin/widget-areas/${currentArea}/reorder`, { order });
        } catch (err) {
            Admin.alert("Gagal menyimpan urutan: " + err.message, true);
        }
    }

    // Event Listeners
    areaListContainer.addEventListener('click', (e) => {
        const btn = e.target.closest('.area-btn');
        if (btn) {
            activateArea(btn.dataset.key, btn.dataset.label);
        }
    });

    btnShowAdd.addEventListener('click', () => {
        addPanel.style.display = 'flex';
        selectionStep.style.display = 'block';
        formStep.style.display = 'none';
        addForm.reset();
    });

    const closeModal = () => {
        addPanel.style.display = 'none';
    };

    btnCancelAdd.addEventListener('click', closeModal);
    const btnCloseModal = document.getElementById('btn-close-modal');
    if (btnCloseModal) {
        btnCloseModal.addEventListener('click', closeModal);
    }
    
    if (btnBackToSelection) {
        btnBackToSelection.addEventListener('click', () => {
            formStep.style.display = 'none';
            selectionStep.style.display = 'block';
        });
    }

    instanceList.addEventListener('click', async (e) => {
        const target = e.target;

        if (target.dataset.delete) {
            if (!await Admin.confirmDelete('widget ini dari area?')) return;
            try {
                await Admin.request('DELETE', `/admin/widget-instances/${target.dataset.delete}`);
                loadInstances();
            } catch (err) { Admin.alert(err.message, true); }
        } else if (target.dataset.toggle) {
            try {
                await Admin.request('PUT', `/admin/widget-instances/${target.dataset.toggle}`, {
                    is_active: target.dataset.active === '1' ? 0 : 1,
                });
                loadInstances();
            } catch (err) { Admin.alert(err.message, true); }
        }
    });

    addForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const settings = {};
        const dynamicInputs = addForm.querySelectorAll('[name^="dynamic_"]');
        dynamicInputs.forEach(input => {
            const fieldName = input.name.replace('dynamic_', '');
            settings[fieldName] = input.type === 'number' ? Number(input.value) : input.value;
        });

        const payload = {
            widget_key: typeInput.value,
            title_override: document.getElementById('widget-title-override').value || undefined,
            settings,
        };

        try {
            await Admin.request('POST', `/admin/widget-areas/${currentArea}/instances`, payload);
            addPanel.style.display = 'none';
            loadInstances();
        } catch (err) {
            Admin.alert(err.message, true);
        }
    });

    // Init
    loadAreas();
    loadTypes();
})();
