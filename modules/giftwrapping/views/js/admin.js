document.addEventListener('DOMContentLoaded', function () {
    if (typeof tinySetup === 'function') {
        document.querySelectorAll('textarea.autoload_rte').forEach((textarea) => {
            tinySetup({
                editor_selector: textarea.id,
                setup: function (editor) {
                    editor.on('change', function () {
                        editor.save();
                    });
                }
            });
        });
    }
});
