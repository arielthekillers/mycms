document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('settings-form');

    // Previews
    const logoPreview = document.getElementById('logo-preview');
    const faviconPreview = document.getElementById('favicon-preview');

    function updatePreview(inputElement, previewElement) {
        inputElement.addEventListener('change', () => {
            if (inputElement.files && inputElement.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewElement.src = e.target.result;
                    previewElement.style.display = 'block';
                };
                reader.readAsDataURL(inputElement.files[0]);
            } else {
                previewElement.style.display = 'none';
            }
        });
    }

    updatePreview(document.getElementById('setting-site-logo-file'), logoPreview);
    updatePreview(document.getElementById('setting-site-favicon-file'), faviconPreview);

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
                
                if (settings.site_logo) {
                    document.getElementById('setting-site-logo').value = settings.site_logo;
                    logoPreview.src = settings.site_logo;
                    logoPreview.style.display = 'block';
                }
                if (settings.site_favicon) {
                    document.getElementById('setting-site-favicon').value = settings.site_favicon;
                    faviconPreview.src = settings.site_favicon;
                    faviconPreview.style.display = 'block';
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
