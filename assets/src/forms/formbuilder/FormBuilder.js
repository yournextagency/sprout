import { DragDrop } from './helpers/DragDrop.js';
import { FieldLayout } from './helpers/FieldLayout.js';
import { Integrations } from './helpers/Integrations.js';

export const FormBuilder = (formId) => ({

    // Helpers
    DragDropHelper: DragDrop,
    FieldLayoutHelper: FieldLayout,

    formId: formId,

    lastUpdatedFormFieldUid: null,

    /**
     * Array of field data
     * [
     *  id: 123,
     *  type: className,
     *  etc.
     * ]
     */
    sourceFields: [],

    /**
     * [
     *   {
     *     id: 123,
     *     label: 'X',
     *     fields: [
     *        'fieldUid': {
     *          id: 123,
     *          name: 'Y',
     *          required,
     *          getExampleHtml(),
     *          getSettingsHtml(),
     *        },
     *        {},
     *        {}
     *      ]
     *   }
     * ]
     */
    tabs: [],
    // fields: [],
    // uiSettings: [],

    fieldLayoutUid: null,

    selectedTabUid: null,
    editTabUid: null,
    editFieldUid: null,

    dragOrigin: null,

    DragOrigins: {
        sourceField: 'source-field',
        layoutField: 'layout-field',
        layoutTabNav: 'layout-tab-nav',
    },

    isDraggingTabUid: null,
    isDragOverTabUid: null,

    isDraggingFormFieldUid: null,
    isDragOverFormFieldUid: null,

    init() {

        let self = this;
        // Get the saved fieldLayout data
        Craft.sendActionRequest('POST', 'sprout-module-forms/form-builder/get-submission-field-layout', {
            data: {
                formId: this.formId,
            },
        }).then((response) => {
            console.log('get-submission-field-layout', response);
            // self.tabs = [
            //   {
            //     id: 123,
            //     label: 'Tab 1',
            //     fields: [],
            //   },
            // ];
            self.tabs = response.data.layout.tabs;
            // self.fields = response.data.layout.fields;
            // self.uiSettings = response.data.layout.uiSettings;
            self.fieldLayoutUid = response.data.layout.uid;

            // get uid of first tab in tabs array
            self.selectedTabUid = self.tabs[0].uid ?? null;

        }).catch(() => {
            console.log('No form data found.');
        });

        window.FormBuilder = this;

        let sourceFields = JSON.parse(this.$refs.formBuilder.dataset.sourceFields);

        for (const field of sourceFields) {
            this.sourceFields.push(field);
        }
    },

    // Helper Methods

    getFieldsByGroup(handle) {
        return window.FormBuilder.sourceFields.filter(item => item.groupName === handle);
    },

    // scrollFieldLayout(stepY) {
    //   let scrollY = document.documentElement.scrollTop || document.body.scrollTop;
    //   window.scrollTo(0, (scrollY + stepY));
    //
    //   if (this.scrollActive) {
    //     setTimeout(function() {
    //       scroll(0, stepY);
    //     }, 20);
    //   }
    // },

    // isBefore(element1, element2) {
    //     if (element2.parentNode === element1.parentNode) {
    //         for (let currentElement = element1.previousSibling; currentElement && currentElement.nodeType !== 9; currentElement = currentElement.previousSibling) {
    //             if (currentElement === element2) {
    //                 return true;
    //             }
    //         }
    //     }
    //     return false;
    // },

    // The js output by the condition builder
    // "<script>
    // const conditionBuilderJs = "<script>Garnish.requestAnimationFrame(() => {
    //   const $button = $('#sources-__SOURCE_KEY__-condition-type-btn');
    //   $button.menubtn().data('menubtn').on('optionSelect', event => {
    //     const $option = $(event.option);
    //     $button.text($option.text()).removeClass('add');
    // // Don\'t use data(\'value\') here because it could result in an object if data-value is JSON
    //     const $input = $('#sources-__SOURCE_KEY__-condition-type-input').val($option.attr('data-value'));
    //     htmx.trigger($input[0], 'change');
    //   });
    // });
    // htmx.process(htmx.find('#__ID__'));
    // htmx.trigger(htmx.find('#__ID__'), 'htmx:load');
    // </script>";
    // swapPlaceholders(str, sourceKey) {
    //     const defaultId = `condition${Math.floor(Math.random() * 1000000)}`;
    //
    //     return str
    //         .replace(/__ID__/g, defaultId)
    //         .replace(/__SOURCE_KEY__(?=-)/g, Craft.formatInputId('"' + sourceKey + '"'))
    //         .replace(/__SOURCE_KEY__/g, sourceKey);
    // },
});
