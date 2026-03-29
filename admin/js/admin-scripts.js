(function(window, $) {
    'use strict';

    function initAdmin() {
        const admin = window.Alynt404Admin || {};

        if (typeof admin.initColorPickers === 'function') {
            admin.initColorPickers();
        }
        if (typeof admin.initMediaUploaders === 'function') {
            admin.initMediaUploaders();
        }
        if (typeof admin.initButtonLinks === 'function') {
            admin.initButtonLinks();
        }
        if (typeof admin.initMetaDescriptionCounter === 'function') {
            admin.initMetaDescriptionCounter();
        }
        if (typeof admin.initCustomCSSEditor === 'function') {
            admin.initCustomCSSEditor();
        }
        if (typeof admin.initFormValidation === 'function') {
            admin.initFormValidation();
        }
    }

    $(document).ready(initAdmin);
})(window, jQuery);
