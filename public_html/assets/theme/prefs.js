var Pref = {

    handleMenu: function ({ menu_layout = 'vertical' } = {}) {
        menu_layout === 'horizontal' ? handleNavbarMenu() : handleSidebarMenu();
    },

    apply: function (prefs = null) {

        this.prefs = prefs;

        this.handleMenu({
            menu_layout: this.prefs.menu_layout
        });

        setPanelTheme(this.prefs.theme);
    }

};
