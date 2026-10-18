document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('settings-form');

    // Previews & Controls
    const logoPreview = document.getElementById('logo-preview');
    const faviconPreview = document.getElementById('favicon-preview');
    const logoPreviewBox = document.getElementById('logo-preview-box');
    const faviconPreviewBox = document.getElementById('favicon-preview-box');
    const btnRemoveLogo = document.getElementById('btn-remove-logo');
    const btnRemoveFavicon = document.getElementById('btn-remove-favicon');
    const siteLogoInput = document.getElementById('setting-site-logo');
    const siteFaviconInput = document.getElementById('setting-site-favicon');
    const siteLogoFileInput = document.getElementById('setting-site-logo-file');
    const siteFaviconFileInput = document.getElementById('setting-site-favicon-file');

    function updatePreview(inputElement, previewElement, previewBox) {
        inputElement.addEventListener('change', () => {
            if (inputElement.files && inputElement.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewElement.src = e.target.result;
                    if (previewBox) previewBox.style.display = 'flex';
                };
                reader.readAsDataURL(inputElement.files[0]);
            }
        });
    }

    updatePreview(siteLogoFileInput, logoPreview, logoPreviewBox);
    updatePreview(siteFaviconFileInput, faviconPreview, faviconPreviewBox);

    // Remove buttons handler
    if (btnRemoveLogo) {
        btnRemoveLogo.addEventListener('click', () => {
            siteLogoInput.value = '';
            siteLogoFileInput.value = '';
            logoPreview.src = '';
            if (logoPreviewBox) logoPreviewBox.style.display = 'none';
            window.Admin.alert('Logo dihapus. Klik "Simpan Pengaturan" untuk menerapkan.', false);
        });
    }

    if (btnRemoveFavicon) {
        btnRemoveFavicon.addEventListener('click', () => {
            siteFaviconInput.value = '';
            siteFaviconFileInput.value = '';
            faviconPreview.src = '';
            if (faviconPreviewBox) faviconPreviewBox.style.display = 'none';
            window.Admin.alert('Favicon dihapus. Klik "Simpan Pengaturan" untuk menerapkan.', false);
        });
    }

    // Load existing settings
    window.Admin.request('GET', '/admin/api/settings')
        .then(response => {
            if (response.data) {
                const settings = response.data;
                if (settings.site_name) document.getElementById('setting-site-name').value = settings.site_name;
                if (settings.site_description) document.getElementById('setting-site-description').value = settings.site_description;
                if (settings.site_address) document.getElementById('setting-site-address').value = settings.site_address;
                if (settings.contact_email) document.getElementById('setting-contact-email').value = settings.contact_email;
                if (settings.timezone) document.getElementById('setting-timezone').value = settings.timezone;
                if (settings.posts_per_page) document.getElementById('setting-posts-per-page').value = settings.posts_per_page;
                if (settings.date_format) document.getElementById('setting-date-format').value = settings.date_format;
                if (settings.time_format) document.getElementById('setting-time-format').value = settings.time_format;
                
                if (settings.site_logo && settings.site_logo.trim() !== '') {
                    siteLogoInput.value = settings.site_logo;
                    logoPreview.src = settings.site_logo;
                    if (logoPreviewBox) logoPreviewBox.style.display = 'flex';
                } else {
                    siteLogoInput.value = '';
                    if (logoPreviewBox) logoPreviewBox.style.display = 'none';
                }

                if (settings.site_favicon && settings.site_favicon.trim() !== '') {
                    siteFaviconInput.value = settings.site_favicon;
                    faviconPreview.src = settings.site_favicon;
                    if (faviconPreviewBox) faviconPreviewBox.style.display = 'flex';
                } else {
                    siteFaviconInput.value = '';
                    if (faviconPreviewBox) faviconPreviewBox.style.display = 'none';
                }
            }
        })
        .catch(err => {
            console.error('Failed to load settings', err);
            window.Admin.alert('Gagal memuat pengaturan', true);
        });

    // Helper to upload file via media manager
    async function uploadMedia(fileInput) {
        if (!fileInput.files || !fileInput.files[0]) return null;
        
        const formData = new FormData();
        formData.append('file', fileInput.files[0]);
        
        const res = await window.Admin.request('POST', '/admin/media', formData);
        
        // Return WebP variant if available, otherwise fallback to the original
        let finalPath = res.data.disk_path;
        if (res.data.variants) {
            try {
                const variants = JSON.parse(res.data.variants);
                if (variants.webp) finalPath = variants.webp;
            } catch (e) {}
        }
        
        return '/uploads/' + finalPath;
    }

    // Save settings
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        try {
            // Check and upload files first if any
            const newLogoUrl = await uploadMedia(document.getElementById('setting-site-logo-file'));
            if (newLogoUrl) document.getElementById('setting-site-logo').value = newLogoUrl;

            const newFaviconUrl = await uploadMedia(document.getElementById('setting-site-favicon-file'));
            if (newFaviconUrl) document.getElementById('setting-site-favicon').value = newFaviconUrl;

            const payload = {
                site_name: document.getElementById('setting-site-name').value,
                site_description: document.getElementById('setting-site-description').value,
                site_address: document.getElementById('setting-site-address').value,
                contact_email: document.getElementById('setting-contact-email').value,
                timezone: document.getElementById('setting-timezone').value,
                posts_per_page: document.getElementById('setting-posts-per-page').value,
                date_format: document.getElementById('setting-date-format').value,
                time_format: document.getElementById('setting-time-format').value,
                site_logo: document.getElementById('setting-site-logo').value,
                site_favicon: document.getElementById('setting-site-favicon').value,
            };

            await window.Admin.request('PUT', '/admin/api/settings', payload);
            window.Admin.alert('Pengaturan berhasil disimpan', false);
            
            // Clear file inputs so we don't re-upload on next save
            document.getElementById('setting-site-logo-file').value = '';
            document.getElementById('setting-site-favicon-file').value = '';

        } catch (err) {
            console.error('Failed to save settings', err);
            window.Admin.alert('Gagal menyimpan pengaturan', true);
        } finally {
            form.dispatchEvent(new Event('form:restore'));
        }
    });
});
