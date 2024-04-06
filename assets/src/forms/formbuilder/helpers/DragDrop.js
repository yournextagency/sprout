const DragDrop = {

    // -------------------------------------------------------------------------
    // LAYOUT TAB NAV
    // -------------------------------------------------------------------------

    dragStartLayoutTabNav: function(e) {
        console.log('dragStartLayoutTabNav');

        e.dataTransfer.setData('sprout/origin-page-tab-uid', e.target.dataset.tabUid);
        window.FormBuilder.dragOrigin = window.FormBuilder.DragOrigins.layoutTabNav;
        window.FormBuilder.isDraggingTabUid = e.target.dataset.tabUid;

        // e.dataTransfer.dropEffect = 'link';
        // e.dataTransfer.effectAllowed = 'copyLink';
    },

    dragEndLayoutTabNav: function(e) {
        console.log('dragEndLayoutTabNav');

        window.FormBuilder.dragOrigin = null;
        window.FormBuilder.isDraggingTabUid = null;
        window.FormBuilder.isDragOverTabUid = null;
    },

    dragEnterLayoutTabNav: function(e) {
        console.log('dragEnterLayoutTabNav');
        e.target.classList.add('no-pointer-events');
    },

    dragLeaveLayoutTabNav: function(e) {
        console.log('dragLeaveLayoutTabNav');
        e.target.classList.remove('no-pointer-events');
    },

    dragOverLayoutTabNav: function(e) {
        let FormBuilder = window.FormBuilder;

        if (FormBuilder.dragOrigin === FormBuilder.DragOrigins.layoutTabNav) {

        }

        if (FormBuilder.dragOrigin === FormBuilder.DragOrigins.sourceField || FormBuilder.dragOrigin === FormBuilder.DragOrigins.layoutField) {
            setTimeout(function() {
                FormBuilder.selectedTabUid = e.target.dataset.tabUid;
            }, 1000);
        }
    },

    dropOnLayoutTabNav: function(e) {
        console.log('dropOnLayoutTabNav');

        let FormBuilder = window.FormBuilder;

        e.target.classList.remove('no-pointer-events');

        let originTabUid = e.dataTransfer.getData('sprout/origin-page-tab-uid');
        let targetTabUid = e.target.dataset.tabUid;

        if (FormBuilder.dragOrigin === FormBuilder.DragOrigins.layoutTabNav) {
            FormBuilder.updateTabPosition(originTabUid, targetTabUid);
            FormBuilder.selectedTabUid = originTabUid;
        }

        if (FormBuilder.dragOrigin === FormBuilder.DragOrigins.sourceField) {
            let type = e.dataTransfer.getData('sprout/field-type');
            FormBuilder.addFieldToLayoutTab(type);
        }

        if (FormBuilder.dragOrigin === FormBuilder.DragOrigins.layoutField) {
            FormBuilder.updateFieldPosition(originTabUid, targetTabUid, self.isDraggingFormFieldUid);
        }
    },

    // -------------------------------------------------------------------------
    // LAYOUT TAB BODY
    // -------------------------------------------------------------------------

    // See specifying drop targets docs:
    // https://developer.mozilla.org/en-US/docs/Web/API/HTML_Drag_and_Drop_API/Drag_operations#specifying_drop_targets
    dragOverLayoutTabBody: function(e) {
        const isDraggingFormField = e.dataTransfer.types.includes('sprout/field-type');

        if (isDraggingFormField) {
            console.log('dragOverLayoutTabBody');
            event.preventDefault();
        }
    },

    dragLeaveLayoutTabBody: function(e) {
        console.log('dragLeaveLayoutTabBody');

        window.FormBuilder.isDragOverTabUid = null;
    },

    dragEnterLayoutTabBody: function(e) {
        console.log('dragEnterLayoutTabBody');

        let FormBuilder = window.FormBuilder;

        FormBuilder.isDragOverTabUid = FormBuilder.selectedTabUid;
    },

    dropOnLayoutTabBody: function(e) {
        console.log('dropOnLayoutTabBody');

        let FormBuilder = window.FormBuilder;
        let FieldLayoutHelper = FormBuilder.FieldLayoutHelper;

        e.target.classList.remove('no-pointer-events');

        let type = e.dataTransfer.getData('sprout/field-type');
        let originTabUid = e.dataTransfer.getData('sprout/origin-page-tab-uid');

        if (FormBuilder.dragOrigin === FormBuilder.DragOrigins.sourceField) {
            FieldLayoutHelper.addFieldToLayoutTab(type);

            let fieldData = FieldLayoutHelper.getFieldByType(type);
            let layoutElement = FieldLayoutHelper.getLayoutElement(fieldData.field.uid, fieldData.field, fieldData.uiSettings);
            FieldLayoutHelper.editFieldLayoutElement(layoutElement);
        }

        if (FormBuilder.dragOrigin === FormBuilder.DragOrigins.layoutField) {
            let dropBeforeTargetFieldUid = e.target.dataset.fieldUid;
            FieldLayoutHelper.updateFieldPosition(
                originTabUid,
                FormBuilder.selectedTabUid,
                FormBuilder.isDraggingFormFieldUid,
                dropBeforeTargetFieldUid
            );
        }

        console.log('tabs', FormBuilder.tabs);
    },

    // -------------------------------------------------------------------------
    // SOURCE FIELDS
    // -------------------------------------------------------------------------

    dragStartSourceField: function(e) {
        console.log('dragStartSourceField');

        window.FormBuilder.dragOrigin = window.FormBuilder.DragOrigins.sourceField;

        e.dataTransfer.setData('sprout/field-type', e.target.dataset.type);

        // e.dataTransfer.dropEffect = 'link';
        // e.dataTransfer.effectAllowed = 'copyLink';
    },

    dragEndSourceField: function(e) {
        console.log('dragEndSourceField');

        window.FormBuilder.isDraggingFormFieldUid = null;
        window.FormBuilder.isDragOverFormFieldUid = null;
    },

    // -------------------------------------------------------------------------
    // LAYOUT FIELDS
    // -------------------------------------------------------------------------

    dragStartLayoutField: function(e) {
        console.log('dragStartLayoutField');

        let FormBuilder = window.FormBuilder;

        // Store selected tab in drag object as it might change before the drop event
        e.dataTransfer.setData('sprout/origin-page-tab-uid', FormBuilder.selectedTabUid);
        e.dataTransfer.setData('sprout/field-type', e.target.dataset.type);
        FormBuilder.dragOrigin = FormBuilder.DragOrigins.layoutField;
        FormBuilder.isDraggingTabUid = e.target.dataset.tabUid;
        FormBuilder.isDraggingFormFieldUid = e.target.dataset.fieldUid;

        // Need setTimeout before manipulating dom:
        // https://stackoverflow.com/questions/19639969/html5-dragend-event-firing-immediately
        // setTimeout(function() {
        //   FormBuilder.isDraggingFormFieldUid = isDraggingFormFieldUid;
        // }, 10);

        // e.dataTransfer.dropEffect = 'move';
        // e.dataTransfer.effectAllowed = 'move';

        // Handle scroll stuff: https://stackoverflow.com/a/72807140/1586560
        // On drag scroll, prevents page from growing with mobile safari rubber-band effect
        // let VerticalMaxed = (window.innerHeight + window.scrollY) >= document.body.offsetHeight;
        //
        // this.scrollActive = true;
        //
        // if (e.clientY < 150) {
        //   this.scrollActive = false;
        //   this.scrollFieldLayout(-1);
        // }
        //
        // if ((e.clientY > (document.documentElement.clientHeight - 150)) && !VerticalMaxed) {
        //   this.scrollActive = false;
        //   this.scrollFieldLayout(1)
        // }
    },

    dragEndLayoutField: function(e) {
        console.log('dragEndLayoutField');

        // Reset scrolling
        // this.scrollActive = false;

        window.FormBuilder.isDraggingFormFieldUid = null;
        window.FormBuilder.isDragOverFormFieldUid = null;
    },

    dragEnterLayoutField: function(e) {
        console.log('dragEnterLayoutField');
        e.target.classList.add('no-pointer-events');
    },

    dragOverLayoutField: function(e) {
        const isDraggingLayoutField = e.dataTransfer.types.includes('sprout/field-type');

        if (isDraggingLayoutField) {
            // console.log('dragOverLayoutField');
            event.preventDefault();
        }
    },

    dragLeaveLayoutField: function(e) {
        console.log('dragLeaveLayoutField');
        e.target.classList.remove('no-pointer-events');
    },

    dropOnExistingLayoutField: function(e) {
        console.log('dropOnExistingLayoutField');
        let FormBuilder = window.FormBuilder;

        e.target.classList.remove('no-pointer-events');

        // let fieldUid = e.dataTransfer.getData('sprout/field-id');
        let type = e.dataTransfer.getData('sprout/field-type');
        let originTabUid = e.dataTransfer.getData('sprout/origin-page-tab-uid');
        let targetTabUid = e.target.dataset.tabUid;
        let beforeFieldUid = e.target.dataset.fieldUid;

        if (FormBuilder.dragOrigin === FormBuilder.DragOrigins.sourceField) {
            FormBuilder.addFieldToLayoutTab(type, beforeFieldUid);
        }

        if (FormBuilder.dragOrigin === FormBuilder.DragOrigins.layoutField) {
            FormBuilder.updateFieldPosition(originTabUid, targetTabUid, self.isDraggingFormFieldUid, beforeFieldUid);
        }
    },

    // -------------------------------------------------------------------------
    // LAYOUT FIELDS END ZONE
    // -------------------------------------------------------------------------

    dragEnterFieldLayoutEndZone: function(e) {
        console.log('dragEnterFieldLayoutEndZone');

        // this.isMouseOverEndZone = true;
    },

    dragLeaveFieldLayoutEndZone: function(e) {
        console.log('dragLeaveFieldLayoutEndZone');

        // window.FormBuilder.isMouseOverEndZone = false;
    },

    isOverFieldLayoutEndZone: function(e) {
        const sproutFormField = e.dataTransfer.types.includes('sprout/field-type');

        // this.isDragOverTabUid = this.selectedTabUid;
        // this.isDragOverFormFieldUid = e.target.parentNode.dataset.fieldUid;


        if (sproutFormField) {
            console.log('isOverFieldLayoutEndZone');
            event.preventDefault();
        }
    },

    dropOnLayoutEndZone: function(e) {
        console.log('dropOnLayoutEndZone');

        let FormBuilder = window.FormBuilder;
        let FieldLayoutHelper = FormBuilder.FieldLayoutHelper;

        e.target.classList.remove('no-pointer-events');

        let type = e.dataTransfer.getData('sprout/field-type');
        let originTabUid = e.dataTransfer.getData('sprout/origin-page-tab-uid');
        let targetTabUid = e.target.dataset.tabUid;
        let beforeFieldUid = e.target.dataset.fieldUid;

        if (FormBuilder.dragOrigin === FormBuilder.DragOrigins.sourceField) {
            FieldLayoutHelper.addFieldToLayoutTab(type);
        }

        if (FormBuilder.dragOrigin === FormBuilder.DragOrigins.layoutField) {
            FieldLayoutHelper.updateFieldPosition(originTabUid, targetTabUid, self.isDraggingFormFieldUid, beforeFieldUid);
        }
    },
};

export { DragDrop };