// code/js/slim-selects.js

import SlimSelect from "slim-select";

class SlimSelects {
    constructor() {
        this.list = {};
    }

    init() {
        let that = this;
        $("select.slim-select").each(function () { that.add(this); });
    }

    add(selectNode) {
        let node = $(selectNode);
        let selectedOption = node.find('option[selected=selected]').first();

        let placeHolder = node.attr('placeholder');
        if (placeHolder) {
            let nodePlaceHolder = $('<option data-placeholder="true"></option>');
            if (!node.attr('multiple') && !selectedOption) {
                nodePlaceHolder.attr('selected', true);
            }
            nodePlaceHolder.text(placeHolder);
            node.prepend(nodePlaceHolder);
        }

        let options = {
            select:            selectNode,
            searchPlaceholder: translator.trans('app.slim_select.search'),
            searchText:        translator.trans('app.slim_select.no_result'),
            events: {
                searchFilter: (option, search) => {
                    let normalizedOption = option.text.normalize('NFD').replace(/[\u0300-\u036f]/g,'');
                    let normalizedSearch = search.normalize('NFD').replace(/[\u0300-\u036f]/g,'');
                    return normalizedOption.toLowerCase().indexOf(normalizedSearch.toLowerCase()) !== -1
                },
            }
        }

        this.list[selectNode.id] = new SlimSelect(options);
        if (node.attr('multiple')) {
            this.enableMultiselectRequire(selectNode.id);
        }
    }

    enableMultiselectRequire(id) {
        $(this.get(id).select.element).change($.proxy(function () { this.changeMultiselectRequire(id); }, this));
        this.changeMultiselectRequire(id);
    }

    changeMultiselectRequire(id) {
        let slimSelect = this.get(id);
        let select = $(slimSelect.select.element);
        let element = select.parent().find('.ss-multi-selected');

        if (select.val().length === 0 && select.attr('required')) {
            element.addClass('border-danger');
        } else {
            element.removeClass('border-danger');
        }
    }

    get(id) {
        return this.list[id];
    }
}

window.slimSelects = new SlimSelects();

documentReady.add(function () {
    slimSelects.init();
});
