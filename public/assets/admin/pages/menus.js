(function () {
    const selectMenu = document.getElementById('menu-select');
    const builderWrap = document.getElementById('menu-builder-wrapper');
    const createWrap = document.getElementById('menu-create-wrapper');
    const itemsList = document.getElementById('menu-items-list');
    const saveBtn = document.getElementById('menu-save-btn');
    const deleteBtn = document.getElementById('menu-delete-btn');
    const saveStatus = document.getElementById('menu-save-status');
    const activeMenuName = document.getElementById('active-menu-name');
    const template = document.getElementById('menu-item-template');

    let currentMenuId = null;
    let allMenus = [];
    let menuItems = [];

    async function init() {
        await loadMenus();
        await loadAccordions();
        
        selectMenu.addEventListener('change', (e) => {
            if (e.target.value) {
                loadMenuItems(e.target.value);
            } else {
                builderWrap.style.display = 'none';
                saveBtn.style.display = 'none';
                deleteBtn.style.display = 'none';
            }
        });

        document.getElementById('menu-create-link').addEventListener('click', (e) => {
            e.preventDefault();
            createWrap.hidden = false;
            builderWrap.style.display = 'none';
            saveBtn.style.display = 'none';
            deleteBtn.style.display = 'none';
            selectMenu.value = '';
        });

        document.getElementById('menu-create-cancel').addEventListener('click', () => {
            createWrap.hidden = true;
            if (currentMenuId) {
                selectMenu.value = currentMenuId;
                selectMenu.dispatchEvent(new Event('change'));
            }
        });

        document.getElementById('menu-create-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const name = document.getElementById('menu-name').value;
            const slug = document.getElementById('menu-slug').value;
            try {
                const res = await Admin.request('POST', '/admin/menus', { name, slug });
                Admin.alert('Menu dibuat.');
                form.reset();
                createWrap.hidden = true;
                await loadMenus();
                selectMenu.value = res.data.id;
                selectMenu.dispatchEvent(new Event('change'));
            } catch (err) { 
                Admin.alert(err.message, true); 
            } finally {
                form.dispatchEvent(new Event('form:restore'));
            }
        });

        setupDragAndDrop();
        
        saveBtn.addEventListener('click', saveMenuStructure);
        
        document.getElementById('menu-delete-btn').addEventListener('click', async () => {
            if (!currentMenuId) return;
            if (!await Admin.confirmDelete('Apakah Anda yakin ingin menghapus seluruh menu ini beserta isinya?')) return;
            try {
                await Admin.request('DELETE', `/admin/menus/${currentMenuId}`);
                Admin.alert('Menu berhasil dihapus.');
                currentMenuId = null;
                builderWrap.style.display = 'none';
                saveBtn.style.display = 'none';
                deleteBtn.style.display = 'none';
                await loadMenus();
                selectMenu.value = '';
            } catch (err) { Admin.alert(err.message, true); }
        });
        
        setupAddButtons();
    }

    async function loadMenus() {
        try {
            const res = await Admin.request('GET', '/admin/menus');
            allMenus = res.data;
            selectMenu.innerHTML = '<option value="">-- Pilih Menu --</option>' + allMenus.map(m => 
                `<option value="${m.id}">${Admin.escapeHtml(m.name)}</option>`
            ).join('');
        } catch (e) {
            console.error('Failed to load menus', e);
        }
    }

    async function loadAccordions() {
        try {
            const [pagesRes, catRes] = await Promise.all([
                Admin.request('GET', '/admin/pages'),
                Admin.request('GET', '/admin/categories')
            ]);
            
            document.getElementById('menu-add-pages').innerHTML = pagesRes.data.map(p => 
                `<label><input type="checkbox" value="${p.id}" data-label="${Admin.escapeHtml(p.title)}"> ${Admin.escapeHtml(p.title)}</label>`
            ).join('');
            
            document.getElementById('menu-add-categories').innerHTML = catRes.data.map(c => 
                `<label><input type="checkbox" value="${c.id}" data-label="${Admin.escapeHtml(c.name)}"> ${Admin.escapeHtml(c.name)}</label>`
            ).join('');
        } catch (e) {
            console.error('Failed to load accordion data', e);
        }
    }

    function setupAddButtons() {
        document.querySelectorAll('.add-to-menu-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (!currentMenuId) return;
                const type = btn.dataset.type;
                const container = btn.closest('details').querySelector('.accordion-content');
                const checked = container.querySelectorAll('input[type="checkbox"]:checked');
                
                if (checked.length === 0) return;
                
                btn.textContent = 'Menambahkan...';
                btn.disabled = true;
                
                try {
                    for (const cb of checked) {
                        await Admin.request('POST', `/admin/menus/${currentMenuId}/items`, {
                            label: cb.dataset.label,
                            target_type: type,
                            target_id: cb.value
                        });
                        cb.checked = false;
                    }
                    Admin.alert('Item ditambahkan ke menu.');
                    await loadMenuItems(currentMenuId);
                } catch (e) {
                    Admin.alert(e.message, true);
                } finally {
                    btn.textContent = 'Tambah ke Menu';
                    btn.disabled = false;
                }
            });
        });

        document.getElementById('add-custom-link-btn').addEventListener('click', async (e) => {
            if (!currentMenuId) return;
            const urlInput = document.getElementById('custom-link-url');
            const textInput = document.getElementById('custom-link-text');
            
            if (!urlInput.value || !textInput.value) {
                Admin.alert('URL dan Teks Tautan harus diisi.', true);
                return;
            }
            
            const btn = e.target;
            btn.textContent = 'Menambahkan...';
            btn.disabled = true;
            
            try {
                await Admin.request('POST', `/admin/menus/${currentMenuId}/items`, {
                    label: textInput.value,
                    target_type: 'custom_url',
                    custom_url: urlInput.value
                });
                urlInput.value = 'http://';
                textInput.value = '';
                Admin.alert('Tautan kustom ditambahkan.');
                await loadMenuItems(currentMenuId);
            } catch (err) {
                Admin.alert(err.message, true);
            } finally {
                btn.textContent = 'Tambah ke Menu';
                btn.disabled = false;
            }
        });
    }

    async function loadMenuItems(menuId) {
        currentMenuId = menuId;
        const menuObj = allMenus.find(m => m.id == menuId);
        if (menuObj) activeMenuName.textContent = `Struktur Menu: ${menuObj.name}`;
        
        createWrap.hidden = true;
        builderWrap.style.display = 'flex';
        saveBtn.style.display = 'inline-block';
        deleteBtn.style.display = 'inline-block';
        itemsList.innerHTML = '<p style="color:var(--admin-muted); font-style:italic;">Memuat...</p>';

        try {
            const res = await Admin.request('GET', `/admin/menus/${menuId}/items`);
            menuItems = res.data;
            renderMenuTree();
        } catch (e) {
            itemsList.innerHTML = `<p style="color:var(--admin-danger);">Gagal memuat: ${Admin.escapeHtml(e.message)}</p>`;
        }
    }

    function buildTree(items, parentId = null) {
        const branch = [];
        for (const item of items) {
            if (item.parent_id == parentId) {
                const children = buildTree(items, item.id);
                if (children.length > 0) item.children = children;
                branch.push(item);
            }
        }
        return branch;
    }

    function renderMenuTree() {
        itemsList.innerHTML = '';
        if (menuItems.length === 0) {
            itemsList.innerHTML = '<p style="color:var(--admin-muted); font-style:italic;">Belum ada item. Tambahkan dari panel sebelah kiri.</p>';
            return;
        }

        const tree = buildTree(menuItems, null);
        
        function createNode(item) {
            const clone = template.content.cloneNode(true);
            const block = clone.querySelector('.menu-item-block');
            block.dataset.id = item.id;
            
            clone.querySelector('.menu-item-title').textContent = item.label;
            clone.querySelector('.menu-item-type').textContent = item.target_type;
            
            const editLabel = clone.querySelector('.edit-label');
            editLabel.value = item.label;
            
            const editUrl = clone.querySelector('.edit-url');
            if (item.target_type === 'custom_url') {
                editUrl.value = item.custom_url || '';
            } else {
                editUrl.value = item.url || '';
                editUrl.disabled = true;
            }

            const editNewTab = clone.querySelector('.edit-new-tab');
            editNewTab.checked = item.open_new_tab == 1;

            const header = clone.querySelector('.menu-item-header');
            const body = clone.querySelector('.menu-item-body');
            
            clone.querySelector('.toggle-item-btn').addEventListener('click', () => {
                body.classList.toggle('show');
                header.classList.toggle('expanded');
            });
            
            const childrenContainer = clone.querySelector('.menu-item-children');
            if (item.children) {
                item.children.forEach(child => {
                    childrenContainer.appendChild(createNode(child));
                });
            }

            // Real-time update label
            editLabel.addEventListener('input', (e) => {
                header.querySelector('.menu-item-title').textContent = e.target.value;
            });
            
            // Delete button
            clone.querySelector('.remove-item-btn').addEventListener('click', async () => {
                if (!await Admin.confirmDelete('Hapus item ini?')) return;
                try {
                    await Admin.request('DELETE', `/admin/menu-items/${item.id}`);
                    block.remove();
                    Admin.alert('Item dihapus.');
                } catch (e) {
                    Admin.alert(e.message, true);
                }
            });

            return block;
        }

        tree.forEach(item => {
            itemsList.appendChild(createNode(item));
        });
    }

    async function saveMenuStructure() {
        if (!currentMenuId) return;
        saveBtn.textContent = 'Menyimpan...';
        saveBtn.disabled = true;
        saveStatus.textContent = '';
        
        const updates = [];
        let globalOrder = 0;

        function traverse(container, parentId) {
            const blocks = container.children;
            for (let i = 0; i < blocks.length; i++) {
                const block = blocks[i];
                if (!block.classList.contains('menu-item-block')) continue;
                
                const id = block.dataset.id;
                globalOrder++;
                
                const label = block.querySelector('.edit-label').value;
                const newTab = block.querySelector('.edit-new-tab').checked ? 1 : 0;
                const url = block.querySelector('.edit-url').value;
                
                // First push structure update
                updates.push({
                    id: id,
                    parent_id: parentId,
                    sort_order: globalOrder
                });
                
                // Fire updateItem request in background for label/url/new_tab changes
                Admin.request('PUT', `/admin/menu-items/${id}`, {
                    label: label,
                    open_new_tab: newTab,
                    custom_url: url
                }).catch(e => console.error(e));

                const childrenContainer = block.querySelector('.menu-item-children');
                traverse(childrenContainer, id);
            }
        }

        traverse(itemsList, null);

        try {
            await Admin.request('PUT', `/admin/menus/${currentMenuId}/reorder`, { items: updates });
            const now = new Date();
            saveStatus.textContent = `Tersimpan pada ${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;
            Admin.alert('Struktur menu berhasil disimpan.', false);
        } catch (e) {
            Admin.alert('Gagal menyimpan struktur: ' + e.message, true);
        } finally {
            saveBtn.textContent = 'Simpan Menu';
            saveBtn.disabled = false;
        }
    }

    // --- HTML5 Drag and Drop for Nested Sortable ---
    let draggedItem = null;
    let placeholder = document.createElement('div');
    placeholder.className = 'menu-item-block';
    placeholder.style.border = '2px dashed var(--admin-primary)';
    placeholder.style.background = '#f0f9ff';
    placeholder.style.height = '45px';
    placeholder.style.marginBottom = '0.5rem';

    function setupDragAndDrop() {
        itemsList.addEventListener('dragstart', (e) => {
            if (e.target.classList.contains('menu-item-block')) {
                draggedItem = e.target;
                e.dataTransfer.effectAllowed = 'move';
                setTimeout(() => {
                    draggedItem.style.display = 'none';
                }, 0);
            }
        });

        itemsList.addEventListener('dragend', (e) => {
            if (draggedItem) {
                draggedItem.style.display = 'block';
                if (placeholder.parentNode) {
                    placeholder.parentNode.insertBefore(draggedItem, placeholder);
                    placeholder.remove();
                }
                draggedItem = null;
            }
        });

        itemsList.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            if (!draggedItem) return;
            
            // 1. Vertical placement based on Y axis
            const blocks = Array.from(itemsList.querySelectorAll('.menu-item-block'))
                .filter(b => b !== draggedItem && !draggedItem.contains(b) && b !== placeholder && b.style.display !== 'none');
            
            let closestBlock = null;
            let closestOffset = Number.NEGATIVE_INFINITY;
            
            blocks.forEach(block => {
                const content = block.querySelector('.menu-item-content');
                if (!content) return;
                const box = content.getBoundingClientRect();
                const offset = e.clientY - box.top - (box.height / 2);
                if (offset < 0 && offset > closestOffset) {
                    closestOffset = offset;
                    closestBlock = block;
                }
            });

            if (closestBlock) {
                // Prevent jumping back and forth if we are just changing horizontal depth
                if (placeholder.nextElementSibling !== closestBlock) {
                    closestBlock.parentNode.insertBefore(placeholder, closestBlock);
                }
            } else {
                if (placeholder.parentNode !== itemsList || placeholder.nextElementSibling !== null) {
                    itemsList.appendChild(placeholder);
                }
            }

            // 2. Horizontal placement (Indentation) based on X axis
            const rootLeft = itemsList.getBoundingClientRect().left;
            const desiredDepth = Math.max(0, Math.floor((e.clientX - rootLeft - 20) / 30));
            
            let currentDepth = 0;
            let curr = placeholder.parentNode;
            while (curr && curr !== itemsList) {
                if (curr.classList.contains('menu-item-children')) currentDepth++;
                curr = curr.parentNode;
            }
            
            if (desiredDepth > currentDepth) {
                let prev = placeholder.previousElementSibling;
                while (prev && (prev === draggedItem || prev.style.display === 'none')) {
                    prev = prev.previousElementSibling;
                }
                
                if (prev && prev.classList.contains('menu-item-block')) {
                    const childrenContainer = prev.querySelector('.menu-item-children');
                    if (childrenContainer) childrenContainer.appendChild(placeholder);
                }
            } else if (desiredDepth < currentDepth) {
                const parentBlock = placeholder.parentNode.closest('.menu-item-block');
                if (parentBlock) {
                    parentBlock.parentNode.insertBefore(placeholder, parentBlock.nextSibling);
                }
            }
        });
    }

    init();
})();
