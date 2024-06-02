const FieldLayout = {

    // -------------------------------------------------------------------------
    // FIELD LAYOUT HTML INPUTS
    // -------------------------------------------------------------------------

    get submissionFieldLayoutInputValue() {

        let FormBuilder = window.FormBuilder;

        let fieldLayout = {};

        if (FormBuilder.tabs.length && !FormBuilder.tabs[0].elements.length) {
            return [];
        }

        let fieldLayoutTabs = [];

        for (const tab of FormBuilder.tabs) {

            let fieldLayoutFields = [];

            for (const element of tab.elements) {

                let field = this.getFormFieldAttributes(element);

                fieldLayoutFields.push(field);
            }

            fieldLayoutTabs.push({
                id: tab.uid, // remove
                uid: tab.uid,
                name: tab.name,
                sortOrder: null,
                userCondition: null,
                elementCondition: null,
                elements: fieldLayoutFields,
            });
        }

        fieldLayout['tabs'] = fieldLayoutTabs;

        return JSON.stringify(fieldLayout);
    },

    // Removes uiSettings from element/field data
    getFormFieldAttributes(fieldData) {

        const {
            uiSettings,
            ...fieldAttributes
        } = fieldData;

        return fieldAttributes;
    },

    // -------------------------------------------------------------------------
    // FORM TABS
    // -------------------------------------------------------------------------

    getTabIndexByTabUid(tabUid) {
        return window.FormBuilder.tabs.findIndex(item => item.uid === tabUid);
    },

    addTab() {
        let newUid = Craft.uuid();
        let tab = {
            uid: newUid,
            name: 'New Page',
            userCondition: null,
            elementCondition: null,
            elements: [],
        };

        this.tabs.push(tab);
        this.selectedTabUid = newUid;
    },

    editTab(tab) {
        let self = this;
        let FormBuilder = window.FormBuilder;

        FormBuilder.editTabUid = tab.uid;

        let slideout = new Craft.CpScreenSlideout('sprout-module-forms/form-builder/edit-form-tab-slideout-via-cp-screen', {
            hasTabs: false,
            params: {
                formId: FormBuilder.formId,
                tab: tab,
            },
        });

        const $removeBtn = this.getRemoveButtonHtml();
        slideout.$footer.find('.flex-grow').before($removeBtn);
        slideout.$footer.find('.submit').addClass('secondary');

        $removeBtn.on('click', () => {
            if (FormBuilder.tabs.length === 1) {
                Craft.cp.displayNotice(Craft.t('sprout-module-forms', 'Form must contain at least one tab.'));

                return;
            }

            let tabIndex = self.getTabIndexByTabUid(FormBuilder.selectedTabUid);

            let newSelectedTabUid = (tabIndex - 1) >= 0
                ? FormBuilder.tabs[tabIndex - 1].uid
                : FormBuilder.tabs[tabIndex + 1].uid;

            FormBuilder.tabs.splice(tabIndex, 1);

            let newTabIndex = self.getTabIndexByTabUid(newSelectedTabUid);
            FormBuilder.selectedTabUid = FormBuilder.tabs[newTabIndex].uid;
            FormBuilder.editTabUid = FormBuilder.selectedTabUid;

            console.log('Remove Tab');

            slideout.close();
        });

        slideout.on('submit', ev => {
            this.updateTab(FormBuilder.editTabUid, ev.response.data.tab);
        });

        slideout.on('close', () => {
            console.log('Close Tab Slideout');
            FormBuilder.editTabUid = null;
        });
    },

    updateTab(tabUid, data) {
        let FormBuilder = window.FormBuilder;

        let tabIndex = this.getTabIndexByTabUid(tabUid);

        // loop through js object
        Object.entries(data).forEach(([index, value]) => {
            FormBuilder.tabs[tabIndex][index] = value;
        });

        if (!FormBuilder.tabs[tabIndex]['name']) {
            FormBuilder.tabs[tabIndex]['name'] = 'Page';
        }
    },

    updateTabPosition(tabUid, beforeTabUid = null) {
        let FormBuilder = window.FormBuilder;

        let beforeTabIndex = this.getTabIndexByTabUid(beforeTabUid);
        let tabIndex = this.getTabIndexByTabUid(tabUid);
        let targetTab = FormBuilder.tabs[tabIndex];

        // console.log(this.tabs);
        // Remove the updated tab
        FormBuilder.tabs.splice(tabIndex, 1);

        if (beforeTabUid) {

            // console.log('target' + targetTab);
            // Insert the updated tab before the target tab
            FormBuilder.tabs.splice(beforeTabIndex, 0, targetTab);
        } else {
            FormBuilder.tabs.push(targetTab);
        }

        FormBuilder.lastUpdatedTabUid = targetTab.uid;
    },

    // -------------------------------------------------------------------------
    // FORM FIELDS
    // -------------------------------------------------------------------------

    getFieldIndexByFieldUid(tab, fieldUid) {
        return tab.elements.findIndex(item => item.fieldUid === fieldUid);
    },

    getFieldByType(type) {
        return window.FormBuilder.sourceFields.filter(item => item.field.type === type)[0] ?? null;
    },

    // -------------------------------------------------------------------------
    // LAYOUT ELEMENTS
    // -------------------------------------------------------------------------

    addFieldToLayoutTab(type, beforeFieldUid = null) {
        console.log('addFieldToLayoutTab', type, beforeFieldUid);

        let FormBuilder = window.FormBuilder;

        let fieldData = this.getFieldByType(type);
        fieldData.field.type = type;

        if (FormBuilder.dragOrigin === FormBuilder.DragOrigins.sourceField) {
            fieldData.field.name = fieldData.uiSettings.displayName;
            fieldData.field.handle = fieldData.uiSettings.defaultHandle + '_' + Craft.randomString(4);
            fieldData.field.uid = Craft.uuid();
        }

        if (FormBuilder.dragOrigin === FormBuilder.DragOrigins.layoutField) {

        }

        let fieldUid = fieldData.field.uid;

        let tabIndex = this.getTabIndexByTabUid(FormBuilder.selectedTabUid);
        let layoutElement = this.getLayoutElement(fieldUid, fieldData.field, fieldData.uiSettings);
        FormBuilder.tabs[tabIndex].elements.push(layoutElement);

        if (beforeFieldUid) {

            let tabIndex = this.getTabIndexByTabUid(FormBuilder.selectedTabUid);
            let tab = FormBuilder.tabs[tabIndex];

            let fieldIndex = this.getFieldIndexByFieldUid(tab, fieldUid);
            let targetField = tab.elements[fieldIndex];

            // Remove the updated field
            tab.elements.splice(fieldIndex, 1);

            // let beforeFieldIndex = tab.elements.length + 1;
            let beforeFieldIndex = this.getFieldIndexByFieldUid(tab, beforeFieldUid);

            // Insert the updated field before the target field
            tab.elements.splice(beforeFieldIndex, 0, targetField);

            // Update tab
            FormBuilder.tabs[tabIndex] = tab;

            // FormBuilder.lastUpdatedFormFieldUid = targetField.uid;
            //
            // this.resetLastUpdated();
        }
    },

    getLayoutElement(fieldUid, field, uiSettings) {
        return {
            type: 'BarrelStrength\\Sprout\\forms\\submissions\\CustomFormField',
            required: false,
            width: 100,
            uid: Craft.uuid(),
            userCondition: null,
            elementCondition: null,
            fieldUid: fieldUid,
            field: field,
            uiSettings: uiSettings,
        };
    },

    editFieldLayoutElement(layoutElement) {
        let FormBuilder = window.FormBuilder;

        FormBuilder.editFieldUid = layoutElement.fieldUid;

        let slideout = new Craft.CpScreenSlideout('sprout-module-forms/form-builder/edit-form-field-slideout-via-cp-screen', {
            hasTabs: true,
            tabManager: '',
            params: {
                formId: FormBuilder.formId,
                layoutElement: layoutElement,
            },
        });

        const $removeBtn = this.getRemoveButtonHtml();
        slideout.$footer.find('.flex-grow').before($removeBtn);
        slideout.$footer.find('.submit').addClass('secondary');

        $removeBtn.on('click', () => {
            console.log('waa');
            slideout.close();
        });

        // let settingsHtml = self.swapPlaceholders(response.data.settingsHtml, response.data.fieldUid);
        slideout.on('submit', ev => {
            this.updateFieldLayoutElement(FormBuilder.editFieldUid, ev.response.data.layoutElement);
        });

        slideout.on('close', () => {
            console.log('Close Edit Form Field Slideout');
            FormBuilder.editFieldUid = null;
        });

        // init ui elements on slideout
        // Craft.initUiElements(slideout);
    },

    updateFieldLayoutElement(fieldUid, fieldLayoutElement) {
        let FormBuilder = window.FormBuilder;

        let tabIndex = this.getTabIndexByTabUid(FormBuilder.selectedTabUid);
        let tab = FormBuilder.tabs[tabIndex];
        let fieldIndex = this.getFieldIndexByFieldUid(tab, fieldUid);
        let targetFieldLayoutElement = tab.elements[fieldIndex];

        targetFieldLayoutElement.required = fieldLayoutElement.required;

        // Merge updated values into existing field
        targetFieldLayoutElement.field = Object.assign(targetFieldLayoutElement.field, fieldLayoutElement.field);

        tab.elements[fieldIndex] = targetFieldLayoutElement;
    },

    updateFieldLayoutElementPosition(originTabUid, targetTabUid, fieldUid, beforeFieldUid = null) {
        console.log('updateFieldPosition');

        let FormBuilder = window.FormBuilder;

        let originTabIndex = this.getTabIndexByTabUid(originTabUid);
        let originTab = FormBuilder.tabs[originTabIndex];

        let targetTabIndex = this.getTabIndexByTabUid(targetTabUid);
        let targetTab = FormBuilder.tabs[targetTabIndex];

        if (!targetTab) {
            targetTab = originTab;
        }

        let originFieldIndex = this.getFieldIndexByFieldUid(originTab, fieldUid);
        let targetField = originTab.elements[originFieldIndex];

        // Remove the updated field from the layout
        // this might change the indexes of the elements on the tab
        originTab.elements.splice(originFieldIndex, 1);

        if (beforeFieldUid) {
            let beforeFieldIndex = this.getFieldIndexByFieldUid(targetTab, beforeFieldUid);

            // Insert the updated field before the target field
            targetTab.elements.splice(beforeFieldIndex, 0, targetField);
        } else {
            targetTab.elements.push(targetField);
        }

        // Update tab
        FormBuilder.tabs[targetTabIndex] = targetTab;

        // FormBuilder.lastUpdatedFormFieldUid = targetField.uid;

        // this.resetLastUpdated();
    },

    // -------------------------------------------------------------------------
    // MISC
    // -------------------------------------------------------------------------

    resetLastUpdated() {
        // The timeout here needs to match the time of the 'drop-highlight' css transition effect
        setTimeout(function() {
            window.FormBuilder.lastUpdatedFormFieldUid = null;
        }, 300);
    },

    getRemoveButtonHtml: function() {
        const $removeBtn = Craft.ui.createButton({
            class: 'icon',
            label: Craft.t('app', 'Remove'),
            spinner: true,
        });

        $removeBtn.attr('data-icon', 'trash');

        return $removeBtn;
    },
};

export { FieldLayout };