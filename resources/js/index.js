document.addEventListener('alpine:initializing', () => {
    window.Alpine.data('pageManager', () => ({
        init() {
            window.addEventListener('filament-page-manager::copy-url', (event) => {
                this.copyUrl(event.detail.url);
            });
        },
        copyUrl(url) {
            window.navigator.clipboard.writeText(url);
            this.$tooltip('Copied', {theme: window.Alpine.store('theme'), timeout: 2000});
        }
    }))
})
