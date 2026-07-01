/**
 * Shared Quill helpers for SweetDesk message composers.
 */
(function () {
    const editors = {};

    const TOOLBAR = [
        ['bold', 'italic', 'underline'],
        ['link'],
        [{ list: 'ordered' }, { list: 'bullet' }],
        ['blockquote', 'code-block']
    ];

    function getMount(id) {
        return document.getElementById(id);
    }

    function buildKeyboardBindings(onCtrlEnter) {
        const handler = () => {
            onCtrlEnter();
            return false;
        };

        return {
            bindings: {
                submitWin: {
                    key: 13,
                    ctrlKey: true,
                    handler
                },
                submitMac: {
                    key: 13,
                    metaKey: true,
                    handler
                }
            }
        };
    }

    function resetMountElement(element) {
        const parent = element.parentNode;

        if (!parent) {
            return element;
        }

        const replacement = document.createElement('div');
        replacement.id = element.id;
        replacement.className = element.className
            .split(' ')
            .filter((className) => !className.startsWith('ql-'))
            .join(' ')
            .trim();

        if (element.dataset.placeholder) {
            replacement.dataset.placeholder = element.dataset.placeholder;
        }

        parent.replaceChild(replacement, element);

        return replacement;
    }

    window.SweetDeskEditor = {
        init(id, options = {}) {
            if (typeof Quill === 'undefined') {
                return;
            }

            if (editors[id]) {
                return;
            }

            const mount = getMount(id);

            if (!mount) {
                return;
            }

            const modules = {
                toolbar: TOOLBAR
            };

            if (typeof options.onCtrlEnter === 'function') {
                modules.keyboard = buildKeyboardBindings(options.onCtrlEnter);
            }

            editors[id] = new Quill(mount, {
                theme: 'snow',
                modules,
                placeholder:
                    mount.dataset.placeholder ||
                    options.placeholder ||
                    ''
            });
        },

        destroy(id) {
            if (!editors[id]) {
                return;
            }

            const mount = getMount(id);

            delete editors[id];

            if (mount) {
                resetMountElement(mount);
            }
        },

        getContent(id) {
            const quill = editors[id];

            if (!quill) {
                return '';
            }

            return quill.root.innerHTML.trim();
        },

        setContent(id, html) {
            const quill = editors[id];

            if (!quill) {
                return;
            }

            quill.setContents([]);

            if (html) {
                quill.clipboard.dangerouslyPasteHTML(html);
            }
        },

        focus(id) {
            editors[id]?.focus();
        },

        isEmpty(id) {
            const quill = editors[id];

            if (!quill) {
                return true;
            }

            return quill.getText().trim().length === 0;
        },

        setPlaceholder(id, text) {
            const quill = editors[id];

            if (!quill) {
                const mount = getMount(id);

                if (mount) {
                    mount.dataset.placeholder = text;
                }

                return;
            }

            quill.root.dataset.placeholder = text;
        },

        stripHtml(html) {
            const element = document.createElement('div');
            element.innerHTML = html || '';
            return (element.textContent || '').replace(/\s+/g, ' ').trim();
        }
    };
})();
