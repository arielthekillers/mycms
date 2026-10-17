(function () {
    const form = document.getElementById('theme-options-form');

    async function load() {
        try {
            const res = await Admin.request('GET', '/admin/theme-options');
            const options = res.data;

            document.getElementById('opt-primary-color').value = options.primary_color || '#0f766e';
            document.getElementById('opt-secondary-color').value = options.secondary_color || '#1e293b';
            document.getElementById('opt-font-heading').value = options.font_heading || '';
            document.getElementById('opt-font-body').value = options.font_body || '';
            document.getElementById('opt-logo-display').value = options.logo_display || 'logo_text';
            document.getElementById('opt-logo-text-width').value = options.logo_text_width || '200px';
            document.getElementById('opt-sticky-header').value = options.sticky_header || 'yes';
            
            if(document.getElementById('opt-header-style')) {
                document.getElementById('opt-header-style').value = options.header_style || 'auto';
            }
            
            document.getElementById('opt-menu-header').value = options.menu_header || '';
            document.getElementById('opt-menu-footer').value = options.menu_footer || '';
            if(document.getElementById('opt-homepage-slider')) {
                document.getElementById('opt-homepage-slider').value = options.homepage_slider || '';
            }
        } catch (e) {
            Admin.alert(e.message, true);
        }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const payload = {
            primary_color: document.getElementById('opt-primary-color').value,
            secondary_color: document.getElementById('opt-secondary-color').value,
            font_heading: document.getElementById('opt-font-heading').value,
            font_body: document.getElementById('opt-font-body').value,
            logo_display: document.getElementById('opt-logo-display').value,
            logo_text_width: document.getElementById('opt-logo-text-width').value,
            sticky_header: document.getElementById('opt-sticky-header').value,
            header_style: document.getElementById('opt-header-style') ? document.getElementById('opt-header-style').value : 'auto',
            menu_header: document.getElementById('opt-menu-header').value,
            menu_footer: document.getElementById('opt-menu-footer').value,
            homepage_slider: document.getElementById('opt-homepage-slider') ? document.getElementById('opt-homepage-slider').value : '',
        };

        try {
            await Admin.request('PUT', '/admin/theme-options', payload);
            Admin.alert('Tampilan disimpan. Cek beranda untuk melihat perubahan.');
        } catch (err) {
            Admin.alert(err.message, true);
        } finally {
            e.target.dispatchEvent(new Event('form:restore'));
        }
    });

    load();
})();
